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

<div class="w3-margin-top">
    <button class="w3-button w3-deep-orange w3-small w3-right" style="min-width: 90px; margin-right: 3px" onclick="display_tab('submit-file')"><?= _("File") ?></button>
    <button class="w3-button w3-deep-orange w3-small w3-right" style="min-width: 90px; margin-right: 3px" onclick="display_tab('submit-data')"><?= _("Data") ?></button>
    <button class="w3-button w3-deep-purple w3-small w3-right" style="min-width: 90px; margin-right: 3px" onclick="content_replace(event, 'page-content', 'index?sort=state&filter=recent')"><?= _("Queue") ?></button>
    <h3><?= _("Submit job") ?></h3>
</div>

<div id="error-dialog" class="w3-panel w3-red w3-animate-opacity" 
     style="display: none; cursor: pointer" onclick="this.style.display = 'none'">
    <h3><?= _("Oops, something went wrong") ?></h3>
    <p>Some error message</p>
</div>

<div style="width: 100%; max-width: 800px">
    <div id="submit-data" class="submit w3-animate-left" style="display: <?= $type == 'data' ? 'block' : 'none' ?>">
        <form class="w3-container">
            <p>
                <textarea class="w3-input w3-border w3-round" cols="60" rows="5"></textarea>
            </p>
            <div class="w3-animate-opacity" id="submit-advanced-option" style="display: none">
                <p>
                    <label for="submit-name"><?= _("Name:") ?></label>
                    <input class="w3-input" type="text" id="submit-name" placeholder="<?= _("An optional name for this job") ?>"/>
                </p>
                <p>
                    <label for="submit-task"><?= _("Task:") ?></label>
                    <input class="w3-input" type="text" id="submit-task" placeholder="<?= _("The task processor to address for this job") ?>"/>
                </p>
            </div>
            <p>
                <input class="w3-check" type="checkbox" onclick="show_advanced_options(this)"/>
                <label for="submit-advanced"><?= _("Show advanced options") ?></label>
            </p>
            <p>
                <a onclick="submit_data(this)" class="w3-btn"><?= _("Submit") ?></a>
            </p>
        </form>
        <div class="w3-panel w3-indigo">
            <p>
                <?= _("Use the text area for defining job data input.") ?>
                <?= _("You can also add URL from where data can be downloaded.") ?>
                <?= _("One job will be enqueued for each URL.") ?>
            </p>
        </div>
    </div>
    <div id="submit-file" class="submit w3-animate-right" style="display: <?= $type == 'file' ? 'block' : 'none' ?>">
        FILE
    </div>
</div>

<script src="common.js"></script>
<script src="submit.js"></script>
