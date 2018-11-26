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

<h3>Java client</h3>
<p>
    We will develop a simple java client and call some methods in the queue API.
    It's assumed that you have some basic knowledge about programming in Java.
</p>

<h4>Java (wsimport)</h4>
<p>
    Generate proxy classes for the queue API using wsimport:
</p>
<div class="w3-code">
    <bash>mkdir classes sources</bash><br>
    <bash>wsimport -d classes -s sources -p batchelor.soap <?= $this->config->getUrl("api/soap/", true) ?>?wsdl=1</bash><br>
    <span>parsing WSDL...</span><br>
    <span>Generating code...</span><br>
    <span>Compiling code...</span><br>
</div>

<h4>Add main class</h4>
<p>
    Change directory to sources and use your favorite editor to create a file 
    named client.java inside the batchelor directory.
</p>
<div class="w3-code">
    <bash>cd sources</bash><br>
    <bash>jed batchelor/client.java</bash><br>
</div>
<p>
    This class will contain our main() method, the starting point when running 
    the application.
</p>

<h4>Compiling</h4>
<p>
    The compiled SOAP proxy classes is located in the classes directory and need
    to be included:
</p>
<div class="w3-code">
    <bash>javac -cp ../classes batchelor/client.java</bash><br>
</div>

<h4>Running</h4>
<p>
    When running the application, both classes and current directory need to 
    be included to satisfy packages search path.
</p>
<div class="w3-code">
    <bash>java -cp ../classes:. batchelor/Client</bash><br>
</div>

<h4>Examples</h4>
<p>
    First code example simply retrieves the API version from remote server, 
    while the second is a bit more complex and both fetches queued jobs and 
    enqueue a new job.
</p>

<?php
UUP\Web\Component\Script\CodeBox::outputFile($this->params->getParam("file", "client1.java"), false, [
        "client1.java", "client2.java", "output.txt"
])

?>

<p>
    Output from running client2.java should look something like this:
</p>
<pre class="w3-code">
    2.0
Task: default (Job 2)
    Queued: 2018-11-14 13:05:20.082591[Europe/Stockholm]
    State: crashed
Task: default (Job 2)
    Queued: 2018-11-14 13:03:14.603137[Europe/Stockholm]
    State: crashed
Task: default (Job 2)
    Queued: 2018-11-14 12:59:25.445768[Europe/Stockholm]
    State: success
Task: default (Job 1)
    Queued: 2018-11-14 12:43:35.872742[Europe/Stockholm]
    State: success
        ...
Task: default ()
    Queued: 2018-11-15 16:12:39.264869[Europe/Stockholm]
    State: pending
</pre>
