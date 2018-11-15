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

<h3>SOAP API</h3>

<p>
    The service API can be browsed online using the builtin, self-documentation 
    provided by the SOAP library being used.
</p>
<a class="w3-btn w3-deep-orange" href='<?= $this->config->url("api/soap/?docs=1") ?>'>Documentation</a>

<p>
    For consuming the SOAP service use this WSDL link:
</p>
<div class="w3-code">
    <?= $this->config->getUrl("api/soap/?wsdl=1", true) ?>
</div>

<h4>Generate proxy classes</h4>
<p>
    These are the steps to perform to start communicate with the SOAP service:
</p>
<ol>
    <li>Generate proxy classes from WSDL</li>
    <li>Compile the generate source code</li>
    <li>Link the proxy client with your application</li>
</ol>

<h4>Simple client application</h4>
<p>
    On the client page we will develop a simple client and use it to call some 
    methods exposed by the web service.
</p>
<a class="w3-btn w3-deep-orange" href="client">Client application</a>
