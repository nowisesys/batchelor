/* 
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems)
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/* global fetch */

(function () {
    let formFile = document.getElementById('file-input');
    let dropArea = document.getElementById('file-upload-drop-area');

    let jobsTodo = 0;
    let jobsDone = 0;
    let jobsFail = [];
    let lastError = false;

    let submitStatus = document.getElementById('submit-status');
    let submitFinish = document.getElementById('submit-finish');

    let responses = [];

    // 
    // Toggle display of advanced options:
    // 
    document.querySelectorAll(".show-advanced-options").forEach(function (elem) {
        elem.addEventListener('change', function () {
            show_advanced_options(this);
        }, false);
    });

    // 
    // Handle click on submit data button:
    // 
    document.querySelector("#submit-data-button").addEventListener("click", function () {
        submit_data(this);
    }, false);

    // 
    // Handle upload when browsing for files:
    // 
    formFile.addEventListener('change', function () {
        handle_files(this.files);
    });

    // 
    // Prevent default action:
    // 
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(event => {
        dropArea.addEventListener(event, prevent_defaults, false);
    });

    function prevent_defaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // 
    // Add visual feedback on drag enter/leave:
    // 
    ['dragenter', 'dragover'].forEach(event => {
        dropArea.addEventListener(event, drag_add_highlight, false);
    });

    ['dragleave', 'drop'].forEach(event => {
        dropArea.addEventListener(event, drag_remove_highlight, false);
    });

    function drag_add_highlight(e) {
        dropArea.classList.add('w3-card');
    }

    function drag_remove_highlight(e) {
        dropArea.classList.remove('w3-card');
    }

    // 
    // The file upload progress:
    // 
    function initialize_progress(number) {
        jobsDone = 0;
        jobsTodo = number;
        jobsFail = [];

        submitFinish.style.display = 'none';
        submitStatus.style.display = 'inline-block';
    }

    function progress_tick() {
        jobsDone++;
        submitStatus.innerHTML = '(' + 100 * (jobsDone / jobsTodo) + '%)';
    }

    function progress_done() {
        if (jobsDone === jobsTodo) {
            show_submit_result();
        }
    }

    function show_submit_result() {
        submitStatus.style.display = 'none';

        if (lastError) {
            show_submit_error();
        } else if (jobsFail.length > 0) {
            show_submit_warning();
        } else {
            show_submit_success();
        }
    }

    function show_submit_success() {
        submitFinish.classList.remove('w3-animate-hide');
        submitFinish.style.display = 'block';

        setTimeout(function () {
            submitFinish.classList.add('w3-animate-hide');
        }, 3000);
    }

    function show_submit_warning() {
        const list = document.createElement("ul");

        for (const job of jobsFail) {
            let item = document.createElement("li");
            let text = document.createTextNode(job.message + ' (' + job.subj + ')');
            item.appendChild(text);
            list.appendChild(item);
        }

        show_error_dialog(list);
    }

    function show_submit_error() {
        show_error_dialog(lastError);
    }

    // 
    // Handle file drop:
    // 
    dropArea.addEventListener('drop', handle_drop, false);

    function handle_drop(e) {
        let dt = e.dataTransfer;
        let files = dt.files;

        handle_files(files);
    }

    // 
    // Send each file individual:
    // 
    function handle_files(files) {
        files = [...files];
        initialize_progress(files.length);
        (files).forEach(submit_file);
    }

    // 
    // Upload a single file:
    // 
    function submit_file(file) {
        enqueue_file(file);
    }

    // 
    // Add response button showing job details.
    // 
    function add_submit_response(resp) {
        const target = document.getElementById('submit-job-listing');
        const parent = target.parentElement;
        const button = document.createElement("a");
        const length = responses.length;

        button.innerHTML = "Job " + length;
        button.classList.add("w3-btn");
        button.classList.add("w3-animate-opacity");
        button.style = "margin-right: 3px; margin-top: 3px; min-width: 80px";

        button.addEventListener('click', function () {
            content_replace(event, "submit-job-details", 'details?jobid=' + resp.identity.jobid + '&' + 'result=' + resp.identity.result + '&embed=1', false);
        }, false);

        target.appendChild(button);

        target.style.display = 'block';
        parent.style.display = 'block';
    }

    // 
    // On enqueue job response.
    // 
    function on_submit_response(resp) {
        responses.push(resp);
        add_submit_response(resp);
    }

    // 
    // On enqueue job successful.
    // 
    function on_submit_success(subj) {
        progress_tick();
        progress_done();
    }

    // 
    // On enqueue job failed (non-critical).
    // 
    function on_submit_warning(resp, subj) {
        resp.subj = subj;
        jobsFail.push(resp);
        progress_tick();
        progress_done();
    }

    // 
    // On enqueue job failed (severe error).
    // 
    function on_submit_error(resp, subj) {
        resp.subj = subj;
        lastError = resp;
        show_submit_result();      // Report immediatelly
    }

    // 
    // Submit form data. The data is either a chunk of text or one or more
    // URL. Create (enqueue) one job per URL.
    // 
    function submit_data(sender) {
        const form = sender.parentNode.parentNode;
        const text = form.querySelector("textarea").value.trim();
        const name = form.querySelector('#submit-name').value.trim();
        const task = form.querySelector('#submit-task').value.trim();

        // 
        // We need to have input in array form for detecting URL's:
        // 
        let data = text.split("\n");

        let send = {
            text: [],
            urls: []
        };

        for (var i = 0; i < data.length; ++i) {
            if (data[i].startsWith("http://") || data[i].startsWith("https://") ||
                data[i].startsWith("ftp://") || data[i].startsWith("ftps://")) {
                send.urls.push(data[i]);
            } else {
                send.text.push(data[i]);
            }
        }

        jobsTodo = 0;
        jobsDone = 0;

        if (send.text.length !== 0) {
            jobsTodo += 1;
        }
        if (send.urls.length !== 0) {
            jobsTodo += send.urls.length;
        }

        initialize_progress(jobsTodo);

        if (send.text.length !== 0) {
            enqueue_text(name, task, send.text.join("\n"));
        }
        if (send.urls.length !== 0) {
            enqueue_urls(name, task, send.urls);
        }
    }

    function enqueue_text(name, task, text) {
        enqueue_data({
            data: text,
            type: 'data',
            task: task,
            name: name
        });
    }

    function enqueue_urls(name, task, urls) {
        for (var i = 0; i < urls.length; ++i) {
            enqueue_data({
                data: urls[i],
                type: 'url',
                task: task,
                name: name
            });
        }
    }

    function enqueue_data(data) {
        if (data.data.length === 0) {
            throw "Input data is empty";
        }

        fetch('../api/json/enqueue', {
            method: 'post',
            credentials: 'same-origin',
            body: JSON.stringify(data)
        })
            .then((resp) => resp.json())
            .then((resp) => {
                if (resp.status === "success") {
                    on_submit_response(resp.result);
                    on_submit_success();
                } else {
                    on_submit_warning(resp);
                }
            })
            .catch((error) => {
                on_submit_error(error);
            });
    }

    function enqueue_file(file) {
        let url = "../api/json/enqueue";
        let formData = new FormData();

        const form = document.getElementById('submit-file').querySelector("form");

        const name = form.querySelector('#submit-name').value.trim();
        const task = form.querySelector('#submit-task').value.trim();

        if (file !== null) {
            formData.append('file', file);
        }
        if (name !== null) {
            formData.append('name', name);
        }
        if (task !== null) {
            formData.append('task', task);
        }

        fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        })
            .then((resp) => resp.json())
            .then((resp) => {
                if (resp.status === "success") {
                    on_submit_success(file.name);
                } else {
                    on_submit_warning(resp, file.name);
                }
            })
            .catch((error) => {
                on_submit_error(error, file.name);
            });
    }

    function show_advanced_options(sender) {
        const form = sender.parentNode.parentNode;
        const sect = form.querySelector('#submit-advanced-option');

        if (sender.checked) {
            sect.style.display = 'block';
        } else {
            sect.style.display = 'none';

        }
    }

})();
