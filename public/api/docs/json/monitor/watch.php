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

<a name="watch"></a>
<div class="w3-panel w3-dark-gray">
    <h4>Get list of jobs finished after a timestamp (watch)</h4>
    <p>
        This method can be used by client side to implement polling for completed
        jobs. Timestamp of each returned job is inject as status.stamp to support
        progressive increment of watch argument as currently running jobs becomes
        finished.
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/watch", true) ?>?pretty=1" -d '{"stamp":1542281262}'</bash>
    <pre>{
    "status": "success",
    "result": [
        {
            "identity": {
                "jobid": "7f22587c-d1e4-4084-8bf4-e9f5d5664840",
                "result": "15422937608783"
            },
            "status": {
                "queued": {
                    "date": "2018-11-15 15:56:00.294915",
                    "timezone_type": 3,
                    "timezone": "Europe/Stockholm"
                },
                "started": null,
                "finished": null,
                "state": "pending",
                "stamp": 1542293760
            },
            "submit": {
                "task": "default",
                "name": null
            }
        },
        {
            "identity": {
                "jobid": "d52f6e17-e6c3-41a7-9bb6-7ae652a48fdf",
                "result": "15422938272592"
            },
            "status": {
                "queued": {
                    "date": "2018-11-15 15:57:07.686520",
                    "timezone_type": 3,
                    "timezone": "Europe/Stockholm"
                },
                "started": null,
                "finished": null,
                "state": "pending",
                "stamp": 1542293827
            },
            "submit": {
                "task": "default",
                "name": null
            }
        },
        {
            "identity": {
                "jobid": "5fba15b2-9c51-4cbf-a5ff-1801616623aa",
                "result": "15422947593926"
            },
                ...
        }
    ]
}</pre>
</div>

<?php inline("watch.inc") ?>

<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(["stamp" => time()]) ?></pre>
</div>