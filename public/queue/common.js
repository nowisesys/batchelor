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

/* global Element */

function toggle_section_display(id) {
    const target = document.getElementById(id);

    if (target.style.display === 'none') {
        target.style.display = 'block';
    } else {
        target.style.display = 'none';
    }
}

// 
// Delete queued job and remove from listing. The json contains the
// JSON encoded job identity.
// 
function delete_queued_job(json) {
    fetch('../api/json/dequeue', {
        method: 'post',
        credentials: 'same-origin',
        body: json
    })
        .then(response => response.json())
        .then(response => {
            if (response.status === 'success') {
                const data = JSON.parse(json);
                const item = document.getElementById(data.jobid);
                item.parentNode.removeChild(item);
            } else {
                throw response.message;
            }
        })
        .catch(error => show_error_dialog(error))
}

// 
// Delete listed jobs.
// 
function delete_listed_jobs(message) {
    if (!confirm(message)) {
        return false;
    }

    document.querySelectorAll(".job-item").forEach(function (elem) {
        delete_queued_job(JSON.stringify({
            jobid: elem.id,
            result: elem.dataset.root
        }));
    });
}

// 
// Display current tab.
// 
function display_tab(id) {
    document.querySelectorAll(".submit").forEach(function (elem) {
        elem.style.display = 'none';
    });
    document.getElementById(id).style.display = 'block';
}

function file_download(event, params) {
    const source = 'download?' + params;
    window.location.href = source;          // Ugly hack ;-)
}

function show_next_sibling(sender) {
    var target = sender.nextSibling.nextSibling;

    if (target.offsetParent === null) {
        target.classList.remove('w3-hide-small');
        target.classList.add("w3-animate-right");
    }
}