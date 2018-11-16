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

<h3>Web services</h3>
<p>
    Job queue management API is implemented using SOAP and JSON providing methods for:
</p>
<ul>
    <li>Running jobs: enqueue, dequeue, suspend, resume</li>
    <li>Monitor jobs: queue, watch, stat</li>
    <li>Reading results: opendir, readdir, fopen</li>
</ul>
<a class="w3-btn w3-deep-orange" href="soap" style="min-width: 90px">SOAP</a>
<a class="w3-btn w3-deep-orange" href="json" style="min-width: 90px">JSON</a>
<p>
    As each user can have multiple queues (useful for sharing queues between 
    co-workers) the select method has been added for easy switch between active
    queues.
</p>
<a class="w3-btn w3-green" href="select" style="min-width: 90px">Select</a>
<p>
    Result from listing the queue can be sorted and filtered. PHP don't support
    native enums, so these are described here for all API types.
</p>
<a class="w3-btn w3-deep-purple" href="sort" style="min-width: 90px">Sort</a>
<a class="w3-btn w3-deep-purple" href="filter" style="min-width: 90px">Filter</a>
