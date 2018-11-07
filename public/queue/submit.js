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
    data = text.split("\n");

    send = {
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
        throw "Input data is empty"
    }
    
    fetch('../ws/json/enqueue', {
        method: 'post',
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(response => {
            if (response.status === 'failure') {
                throw response.message;
            }
        })
        .catch(error => show_error_dialog(error))
}
