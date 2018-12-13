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

uasort($files, static function($a, $b) {
        return strcmp($a->name, $b->name);
});

$logs = array_filter($files, static function ($file) {
        return preg_match('|\.log$|', $file->name);
});
$data = array_filter($files, static function($file) {
        return preg_match('|indata.*|', $file->name);
});

$messages = [];

if ($status->state->isPending()) {
        $messages[] = "This job has not yet been run";
}
if ($status->state->isStarted()) {
        $messages[] = "This job is currently running";
}
if ($status->state->isFinished()) {
        $messages[] = "This job has finished run";
}
if (count($logs)) {
        $messages[] = "Each tasks output is logged in a separate logfile";
}
if (count($data)) {
        $messages[] = "Use the indata button for downloading input data";
}
if (count($files)) {
        $messages[] = "The file explorer can be used to browse, view and download i.e. intermediate files";
}

function get_params($ident, $file)
{
        if (is_string($file)) {
                return http_build_query($ident) . '&name=' . $file;
        } else {
                return http_build_query($ident) . '&' . http_build_query($file);
        }
}

?>

<!-- Job progress header -->
<div class="w3-margin-top w3-animate-opacity">
    <h3><?= _("Details") ?></h3>
    <div class="w3-row">
        <?php if ($status->queued): ?>
                <div class="w3-third w3-panel w3-leftbar w3-border-blue">
                    <i class="far fa-clock"></i> <b><?= _("Queued:") ?></b><br>
                    <?= strftime("%x %X", strtotime($status->queued->date)) ?>
                </div>
        <?php endif; ?>
        <?php if ($status->started): ?>
                <div class="w3-third w3-panel w3-leftbar w3-border-orange">
                    <i class="far fa-clock"></i> <b><?= _("Started:") ?></b><br>
                    <?= strftime("%x %X", strtotime($status->started->date)) ?>
                </div>
        <?php endif; ?>
        <?php if ($status->finished): ?>
                <div class="w3-third w3-panel w3-leftbar w3-border-green">
                    <i class="far fa-clock"></i> <b><?= _("Finished:") ?></b><br>
                    <?= strftime("%x %X", strtotime($status->finished->date)) ?>
                </div>
        <?php endif; ?>
    </div>
</div>

<!-- Information about job and functions below -->
<div class="w3-panel w3-blue-gray">
    <p>
        <?= implode(". ", $messages) ?>.
    </p>
    <p>
        <a class="w3-btn w3-white w3-text-dark-gray" onclick="history.back()"><?= _("Return") ?></a>
    </p>
</div>

<!-- Explorer, preview and download buttons -->
<?php if (count($files)) : ?>
        <button class="w3-button w3-deep-orange w3-small" style="min-width: 90px; margin-right: 3px" onclick="toggle_section_display('file-explorer')" ><?= _("Explore") ?></button>
<?php endif; ?>
<?php if (count($data)) : ?>
        <button class="w3-button w3-deep-orange w3-small" style="min-width: 90px; margin-right: 3px" onclick="file_download(event, '<?= get_params($ident, 'indata.ser') ?>')"><?= _("Input") ?></button>
<?php endif; ?>
<?php if (count($logs)): ?>
        <div class="w3-dropdown-hover">
            <button class="w3-button w3-deep-orange w3-small" style="min-width: 90px; margin-right: 3px"><?= _("Logfiles") ?></button>
            <div class="w3-dropdown-content w3-bar-block w3-border">
                <?php foreach ($logs as $file): ?>
                        <a class="w3-bar-item w3-button" onclick="file_preview(event, '<?= get_params($ident, $file) ?>')"><?= $file->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>
<?php endif; ?>

<!-- The file explorer -->
<div id="file-explorer" style="display: none">
    <h3><?= _("File explorer") ?></h3>
    <?php foreach ($files as $file): ?>
            <ul class="w3-ul">
                <li class="w3-leftbar w3-border-blue">
                    <a class="w3-btn w3-light-grey" style="min-width: 90px" onclick="file_preview(event, '<?= get_params($ident, $file) ?>')"><?= _("Preview") ?></a>
                    <a class="w3-btn w3-light-grey" style="min-width: 80px" onclick="file_download(event, '<?= get_params($ident, $file) ?>')"><?= _("Download") ?> </a>
                    <span class="w3-margin-left"><?= $file->name ?></span>
                </li>
            </ul>
    <?php endforeach; ?>
</div>

<!-- The file preview -->
<div id="file-preview"></div>

<script src="common.js?v=1.1" onajax="run"></script>
<script src="details.js?v=1.1" onajax="run"></script>
