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

<h3>Filtering queue results</h3>
<p>
    The queue() method takes an filter argument. The system uses enum internal to
    represent filtering, but because PHP lacks a native enum type these can't be 
    used i.e. with the SOAP service.
</p>
<div class="w3-panel w3-blue">
    <p>
        This page lists the possible values for the sort argument with descriptions 
        for their values. Use none for no filtering at all.
    </p>
</div>

<p>
    An job is in etiher one of three phases. The first one is pending, the seconds
    is running and the third is finished. These enums filter on phase:
</p>
<ul class="w3-leftbar w3-border-deep-purple">
    <li><b>pending</b>: Job is queued, but not yet started.</li>
    <li><b>running</b>: Job is running, but not yet finished.</li>
    <li><b>finished</b>: Job has finished (includes all states).</li>
</ul>

<p>
    While running the job can have been transitioned to either one of these states:
</p>
<ul class="w3-leftbar w3-border-deep-purple">
    <li><b>suspend</b>: Job is suspended.</li>
    <li><b>resumed</b>: Job is resumed.</li>
    <li><b>continued</b>: Waiting for sub job to complete.</li>
</ul>

<p>
    The finished job can have these states that can be filtered on:
</p>

<ul class="w3-leftbar w3-border-deep-purple">
    <li><b>success</b>: Finished successful.</li>
    <li><b>warning</b>: Finished with warnings.</li>
    <li><b>error</b>: Finished with errors.</li>
    <li><b>crashed</b>: The job has crashed (i.e. segmentation fault).</li>
</ul>

<p>
    These values filter on pseudo states:
</p>
<ul class="w3-leftbar w3-border-deep-purple">
    <li><b>completed</b>: Finished with success or warnings.</li>
    <li><b>recent</b>: Include recently finished or ongoing jobs.</li>
</ul>

<div class="w3-panel w3-green">
    <p>
        In reality, the interesting filter options are pending, running and
        completed.
</div>
