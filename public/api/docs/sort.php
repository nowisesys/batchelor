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

<h3>Sorting queue results</h3>
<p>
    The queue() method takes an sort argument. The system uses enum internal to
    represent sorting, but because PHP lacks a native enum type these can't be 
    used i.e. with the SOAP service.
</p>
<div class="w3-panel w3-blue">
    <p>
        This page lists the possible values for the sort argument with descriptions 
        for their values. Use none for no sorting at all.
    </p>
</div>

<ul class="w3-leftbar w3-border-deep-purple">
    <li><b>started</b>: Sort on started datetime.</li>
    <li><b>jobid</b>: Sort on job ID.</li>
    <li><b>state</b>: Sort on job state.</li>
    <li><b>name</b>: Sort on job name.</li>
    <li><b>published</b>: Sort on published mode.</li>
    <li><b>task</b>: Sort on current task.</li>
</ul>

<div class="w3-panel w3-green">
    <p>
        The state, name and task is new options in Batchelor 2.x. Sorting on them
        provides natural sorting, i.e. the string task1 comes before task2.
    </p>
</div>

<div class="w3-panel w3-red">
    <p>
        The jobid and published is provided for backward compatibility and is 
        accepted even though using them no longer makes sense. The job ID is now 
        an UUID and don't provide any ordering. Publishing results are no longer 
        supported, use social media instead.
    </p>
</div>
