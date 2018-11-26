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

<a name="resume"></a>
<div class="w3-panel w3-dark-gray">
    <h4>Continue paused job (resume)</h4>
    <p>
        Use this method to resume execution of an paused job. Resuming a job will not
        immediatelly make it start again, instead its moved to the ready list that
        will be consumed before other pending jobs.    
    </p>
</div>

<div class="w3-code">
    <bash>curl -XPOST "<?= $this->config->getUrl("api/json/resume", true) ?>?pretty=1" -d '{"jobid":"6633e06c-deaf-4222-bbdf-036e4be4f0a0","result":"f52764d624db129b32c21fbca0cb8d6"}'</bash>
    <pre>{
    "status": "success",
    "result": true
}</pre>
</div>

<?php inline("resume.inc") ?>

<p>    
    The complete input data is:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(new JobIdentity("6633e06c-deaf-4222-bbdf-036e4be4f0a0", "f52764d624db129b32c21fbca0cb8d6")) ?></pre>
</div>
<p>
    Trying to resume an job not paused will render an exception.
</p>
