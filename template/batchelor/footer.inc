<?php
/*
 * Copyright (C) 2018 Anders Lövgren (Nowise Systems).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

?>

<script>

        // 
        // Add a script element as a child of the body:
        // 
        function delayed_javascript_loading() {
<?php if ($show->translate) : ?>
                    var element = document.createElement("script");
                    element.src = "//translate.google.com/translate_a/element.js?cb=google_translate_element_init";
                    document.body.appendChild(element);
<?php endif; ?>
        }

        // 
        // Check for browser support of event handling capability:
        // 
        if (window.addEventListener) {
            window.addEventListener("load", delayed_javascript_loading, false);
        } else if (window.attachEvent) {
            window.attachEvent("onload", delayed_javascript_loading);
        } else {
            window.onload = delayed_javascript_loading;
        }
</script>

<script>

        // 
        // Fetch content as text and assign to inner html. The
        // target is either an element or string (id).
        // 
        function content_replace_text(event, target, source, callback) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            if (typeof target === "string") {
                target = document.getElementById(target);
            }

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function (x) {
                if (this.readyState === 4 && this.status === 200) {
                    try {
                        target.innerHTML = this.responseText;
                        if (callback !== undefined &&
                            callback !== false) {
                            callback(target, source);
                        }
                    } catch (err) {
                        console.log(err);
                    }
                }
            };
            xhr.open("GET", source, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.send();
            close_overlay();
            return false;
        }

        // 
        // Fetch content as DOM document and evaluate all script nodes. The
        // target is either an element or string (id).
        // 
        function content_replace_html(event, target, source, callback) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            if (typeof target === "string") {
                target = document.getElementById(target);
            }

            var xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function (x) {
                if (this.readyState === 4 && this.status === 200) {
                    try {
                        var shadow = target.cloneNode();
                        var insert = this.response.getElementsByTagName('body')[0].children;
                        var script = []; // Scripts to eval.

                        // 
                        // Loop thru all DOM nodes to insert. Filter script elements based on
                        // AJAX load attribute value.
                        // 
                        for (var i = 0; i < insert.length; ++i) {
                            var child = insert[i].cloneNode(true);
                            var attrs = child.attributes;

                            if (child.nodeName !== "SCRIPT") {
                                shadow.appendChild(child);
                            } else if (attrs['onajax'] && attrs['onajax'].value === "add") {
                                shadow.appendChild(child);
                            } else if (attrs['onajax'] && attrs['onajax'].value === "run") {
                                shadow.appendChild(child);
                                script.push(child);
                            }
                        }

                        target.parentNode.replaceChild(shadow, target);
                        if (callback !== undefined &&
                            callback !== false) {
                            callback(target, source);
                        }

                        for (var i = 0; i < script.length; ++i) {
                            var child = script[i];
                            var attrs = child.attributes;

                            // 
                            // Handle extern or inline javascript:
                            // 
                            if (attrs.src) {
                                var linked = document.createElement('script');
                                linked.setAttribute("type", "text/javascript");
                                linked.setAttribute("src", child.src);
                                document.body.appendChild(linked);
                            } else {
                                eval(script[i].innerHTML);
                            }
                        }

                    } catch (err) {
                        console.log(err);
                    }
                }
            };

            xhr.open("GET", source, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            xhr.responseType = 'document';
            xhr.send();

            close_overlay();
            return false;
        }

        // 
        // Replace i.e. HTML content. The target is either an element or string (id)
        // and source is the URL to load. The callback is optional.
        // 
        // The history is modified by this call if pushState() is available, 
        // unless callback is defined. Pass false as callback to disable modfy
        // of history.
        // 
        // 
        function content_replace(event, target, source, callback) {
            if (history.pushState !== undefined && callback === undefined) {
                history.pushState({}, "", source);
            }

            if (typeof content_replace_extern === 'function') {
                return content_replace_extern(event, target, source, callback);
            } else if (source.match('/http(s)?:\/\/')) {
                return content_replace_text(event, target, source, callback);
            } else {
                return content_replace_html(event, target, source, callback)
            }
        }

        // 
        // Reload current location.
        // 
        function content_reload(event) {
            content_replace(event, 'page-content', location.href);
        }

        // 
        // Insert source in main content. Calling this function will replace
        // the main content with source (URL). The callback is optional. This
        // is a convenience function for content_replace().
        // 
        function content_insert(event, source, callback) {
            content_replace(event, 'page-content', source, callback);
        }

        // 
        // Display content in modal dialog.
        // 
        function show_modal_dialog(event, url) {
            const dialog = document.getElementById('modal-dialog');
            const target = dialog.querySelector("#modal-content");

            content_replace(event, target, url, false);

            dialog.style.display = 'block';
        }

        // 
        // Display error message in dialog.
        // 
        function show_error_dialog(error) {
            const dialog = document.getElementById('error-dialog');
            const target = dialog.querySelector("#modal-content");

            if (error instanceof Element) {
                target.innerHTML = "";
                target.appendChild(error);
            } else {
                target.innerHTML = error;
            }

            dialog.style.display = 'block';
        }

        function close_overlay() {
            var xm = document.getElementById("sidenav-menu");
            var xo = document.getElementById("page-overlay");
            xm.style.display = 'none';
            xo.style.display = 'none';
        }

        function close_panels() {
            var i, x = document.getElementsByClassName("panel");
            for (i = 0; i < x.length; i++) {
                x[i].className = x[i].className.replace(" w3-show", "");
                x[i].style.display = "none";
            }
        }

        function open_panel(x) {
            x.style.display = "inline-block";
            x.className += " w3-show";
        }

        function toggle_panel_display(x, func) {
            var open = (x.style.display === 'none');
            if (x.className.indexOf("w3-right")) {
                x.className = x.className.replace(" w3-right", "");
            }
            if (x.className.indexOf("w3-left")) {
                x.className = x.className.replace(" w3-left", "");
            }

            if (window.innerWidth < 650) {
                x.className += " w3-left";
            } else {
                x.className += " w3-right";
            }

            if (open) {
                close_panels();
                open_panel(x);
            } else {
                close_panels();
            }

            if (open && func !== undefined) {
                func(x);
            }
        }

        // 
        // Navigation menus.
        // 
        function toggle_navbar_open() {
            var x = document.getElementById("navbar-menu");
            toggle_panel_display(x);
        }

        function toggle_sidenav_open() {
            var xm = document.getElementById("sidenav-menu");
            var xo = document.getElementById("page-overlay");
            if (xm.style.display === 'none') {
                xm.style.display = 'block';
                xo.style.display = 'block';
            } else {
                xm.style.display = 'none';
                xo.style.display = 'none';
            }
        }

        // 
        // Select work queue.
        // 
        function toggle_queue_select() {
            var x = document.getElementById("queue-box");
            toggle_panel_display(x);
        }

        function queue_switch() {
            queue_select(document.getElementById('queue-name').value);
        }

        function queue_revert() {
            queue_select("");
        }

        function queue_select(name) {
            var target = '<?= $config->location ?>' + 'api/json/select';

            fetch(target, {
                method: 'post',
                body: JSON.stringify({queue: name})
            })
                .then(resp => resp.json())
                .then(resp => {
                    if (resp.status === 'failure') {
                        throw resp.message;
                    } else {
                        toggle_queue_select();
                        location.reload();
                    }
                })
                .catch(error => {
                    alert(error);
                });
        }

        // 
        // Page and site editor.
        // 
        function toggle_edit_open(host) {
            var editable = "<?= $this->isEditable() ? 1 : 0 ?>";

            // 
            // Redirect to editing host if required:
            // 
            if (window.location.host !== host) {
                window.location.host = host;
                return false;
            }

            var x = document.getElementById("edit-box");
            toggle_panel_display(x);

            var edit = (x.style.display !== 'none');
            var page = document.getElementById("page-content");

            if (editable) {
                page.setAttribute("contenteditable", edit);
                page.focus();
            }
        }

        function edit_save(event) {
            var editable = "<?= $this->isEditable() ? 1 : 0 ?>";

            if (!editable) {
                alert("This page is not editable using inline method");
                return;
            }

            var html = document.getElementById("page-content");
            var form = new FormData();

            form.append("content", html.innerHTML);

            var target = "";
            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function (x) {
                if (this.readyState === 4 && this.status === 200) {
                    var json = JSON.parse(this.responseText);
                    if (json.status === 'failure') {
                        alert(json.message);
                    }
                }
            }

            xhr.open("POST", target);
            xhr.send(form);
        }

        function edit_open(event) {
            window.location = "<?= $config->url($config->edit['view']) ?>" + "?path=" + "<?= $this->params->path ?>";
            event.preventDefault();
        }

        function edit_close(event) {
            window.location = "<?= $config->url('/' . $this->params->getParam('path')) ?>";
            event.preventDefault();
        }

        // 
        // Translation.
        // 
        function toggle_translate_open(obj) {
            var x = document.getElementById("language-box");
            toggle_panel_display(x);
        }

        function google_translate_element_init() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                autoDisplay: false,
                multilanguagePage: true,
                layout: google.translate.TranslateElement.InlineLayout.HORIZONTAL
            }, 'google_translate_element');

            var select = document.getElementsByClassName('goog-te-combo')[0];
            select.className += " w3-input w3-white w3-medium w3-border-grey w3-text-black";
        }

        // 
        // Simple search.
        // 
        var search_string;

        function toggle_search_open(obj) {
            var x = document.getElementById("search-box");
            toggle_panel_display(x);
        }

        function save_search(input) {
            search_string = input.value;
        }

        function send_search(site) {
            var query = "https://www.google.se/#q=" + search_string + "+site:" + site;
            document.location = query;
        }

        // 
        // Authenticate and logon status.
        // 
        function toggle_logon_open(obj) {
            var x = document.getElementById("logon-box");
            // 
            // Only display panel on open:
            // 
            toggle_panel_display(x, function (elem) {

                var target = "<?= $config->url($config->auth['logon'] . '?json=1') ?>";
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function (x) {
                    if (this.readyState === 4 && this.status === 200) {
                        var json = JSON.parse(this.responseText);
                        var stat = document.getElementById('logon_status_element');
                        var text = "";
                        switch (json.step) {
                            case 1:
                                text = 'You are not logged on';
                                break;
                            case 3:
                                text = 'Logged on as <b>' + json.data.user + '</b> using ' + json.auth[json.data.auth].desc;
                                break;
                                if (json.step === 3) {
                                }
                        }
                        stat.innerHTML = text;
                    }
                };

                xhr.open("GET", target, true);
                xhr.send();
            });
        }

        // 
        // Set navigation menu always on top when scrolling.
        // 
        var scrolling = false;
        window.addEventListener('scroll', function (e) {

            if (window.scrollY > 86) {
                if (scrolling) {
                    return;
                } else {
                    scrolling = true;
                }
            } else {
                if (!scrolling) {
                    return;
                } else {
                    scrolling = false;
                }
            }

            var tm = document.getElementById("topbar-menu");
            var tt = document.getElementById("topbar-tools");
            var ts = document.getElementById("topbar-sitehome");
            var sm = document.getElementById("sidenav-menu");

            if (scrolling) {
                tm.className += " w3-top";
                tt.style.display = "none";
                ts.style.display = "inline-block";
                sm.style.top = "43px";
            } else {
                tm.className = tm.className.replace(" w3-top", "");
                tt.style.display = "";
                ts.style.display = "none";
                sm.style.top = "auto"; // restore default pos
            }
        });

        // 
        // Called on history navigation. The href is the page to load.
        // 
        window.onpopstate = function (event) {
            if (document.location) {
                content_insert(event, document.location.href);
            }
        };

</script>

