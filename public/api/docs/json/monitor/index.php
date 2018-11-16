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

?>

<h3>JSON API (monitor jobs)</h3>
<p>
    These methods supports listing queue and monitor jobs: 
</p>
<a class="w3-btn w3-deep-orange" style="min-width: 80px" href="#queue">queue</a>
<a class="w3-btn w3-deep-orange" style="min-width: 80px" href="#watch">watch</a>
<a class="w3-btn w3-deep-orange" style="min-width: 80px" href="#stat">stat</a>

<?php include("queue.php") ?>
<?php include("watch.php") ?>
<?php include("stat.php") ?>
