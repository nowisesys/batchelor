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

?>

<a name="opendir"></a>
<div class="w3-panel w3-dark-gray">
    <h4>List all job directories (opendir)</h4>
    <p>
        This method takes no arguments. The listing consists of job identities 
        that can be used to stat a single job or list the directory content.
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/opendir", true) ?>?pretty=1"</bash>
    <pre>{
    "status": "success",
    "result": [
        {
            "jobid": "00a0cacb-fcce-4e96-ae1f-b6e2ceddbb3d",
            "result": "15419047559485"
        },
        {
            "jobid": "d55a7da0-5e90-4667-8633-df7a59200c37",
            "result": "15419330338516"
        },
        {
            "jobid": "096c85f9-0e9a-4572-8ec1-6dfd558257bb",
            "result": "15419335129351"
        },
                ...
        {
            "jobid": "3c980fc7-5807-4dc5-b2a4-861c20c63906",
            "result": "15423131243894"
        }
    ]
}</pre>
</div>

<?php inline("opendir.inc") ?>
