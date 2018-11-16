<?php
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

use Batchelor\WebService\Types\JobIdentity;

?>

<a name="readdir"></a>
<div class="w3-panel w3-dark-gray">
    <h4>Get job directory listing (readdir)</h4>
    <p>
        Use this method to read all directories and files from a single job 
        directory. The listing contains the nessesary information for implementing
        explorer like file browsing on client side.
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "http://localhost/batchelor2/api/json/readdir?pretty=1" -d '{"jobid":"34f95c954-09ce-46b7-bb59-820386cc9c89","result":"15421967654378"}'</bash>
    <pre>{
    "status": "success",
    "result": [
        {
            "name": "result",
            "size": 4096,
            "mime": "directory",
            "type": "dir",
            "lang": ""
        },
        {
            "name": "result/output-reverse.txt",
            "size": 13,
            "mime": "text/plain",
            "type": "file",
            "lang": "text"
        },
        {
            "name": "task-reverse.log",
            "size": 354,
            "mime": "text/plain",
            "type": "file",
            "lang": "log"
        },
        {
            "name": "result.zip",
            "size": 284,
            "mime": "application/zip",
            "type": "file",
            "lang": "zip"
        },
                ...
        {
            "name": "default/indata.ser",
            "size": 243,
            "mime": "text/plain",
            "type": "file",
            "lang": "ser"
        },
        {
            "name": "task-counter.log",
            "size": 354,
            "mime": "text/plain",
            "type": "file",
            "lang": "log"
        }
    ]
}</pre>
</div>

<?php inline("readdir.inc") ?>

<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(new JobIdentity("6633e06c-deaf-4222-bbdf-036e4be4f0a0", "f52764d624db129b32c21fbca0cb8d6")) ?></pre>
</div>
