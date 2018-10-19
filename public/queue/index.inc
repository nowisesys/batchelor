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

// 
// The queued variable is provided by controller/component and contains the 
// array of queued jobs to render by this template.
// 

?>

<div class="w3-margin-top">
    <a class="w3-btn w3-deep-orange w3-right" style="min-width: 90px"><?= $filter ?></a>
    <h3><?= _("Job queue") ?></h3>
</div>

<ul class="w3-ul">
    <?php foreach ($queued as $job) : ?>
            <?php print_r($job); ?>
    <?php endforeach; ?>
</ul>
