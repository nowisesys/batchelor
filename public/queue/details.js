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

/* global hljs */

function toggle_section_display(id) {
    const target = document.getElementById(id);

    if (target.style.display === 'none') {
        target.style.display = 'block';
    } else {
        target.style.display = 'none';
    }
}

function file_download(event, params) {
    const source = 'download?' + params;
    window.location.href = source;          // Ugly hack ;-)
}

function file_preview(event, params) {
    const source = 'preview?' + params;
    const target = 'file-preview';

    content_replace(event, target, source, function () {
        hljs.highlightBlock(document.getElementById(target).querySelector("code"));
    });
}
