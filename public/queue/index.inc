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

use Batchelor\WebService\Types\JobState;

// 
// The queued variable is provided by controller/component and contains the 
// array of queued jobs to render by this template. Modify this script to suite
// your application. 
// 
// Each job gets listed in their own list box inside the unordered list. The 
// builtin operations has buttons float to right. Add any application specific 
// buttons float left.
// 
// The box layout is like this:
// 
//      +---------------------------------------+
//      | <datetime>                <state>  [x]|
//      | <name>                     <task>     |
//      |                                       |
//      | <app-buttons>       <std-buttons>     |
//      +---------------------------------------+
// 
// The output from this script might be loaded by AJAX. JavaScript is enabled 
// when loaded by AJAX-request, so you can use it when implementing your business 
// logic. The builtin function content_replace() can be used to load other views
// and display in an target id:
// 
//      onclick="content_replace(event, 'some-id', 'view')"
// 
// Cross origin is not supported, modify content_replace() in your theme to add
// support. Use page-content as target ID to replace main content.
// 

function getClasses(JobState $state): array
{
        if ($state->isPending()) {
                return [
                        'common' => "w3-white w3-round w3-border w3-border-teal",
                        'delete' => "",
                        'result' => "w3-disabled",
                        'detail' => ""
                ];
        } elseif ($state->isCompleted()) {
                return [
                        'common' => "w3-white w3-round w3-border w3-border-teal",
                        'delete' => "",
                        'result' => "",
                        'detail' => ""
                ];
        } else {
                return [
                        'common' => "w3-white w3-round w3-border w3-border-deep-orange",
                        'delete' => "",
                        'result' => "w3-disabled",
                        'detail' => ""
                ];
        }
}

?>

<div class="w3-margin-top">
    <div class="w3-dropdown-hover w3-right">
        <button class="w3-button w3-deep-orange w3-small" style="min-width: 90px"><?= ucfirst($filter) ?></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=recent')" class="w3-bar-item w3-button"><?= _("Recent") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=completed')" class="w3-bar-item w3-button"><?= _("Completed") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=none')" class="w3-bar-item w3-button"><?= _("Show all") ?></a>
            <hr class="menu-separator" style="margin: 8px 16px">
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=pending')" class="w3-bar-item w3-button"><?= _("Pending") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=running')" class="w3-bar-item w3-button"><?= _("Running") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=finished')" class="w3-bar-item w3-button"><?= _("Finished") ?></a>
            <hr class="menu-separator" style="margin: 8px 16px">
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=success')" class="w3-bar-item w3-button"><?= _("Success") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=warning')" class="w3-bar-item w3-button"><?= _("Warning") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=error')" class="w3-bar-item w3-button"><?= _("Error") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=<?= $sorter ?>&filter=crashed')" class="w3-bar-item w3-button"><?= _("Crashed") ?></a>
        </div>
    </div>
    <div class="w3-dropdown-hover w3-right" style="margin-right: 3px">
        <button class="w3-button w3-deep-orange w3-small" style="min-width: 90px"><?= ucfirst($sorter) ?></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a onclick="content_replace(event, 'page-content', 'index?sort=state&filter=<?= $filter ?>')" class="w3-bar-item w3-button"><?= _("State") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=started&filter=<?= $filter ?>')" class="w3-bar-item w3-button"><?= _("Started") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=name&filter=<?= $filter ?>')" class="w3-bar-item w3-button"><?= _("Name") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=task&filter=<?= $filter ?>')" class="w3-bar-item w3-button"><?= _("Task") ?></a>
            <a onclick="content_replace(event, 'page-content', 'index?sort=jobid&filter=<?= $filter ?>')" class="w3-bar-item w3-button"><?= _("Job ID") ?></a>
        </div>
    </div>
    <div class="w3-dropdown-hover w3-right" style="margin-right: 3px">
        <button class="w3-button w3-deep-purple w3-small" style="min-width: 90px"><?= _("Submit") ?></button>
        <div class="w3-dropdown-content w3-bar-block w3-border">
            <a onclick="content_replace(event, 'page-content', 'submit?type=data')" class="w3-bar-item w3-button"><?= _("Data") ?></a>
            <a onclick="content_replace(event, 'page-content', 'submit?type=file')" class="w3-bar-item w3-button"><?= _("File") ?></a>
        </div>
    </div>
    <h3><?= _("Job queue") ?></h3>
</div>

<ul class="w3-ul w3-animate-right">
    <?php foreach ($queued as $job) : ?>
            <?php $classes = getClasses($job->status->state); ?>
            <div class="w3-panel w3-padding w3-card w3-round job-item" id="<?= $job->identity->jobid ?>" data-root="<?= $job->identity->result ?>">
                <div class="job-header" onclick="show_next_sibling(this)">
                    <span class="job-date"><?= strftime("%x %X", strtotime($job->status->queued->date)) ?></span>
                    <span class="job-state w3-right w3-button w3-small w3-<?= $job->status->state->getColor() ?>" style="min-width: 80px"><?= $job->status->state->getValue() ?></span>
                    <span class="job-task w3-right w3-button w3-small w3-light-gray" style="min-width: 80px"><?= $job->submit->task ?></span>
                </div>
                <div class="w3-hide-small">
                    <br>
                    <div class="job-ident">
                        <span class="job-name"><?= $job->submit->name ?></span>
                    </div>
                    <br>
                    <div class="job-buttons">
                        <!-- application specific buttons -->
                        <span class="job-app-buttons">
                        </span>
                        <!-- standard system buttons -->
                        <span class="job-std-buttons w3-right">
                            <a class="w3-btn <?= $classes['common'] ?> <?= $classes['delete'] ?>" onclick=delete_queued_job('<?= json_encode($job->identity) ?>')><?= $job->status->state->getRemove() ?></a>
                            <a class="w3-btn <?= $classes['common'] ?> <?= $classes['result'] ?>" onclick="file_download(event, '<?= http_build_query($job->identity) ?>&name=result')"><?= _("Result") ?></a>
                            <a class="w3-btn <?= $classes['common'] ?> <?= $classes['detail'] ?>" onclick="content_replace(event, 'page-content', 'details?<?= http_build_query($job->identity) ?>')"><?= _("Details") ?></a>
                        </span>
                    </div>
                </div>
            </div>
    <?php endforeach; ?>
</ul>

<?php if (count($queued) == 0): ?>
        <div class="w3-panel w3-blue-gray w3-animate-opacity">
            <h3><?= _("Nothing to display") ?></h3>
            <p><?= _("Current filter options did not select any jobs for display when query the batch queue.") ?></p>
        </div>
<?php else : ?>
        <p>
            <a class="w3-btn" onclick="delete_listed_jobs('<?= _("This will delete all listed jobs. OK to continue?") ?>')"><?= _("Remove all") ?></a>
        </p>
<?php endif; ?>

<script src="common.js?v=1.1" onajax="run"></script>
