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

?>

<a name="queue"></a>
<div class="w3-panel w3-dark-gray">
    <h4>List content of the queue (queue)</h4>
    <p>
        List the content of current selected queue that matches the supplied
        filter options. The returned result can be optional sorted by server
        side.
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/queue", true) ?>?pretty=1"</bash>
    <pre>{
    "status": "success",
    "result": [
        {
            "identity": {
                "jobid": "91ccd8bf-7c0f-48f3-94b3-8eb1da923ad6",
                "result": "15421971205405"
            },
            "status": {
                "queued": {
                    "date": "2018-11-14 13:05:20.082591",
                    "timezone_type": 3,
                    "timezone": "Europe/Stockholm"
                },
                "started": {
                    "date": "2018-11-14 13:05:20.355639",
                    "timezone_type": 3,
                    "timezone": "Europe/Stockholm"
                },
                "finished": {
                    "date": "2018-11-14 13:05:20.382272",
                    "timezone_type": 3,
                    "timezone": "Europe/Stockholm"
                },
                "state": "finished"
            },
            "submit": {
                "task": "default",
                "name": "Job 2"
            }
        },
        {
            "identity": {
                "jobid": "ac051cbd-5a56-4915-b97c-27061462edc4",
                "result": "15421969943499"
            },
                ...
        }
    ]
}</pre>
</div>

<?php inline("queue.inc") ?>

<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(["sort" => "...", "filter" => "..."]) ?></pre>
</div>
<p>
    The sort and filter options are enums. For possible values and description, 
    please visit their documentation pages.
</p>
<a class="w3-btn w3-deep-purple" style="min-width: 80px" href="../../sort">Sorting</a>
<a class="w3-btn w3-deep-purple" style="min-width: 80px" href="../../filter">Filter</a>

<p>
    This example will sort on start time and include all completed jobs (that is,
    all jobs that finished with either success or warning state):
</p>
<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/queue", true) ?>?pretty=1" -d '{"sort":"started","filter":"completed"}'</bash>
</div>
