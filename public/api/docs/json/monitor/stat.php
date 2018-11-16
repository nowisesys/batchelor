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

use Batchelor\WebService\Types\JobIdentity;

?>

<a name="stat"></a>
<div class="w3-panel w3-dark-gray">
    <h4>Get status of a single job (stat)</h4>
    <p>
        This method can be used to implement client side polling for a single
        job completion. The response provide the nessesary data for progressive
        update the user interface as job state changes.
    </p>
</div>
<div class="w3-code">
    <bash>curl -XPOST "http://localhost/batchelor2/api/json/stat?pretty=1" -d '{"jobid":"3c980fc7-5807-4dc5-b2a4-861c20c63906","result":"1542313123894"}'</bash>
    <pre>{
    "status": "success",
    "result": {
        "queued": {
            "date": "2018-11-15 21:18:44.182470",
            "timezone_type": 3,
            "timezone": "Europe/Stockholm"
        },
        "started": null,
        "finished": null,
        "state": "pending"
    }
}</pre>
</div>
<?php inline("stat.inc") ?>
<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(new JobIdentity("6633e06c-deaf-4222-bbdf-036e4be4f0a0", "f52764d624db129b32c21fbca0cb8d6")) ?></pre>
</div>
