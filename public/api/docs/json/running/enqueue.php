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

include_once("../../support.inc");

use Batchelor\WebService\Types\JobData;

?>

<a name="enqueue"></a>
<div class="w3-panel w3-dark-gray">
    <h4>Submit jobs (enqueue)</h4>
    <p>
        Use this method for scheduling jobs for later execution. The response will
        contain the identity that can be used in future API requests.
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/enqueue", true) ?>?pretty=1" -d '{"data":"hello world","type":"data"}'</bash>
    <pre>{
    "status": "success",
    "result": {
        "identity": {
            "jobid": "34f95c954-09ce-46b7-bb59-820386cc9c89",
            "result": "15421967654378"
        },
        "status": {
            "queued": {
                "date": "2018-11-15 20:59:33.875054",
                "timezone_type": 3,
                "timezone": "Europe/Stockholm"
            },
            "started": null,
            "finished": null,
            "state": "pending"
        },
        "submit": {
            "task": "default",
            "name": null
        }
    }
}</pre>
</div>

<?php inline("enqueue.inc") ?>

<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(new JobData("hello world", "data", "task1", "Job name")) ?></pre>
</div>
<ul>
    <li>[Required] The <u>data</u> is the input to process. If encoding is required, it has
        to be established by the target task.</li>
    <li>[Required] The <u>type</u> tells what kind of data the data member contains (possible 
        values are 'data' or 'url'). When using url, the data member should contain 
        an HTTP(s) or FTP(s) URL from where data can be downloaded.</li>
    <li>[Optional] The <u>task</u> is the target processor on the server side for processing the 
        input data. This member is optional and default to 'default' task.</li>    
    <li>[Optional] The <u>name</u> is some generic string used to identify the job. It don't 
        affect the job execution and is only used for sorting and search.</li>
</ul>

<h5>Form POST</h5>
<p>
    Sending large files in the data member would be potential slow and error-prone
    and possibly lead to out of memory errors. Therefor its also possible to enqueue 
    jobs by ordinary file POST. The task and name can be set as POST parameters. 
    Use file as identifier, but if unset it should be auto detected on server side.
</p>
<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/enqueue", true) ?>?pretty=1" -i -F file=@indata.txt -F task=default -F "name=my task name"</bash>
    <pre>{
    "status": "success",
    "result": {
        "identity": {
            "jobid": "34f95c954-09ce-46b7-bb59-820386cc9c89",
            "result": "15421967654378"
        },
        "status": {
            "queued": {
                "date": "2018-11-15 21:21:40.286134",
                "timezone_type": 3,
                "timezone": "Europe/Stockholm"
            },
            "started": null,
            "finished": null,
            "state": "pending"
        },
        "submit": {
            "task": "default",
            "name": "my task name"
        }
    }
}</pre>
</div>
