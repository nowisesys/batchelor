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
    <h3>
        <?= _("Submit job") ?>
        <span class="w3-hide-small" id="submit-status" style="display: none">(100%)</span>
    </h3>
</div>

<link rel="stylesheet" type="text/css" href="submit.css"/>

<div style="width: 100%; max-width: 800px; margin-top: 30px">
    <div id="submit-data" class="submit w3-animate-left" style="display: <?= $type == 'data' ? 'block' : 'none' ?>">
        <form>
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
                <input class="w3-check show-advanced-options" type="checkbox""/>
                <label for="submit-advanced"><?= _("Show advanced options") ?></label>
            </p>
            <p>
                <a id="submit-data-button" class="w3-btn"><?= _("Submit") ?></a>
            </p>
        </form>
        <div class="w3-panel w3-indigo">
            <?php if (file_exists('submit-help.inc')): ?>
                    <p>
                        <a onclick="toggle_section_display('help-submit-data')" style="cursor: pointer">
                            <i class="fas fa-info-circle w3-xlarge w3-right w3-margin-left"></i>
                        </a>
                    </p>
            <?php endif; ?>
            <p>
                <?= _("Use the text area for defining job data input.") ?>
                <?= _("You can also add URL from where data can be downloaded.") ?>
                <?= _("One job will be enqueued for each URL.") ?>
            </p>
            <?php if (file_exists('submit-help.inc')): ?>
                    <div id="help-submit-data" style="display: none"><?= file_get_contents('submit-help.inc') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div id="submit-file" class="submit w3-animate-right" style="display: <?= $type == 'file' ? 'block' : 'none' ?>">
        <form>
            <div id="file-upload-drop-area" class="w3-center w3-white w3-round-large w3-padding-large w3-margin-bottom">
                <p><?= _("Drag and drop or browse for file(s)") ?></p>
                <input type="file" id="file-input" multiple>
                <label class="w3-btn" for="file-input"><?= _("Browse") ?></label>
            </div>
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
                <input class="w3-check show-advanced-options" type="checkbox""/>
                <label for="submit-advanced"><?= _("Show advanced options") ?></label>
            </p>
        </form>
        <div class="w3-panel w3-indigo">
            <?php if (file_exists('submit-help.inc')): ?>
                    <p>
                        <a onclick="toggle_section_display('help-submit-file')" style="cursor: pointer">
                            <i class="fas fa-info-circle w3-xlarge w3-right w3-margin-left"></i>
                        </a>
                    </p>
            <?php endif; ?>
            <p>
                <?= _("Upload files for batch processing.") ?>
                <?= _("The form will automatic upload the files when dropped in the zone above or accepted in file dialog.") ?>
                <?= _("Each uploaded file will result in one job being enqueued.") ?>
            </p>
            <?php if (file_exists('submit-help.inc')): ?>
                    <div id="help-submit-file" style="display: none"><?= file_get_contents('submit-help.inc') ?></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="w3-padding-top" id="submit-job-result" style="display: none">
        <h4>Submitted jobs</h4>
        <div id="submit-job-listing"></div>
        <div id="submit-job-details"></div>
    </div>
    <div id="submit-finish" class="w3-panel w3-green w3-animate-opacity w3-animate-left" 
         style="display: none; cursor: pointer" onclick="this.style.display = 'none'">
        <p><?= _("All jobs have been enqueued!") ?></p>
    </div>
</div>

<script src="common.js?v=1.1"></script>
<script src="submit.js?v=1.1" onajax="run"></script>
