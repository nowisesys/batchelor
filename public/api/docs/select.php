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

<h3>Select queue</h3>
<p>
    This section documents how to switch between active job queues. By default 
    the IP-address of the calling host is used to deduce the active job queue 
    unless one is selected. The active queue is stored on client computer using 
    the hostid cookie.
</p>

<h4>From web interface</h4>
<p>
    Click on the clock in main tool bar from the web interface to open the queue 
    select dialog. Type in the prefered queue name and apply by pressing swicth
    button.
</p>
<img src="select.png" alt="Showing queue selecting from web interface">
<p>
    To revert back to default queue, use an empty name or press the revert button.
</p>

<h4>From web service</h4>
<p>
    Both the SOAP and JSON service provides methods for switching between active
    queues. These two are functional equivalent, but for simplicity we restrict
    ourself to describing the JSON call using the <a href="https://curl.haxx.se/">curl</a>
    command.
</p>

<div class="w3-code">
    <bash>curl -XPOST <?= $this->config->getUrl("api/json/select", true) ?> -d '{"queue":"myqueue"}'</bash>
    <div>{"status":"success","result":"e47135de28d5c36dd5ed5a816cf61658"}</div>
</div>

<p>
    Appending the verbose flag reveals that HTTP response headers include a set cookie
    directive:
</p>
<div class="w3-code">
    <bash>curl -XPOST <?= $this->config->getUrl("api/json/select", true) ?> -d '{"queue":"myqueue"}' -v</bash>
    <div>Set-Cookie: hostid=e47135de28d5c36dd5ed5a816cf61658; path=/</div>
</div>

<p>
    Enable a cookie store in the web service client should automatic set and send
    the cookie in future requests.
</p>

<h5>Custom HTTP header</h5>
<p>
    Using a cookie store is not required, the hostid can also be provided by a 
    custom HTTP header:
</p>
<div class="w3-code">
    <bash>curl -XPOST <?= $this->config->getUrl("api/json/version", true) ?> \<br>
        -H 'X-Batchelor-Hostid: e47135de28d5c36dd5ed5a816cf61658' -v</bash>
    <div>X-Batchelor-Hostid: e47135de28d5c36dd5ed5a816cf61658</div>
</div>
<p>
    Using a custom HTTP header has the additional feature that your web client
    can easily interact with multiple job queues at same time. For example, 
    output from one could be input for another.
</p>
