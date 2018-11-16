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

include_once("../support.inc");

?>

<h3>JSON API</h3>
<p>
    The API can logical be divided in three sections: running jobs, monitor job 
    state and reading results.
</p>
<a class="w3-btn w3-deep-orange" href="running">Running</a>
<a class="w3-btn w3-deep-orange" href="monitor">Monitor</a>
<a class="w3-btn w3-deep-orange" href="reading">Reading</a>

<h4>Command line</h4>
<p>
    Using <a href="https://curl.haxx.se/">curl</a> is a great way to explore the 
    API from the command line:
</p>
<div class="w3-code">
    <bash>curl -XPOST http://localhost/batchelor2/api/json/queue -d '{"sort":"started","filter":"crashed"}'</bash>
</div>

<h4>Response</h4>
<p>
    All responses (except from fopen()) is JSON encoded and contains at least the
    status member that is either 'success' or 'failure'. A success response contains 
    the additional 'result' member containing the requested data:
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(["status" => "success", "result" => "..."]) ?></pre>
</div>
<p>
    A failed request contains the additional 'message' and 'code' members. The 
    code is internal and can be ignored.
</p>
<div class="w3-code w3-border-deep-purple">
    <pre><?= encode(["status" => "failure", "message" => "The task processor vorbis is missing", "code" => 0]) ?></pre>
</div>
<div class="w3-panel w3-blue">
    <p>
        The HTTP status code is always 200. If you get something else, its origin 
        is not from this system.
    </p>
</div>

<h4>Modify output</h4>
<p>
    A couple of extra parameters can be passed that affects how the JSON response 
    gets encoded:
</p>
<div class="w3-code">
    <bash>curl -XPOST http://localhost/batchelor2/api/json/queue?pretty=1&escape=0&numeric=1&unicode=0&fraction=1 -d '{"sort":"started","filter":"crashed"}'</bash>
</div>

<ul>
    <li><b>pretty</b>: Use whitespace in returned data to format it.</li>
    <li><b>escape</b>: Don't escape '/'.</li>
    <li><b>numeric</b>: Encodes numeric strings as numbers.</li>
    <li><b>unicode</b>: Encode multibyte Unicode characters literally (default is to escape as \uXXXX).</li>
    <li><b>fraction</b>: Ensures that float values are always encoded as a float value.</li>
</ul>
<p>
    These options can be passed using POST or GET (as in the example above). The 
    example also shows the value to enable/disable that praticular option.
</p>
