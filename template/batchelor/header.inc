<?php
/*
 * Copyright (C) 2016-2017 Anders Lövgren (Computing Department at BMC, Uppsala University).
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// 
// Common HTML for navigation menus. 
// 
// Provides an navigation menu including standard and topbar menu, Google translate 
// and search. Tune to suite your needs ;-)
// 

use UUP\Site\Utility\Content\Runtime;

// 
// Get runtime options:
// 
$show = new Runtime($this);

?>

<style>
    html {
        overflow-y: scroll;
    }
    body, h1, h2, h3, h4, h5, h6 {
        font-family: "Lato", sans-serif
    }
    .w3-bar, h1, button {
        font-family: "Montserrat", sans-serif
    }
    .fa-anchor, .fa-coffee {
        font-size:200px
    }
    @media screen {
        .main-content {
            margin-left: 200px;
        }
    }
    @media print {
        .no-print {
            display: none;
            visibility: hidden;
        }     
        .main-content {
            margin-left: 10px;
        }
    }
    @media (max-width: 650px) {
        .panel {
            width: 100%;
        }
    }
    @media (min-width: 650px) {
        .panel {
            min-width: 400px;
        }
    }    
    .goog-te-gadget-simple {
        border: none;
    }
    .goog-te-menu-value > span {
        color: grey;
        font-size: 14px;
    }
    goog-te-menu-frame {
        width: 200px;
        max-width: 200px;
    }

    #topbar-menu a,
    #navbar-menu a {
        text-decoration: none;
    }
    .menu-header-link,
    .menu-header-name {
        margin: 10px 0;
        font-weight: 400;
    }
    .menu-header-name {
        padding: 4px 2px 4px 16px;
    }
    .menu-content {
    }
</style>

<?php if (!$session->authenticated()) : ?>
        <style>
            a[auth="true"],a[auth="1"],a[auth="yes"],
            [auth="true"],[auth="1"],[auth="yes"]{
                display: none;
            }
        </style>
<?php endif; ?>

<!-- Navigation bar -->
<div id="topbar-menu" class="no-print w3-card-2 w3-slim w3-black">
    <div class="w3-bar w3-large">
        <span class="w3-left">
            <?php if ($show->navmenu) : ?>
                    <a class="w3-bar-item w3-hover-white" onclick="toggle_sidenav_open()" href="javascript:void(0)">
                        <i class="fa fa-bars fa-lg"></i>
                    </a>
            <?php endif; ?>
            <?php if ($show->home) : ?>
                    <a class="w3-bar-item w3-hover-white" title="<?= _('Home') ?>" href="<?= $config->location ?>">
                        <i class="fa fa-home fa-lg"></i>
                    </a>
            <?php endif; ?>
            <?php $output->topmenu(false) ?>
        </span>
        <span class="w3-right" id="topbar-sitehome" style="overflow: hidden; display: none">
            <a href="<?= $config->location ?>" class="w3-bar-item w3-hover-white"><?= $config->name ?></a>
        </span>
        <span class="w3-right" id="topbar-tools">
            <?php if ($show->queue) : ?>
                    <a class="w3-bar-item w3-hover-white" title="<?= _('Switch working queue') ?>" onclick="toggle_queue_select()" href="javascript:void(0)">
                        <i class="fa fa-history fa-lg"></i>
                    </a>
            <?php endif; ?>
            <?php if ($show->edit) : ?>
                    <a class="w3-bar-item w3-hover-white" title="<?= _('Open page editor') ?>" onclick="toggle_edit_open('<?= $config->edit['host'] ?>')" href="javascript:void(0)">
                        <i class="fa fa-pencil-square-o fa-lg"></i>
                    </a>
            <?php endif; ?>
            <?php if ($show->translate) : ?>
                    <a class="w3-bar-item w3-hover-white" title="<?= _('Translate this page') ?>" onclick="toggle_translate_open(this)" href="javascript:void(0)">
                        <i class="fa fa-globe fa-lg"></i>
                    </a>
            <?php endif; ?>
            <?php if ($show->search) : ?>
                    <a class="w3-bar-item w3-hover-white" id="search-button" title="<?= _('Search in content') ?>" onclick="toggle_search_open(this)" href="javascript:void(0)">
                        <i class="fa fa-search fa-lg"></i>
                    </a>
            <?php endif; ?>
            <?php if ($show->auth) : ?>
                    <a class="w3-bar-item w3-hover-white" title="<?= _('Open authentication box') ?>" onclick="toggle_logon_open(this)" href="javascript:void(0)">
                        <?php if ($session->authenticated()) : ?>
                                <i class="fa fa-lock fa-lg" id="logon-status"></i>
                        <?php else: ?>
                                <i class="fa fa-unlock-alt fa-lg" id="logon-status"></i>
                        <?php endif; ?>
                    </a>
            <?php endif; ?>
            <?php if ($show->topmenu) : ?>
                    <a class="w3-bar-item w3-hover-white w3-hide-large w3-hide-medium" onclick="toggle_navbar_open()" href="javascript:void(0)">
                        <i class="fa fa-ellipsis-v fa-lg"></i>
                    </a>
            <?php endif; ?>
        </span>
    </div>
</div>

<?php if ($show->queue) : ?>
        <!-- Queue select box -->
        <div id="queue-box" class="panel w3-row-padding w3-padding-16" style="display: none">
            <div class="w3-container w3-right-align">
                <input class="w3-input w3-border" type="text" placeholder="<?= _("Input name of work queue") ?>" id="queue-name">
                <input class="w3-btn w3-deep-orange w3-large search" type="button" onclick="queue_switch()" value="<?= _('Switch') ?>">
                <input class="w3-btn w3-dark-gray w3-large search" type="button" onclick="queue_revert()" value="<?= _('Revert') ?>">
            </div>
        </div>
<?php endif; ?>

<?php if ($show->auth) : ?>
        <!-- Logon status box -->
        <div id="logon-box" class="panel w3-row-padding w3-padding-16" style="display: none;">
            <div class="w3-container w3-right-align">
                <div class="w3-input w3-border-0" id="logon_status_element"></div>
                <?php if ($session->authenticated()) : ?>
                        <input class="w3-btn w3-blue w3-large" type="button" onclick="window.location = '<?= $config->url($config->auth['logon']) ?>'" value="<?= _('Details') ?>">
                        <input class="w3-btn w3-red w3-large" type="button" onclick="window.location = '<?= $config->url($config->auth['logoff']) ?>'" value="<?= _('Logoff') ?>">
                <?php else: ?>
                        <input class="w3-btn w3-orange w3-large" type="button" onclick="window.location = '<?= $config->url($config->auth['logon']) . '?user=1' ?>'" value="<?= _('Logon') ?>">
                <?php endif; ?>
            </div>
        </div>
<?php endif; ?>

<?php if ($show->translate) : ?>
        <!-- Language selection and translation -->
        <div id="language-box" class="panel w3-row-padding w3-padding-16" style="display: none">
            <div class="w3-container w3-right-align">
                <div class="w3-input w3-border" id="google_translate_element"></div>
                <input class="w3-btn w3-blue w3-large" type="button" onclick="#" value="<?= _('Google Translate') ?>">
            </div>
        </div>
<?php endif; ?>

<?php if ($show->search) : ?>
        <!-- Content search box -->
        <div id="search-box" class="panel w3-row-padding w3-padding-16" style="display: none">
            <div class="w3-container w3-right-align">
                <input class="w3-input w3-border" type="text" onchange="save_search(this)" placeholder="Type search text..">
                <input class="w3-btn w3-green w3-large search" type="button" onclick="send_search('<?= $config->site ?>')" value="<?= _('Search Site') ?>">
            </div>
        </div>
<?php endif; ?>

<?php if ($show->edit) : ?>
        <!-- Page edit box -->
        <div id="edit-box" class="panel w3-row-padding w3-padding-16" style="display: none">
            <div class="w3-container w3-right-align">
                <div class="w3-input w3-border-0"><?= _("Edit page content in inline mode") ?></div> 
                <?php if (!$this->params->hasParam('path')) : ?>
                        <input id="input-edit-save" class="w3-btn w3-deep-orange w3-large" type="button" onclick="edit_save(event)" value="<?= _('Save') ?>" title="<?php _("Save changes made on this page") ?>">
                        <input id="input-edit-open" class="w3-btn w3-blue-grey w3-large" type="button" onclick="edit_open(event)" value="<?= _('Open') ?>" title="<?php _("Open this location in editor") ?>">
                <?php else : ?>
                        <input id="input-edit-save" class="w3-btn w3-deep-orange w3-large" type="button" onclick="edit_save(event)" value="<?= _('Save') ?>" title="<?php _("Save changes made on this page") ?>">
                        <input id="input-edit-open" class="w3-btn w3-blue-grey w3-large" type="button" onclick="edit_close(event)" value="<?= _('Close') ?>" title="<?php _("Close edit mode for this location") ?>">
                <?php endif; ?>
            </div>
        </div>
<?php endif; ?>

<!-- Navigation menu (for small devices) -->
<div id="navbar-menu" class="w3-bar w3-animate-top w3-card-2 w3-xlarge w3-hide-medium w3-hide-large panel no-print" style="display: none">
    <?php $output->topmenu(true) ?>            
</div>

<!-- Main content overlay with 50% opaqueness when sidebar menu is opened -->
<div class="w3-overlay w3-animate-opacity" onclick="close_overlay()" style="cursor:pointer" id="page-overlay"></div>
