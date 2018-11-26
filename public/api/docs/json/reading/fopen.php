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

<a name="fopen"></a>
<div class="w3-panel w3-dark-gray">
    <h4>Read file from job directory (fopen)</h4>
    <p>
        Use this method for reading a file or directory inside the job directory. 
        This method will output HTTP headers that should trigger the save file
        dialog to open. The native MIME type of the target file will be used.
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/fopen", true) ?>?pretty=1" -d '{"job":{"jobid":"34f95c954-09ce-46b7-bb59-820386cc9c89","result":"15421967654378"},"file":"result/output-normal.txt"}' -i</bash>
    <pre>HTTP/1.1 200 OK
Date: Fri, 16 Nov 2018 02:17:54 GMT
Server: Apache
X-Powered-By: PHP/7.1.22
Set-Cookie: PHPSESSID=cf9kf7ijoqatvhi3kptfhhmpi3; path=/
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Content-Disposition: attachment; filename="output-reverse.txt"
Content-Length: 13
ETag: d7c7fb2372f26b9345090b6ec62ed1a5
Content-Type: text/plain;charset=UTF-8

hello world!!</pre>
</div>

<?php inline("fopen.inc") ?>

<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?=
        encode([
                "job"  => new JobIdentity("6633e06c-deaf-4222-bbdf-036e4be4f0a0", "f52764d624db129b32c21fbca0cb8d6"),
                "file" => "filename",
                "send" => true])

        ?></pre>
</div>

<p>
    Reading a directory will return the content as a ZIP-file.
</p>
<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/fopen", true) ?>?pretty=1" -d '{"job":{"jobid":"34f95c954-09ce-46b7-bb59-820386cc9c89","result":"15421967654378"},"file":"result"}' -i</bash>
    <pre>HTTP/1.1 200 OK
Date: Fri, 16 Nov 2018 02:19:57 GMT
Server: Apache
X-Powered-By: PHP/7.1.22
Set-Cookie: PHPSESSID=o9dada7m36dlq2a83872gov4u3; path=/
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Content-Disposition: attachment; filename="result.zip"
Content-Length: 284
ETag: af9a273f3e21f4d92ce6df370ba1d0e6
Content-Type: application/zip

Warning: Binary output can mess up your terminal. Use "--output -" to tell 
Warning: curl to output it to your terminal anyway, or consider "--output 
Warning: <FILE>" to save to a file.</pre>
</div>

<p>
    Pass false as send value to download the file as JSON data. The file content 
    will be base64 encoded.
</p>
<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/fopen", true) ?>?pretty=1" -d '{"job":{"jobid":"34f95c954-09ce-46b7-bb59-820386cc9c89","result":"15421967654378"},"file":"result/output-normal.txt","send":false}' -i</bash>
    <pre>HTTP/1.1 200 OK
Date: Fri, 16 Nov 2018 02:37:09 GMT
Server: Apache
X-Powered-By: PHP/7.1.22
Set-Cookie: PHPSESSID=pnrq4ffuad6dbhrrqf5lsp7fc3; path=/
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Cache-Control: no-store, no-cache, must-revalidate
Pragma: no-cache
Content-Length: 65
Content-Type: application/json

{
    "status": "success",
    "result": "ISFkbHJvdyBvbGxlaA=="
}</pre>
</div>
