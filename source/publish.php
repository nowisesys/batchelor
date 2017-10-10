<?php

// -------------------------------------------------------------------------------
//  Copyright (C) 2011-2017 Anders Lövgren
//
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
//
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details.
// -------------------------------------------------------------------------------
// 
// Publish job results.
// 
//
// Get configuration.
// 
include "../conf/config.inc";

// 
// Include UI support:
// 
include "../include/ui.inc";

//
// Include support functions:
// 
include "../include/common.inc";
include "../include/publish.inc";
include "../include/download.inc";

if (file_exists("../include/hooks.inc")) {
        include("../include/hooks.inc");
}

if (!defined("PUBLISH_PREVENT_BROWSING")) {
        define("PUBLISH_PREVENT_BROWSING", false);
}

// 
// The error handler.
// 
function error_handler($type)
{
        // 
        // Redirect caller back to queue.php and let it report an error.
        // 
        header("Location: queue.php?show=queue&error=details&type=$type");
}

// 
// The event handler were all action (i.e. modify) takes place.
// 
function publish_action_handler()
{
        if ($_REQUEST['action'] == "add") {
                if (($name = publish_add($GLOBALS['jobdir'], $_REQUEST['title'], $_REQUEST['descr']))) {
                        header(sprintf("Location: publish.php?action=show&name=%s", $name));
                }
        }
        if ($_REQUEST['action'] == "edit") {
                $name = publish_get_name($GLOBALS['jobdir']);
                if (!publish_is_owner($_COOKIE['hostid'], $name)) {
                        put_error("You are not the owner of this published job");
                        return false;
                }
                if ((publish_edit($name, $_REQUEST['title'], $_REQUEST['descr']))) {
                        header(sprintf("Location: publish.php?action=show&name=%s", $name));
                }
        }
        if ($_REQUEST['action'] == "remove") {
                if (!publish_is_owner($_COOKIE['hostid'], $_REQUEST['name'])) {
                        put_error("You are not the owner of this published job");
                        return false;
                }
                if (publish_remove($_REQUEST['name'])) {
                        header("Location: publish.php?action=list");
                }
        }
        if ($_REQUEST['action'] == "download") {
                switch ($_REQUEST['content']) {
                        case "result":
                                if (publish_send_result($_REQUEST['name'])) {
                                        exit(0);
                                }
                                break;
                        case "indata":
                                if (publish_send_indata($_REQUEST['name'])) {
                                        exit(0);
                                }
                                break;
                        default:
                                put_error(sprintf("Invalid content type %s requested", $_REQUEST['content']));
                                break;
                }
        }
}

// 
// Show form for publish job:
// 
function publish_result_form()
{
        if ($_REQUEST['action'] == "edit") {
                $name = publish_get_name($GLOBALS['jobdir']);
                $desc = publish_get_description($name);
                $head = publish_get_title($name);
                $icon = "published.png";
                $mode = "Modify";
        } else {
                $desc = "";
                $head = "";
                $icon = "publish.png";
                $mode = "Publish";
        }

        printf("<h2><img src=\"icons/nuvola/%s\"> Publish Job ID %s</h2>\n", $icon, $_REQUEST['jobid']);

        printf("<p>Use this form for publishing the job result. The title should be a ");
        printf("short descriptive text suitable as an header in the list of all published ");
        printf("jobs. The description could contain one or more blocks of text and is only ");
        printf("visible when a user chooses to view this specific job.</p>\n");

        printf("<form action=\"publish.php\" method=\"GET\">\n");
        printf("<input type=\"hidden\" name=\"save\" value=\"yes\"/>\n");
        printf("<input type=\"hidden\" name=\"action\" value=\"%s\"/>\n", $_REQUEST['action']);
        printf("<input type=\"hidden\" name=\"jobid\" value=\"%s\"/>\n", $_REQUEST['jobid']);
        printf("<input type=\"hidden\" name=\"result\" value=\"%s\"/>\n", $_REQUEST['result']);
        printf("<label for=\"title\">Title:</label>\n");
        printf("<input type=\"text\" name=\"title\" value=\"%s\" size=\"65\"/>\n", $head);
        printf("<br/>\n");
        printf("<label for=\"descr\">Description:</label>\n");
        printf("<textarea name=\"descr\"\"/>%s</textarea>\n", $desc);
        printf("<br/>\n");
        printf("<br/>\n");
        printf("<label for=\"submit\">&nbsp;</label>\n");
        printf("<input type=\"submit\" name=\"submit\" value=\"%s\"/>\n", $mode);
        printf("<input type=\"reset\"/>\n");
        printf("</form>\n");

        if ($_REQUEST['action'] == "edit") {
                printf("<p>Click on the remove button below to stop publishing the result for this job. ");
                printf("This job itself is not affected by this action, only its publish status is changed.</p>\n");
                printf("<form action=\"publish.php\" method=\"GET\">\n");
                printf("<input type=\"hidden\" name=\"name\" value=\"%s\"/>\n", $name);
                printf("<input type=\"hidden\" name=\"action\" value=\"remove\"/>\n");
                printf("<label for=\"submit\">&nbsp;</label>\n");
                printf("<input type=\"submit\" name=\"submit\" value=\"Remove\"/>\n");
                printf("</form>\n");

                $url = publish_get_url($GLOBALS['jobdir']);
                printf("<hr><p>The direct link (URL) <a href=\"%s\">showing details</a> for this published job are:<br>%s</p>\n", $url, $url);
        }
}

// 
// List all published jobs.
// 
function publish_list()
{
        $options = (object) array(
                    "none"   => "None", "edit"   => "Editable", "others" => "Others"
        );

        if (isset($_REQUEST['filter'])) {
                $filter = $_REQUEST['filter'];
        } else {
                $filter = "none";
        }

        if ($filter != "none") {
                printf("<h2><img src=\"icons/nuvola/published.png\"> %s published jobs:</h2>\n", $options->$filter);
        } else {
                printf("<h2><img src=\"icons/nuvola/published.png\"> All published jobs:</h2>\n");
        }

        if (PUBLISH_PREVENT_BROWSING) {
                put_error("Browsing of published results is forbidden by the system configuration.");
                return false;
        }

        $data = publish_get_data();

        printf("<p><form action=\"publish.php\">\n");
        printf("<input type=\"hidden\" name=\"action\" value=\"list\">\n");
        printf("<label for=\"filter\">Filter:</label>\n");
        printf("<select name=\"filter\">\n");
        foreach ($options as $name => $value) {
                if ($filter == $name) {
                        printf("<option value=\"%s\" selected>%s</option>\n", $name, $value);
                } else {
                        printf("<option value=\"%s\">%s</option>\n", $name, $value);
                }
        }
        printf("</select>\n");
        printf("<input type=\"submit\" value=\"Refresh\">\n");
        printf("</form>\n");

        printf("<ul>\n");
        foreach ($data as $name => $title) {
                if (publish_is_owner($_COOKIE['hostid'], $name)) {
                        if ($filter == "none" || $filter == "edit") {
                                printf("<li>%s<br/><a href=\"?action=show&name=%s\">Details</a></li><br/>\n", $title, $name);
                        }
                } else {
                        if ($filter != "edit") {
                                printf("<li>%s<br/><a href=\"?action=show&name=%s\">Details</a></li><br/>\n", $title, $name);
                        }
                }
        }
        printf("</ul>\n");
}

// 
// Show details for an individual published job.
// 
function publish_show()
{
        $path = publish_get_path($_REQUEST['name']);
        $desc = publish_get_description($_REQUEST['name']);
        $head = publish_get_title($_REQUEST['name']);

        if (function_exists("published_job_hook")) {
                published_job_hook($path, $head, $desc);
        } else {
                printf("<h2><img src=\"icons/nuvola/published.png\"> %s</h2>\n", $head);
                printf("<p>%s</p>\n", str_replace("\n", "<br/>", $desc));
                printf("<h2>Resources:</h2>\n");
                printf("<p>Use these resource URL's when linking to this published job result on external web pages:</p>\n");
                printf("<ul>\n");
                printf("<li><a href=\"?action=download&name=%s&content=result\">Download result</a></li>\n", $_REQUEST['name']);
                printf("<li><a href=\"?action=download&name=%s&content=indata\">Download indata</a></li>\n", $_REQUEST['name']);
                printf("</ul>\n");
                if (publish_is_owner($_COOKIE['hostid'], $_REQUEST['name'])) {
                        $jobdir = readlink(sprintf("%s/jobdir", $path));
                        $result = basename($jobdir);
                        $jobid = file_get_contents(sprintf("%s/jobid", $jobdir));
                        printf("<h2>Actions:</h2>\n");
                        printf("<p>The action section is invisible for other users, but shown here beacuse you are the owner of this published job. ");
                        printf("It provides some convenient shortcuts for managing the result publication of this job.</p>\n");
                        printf("<p>Clicking on 'Stop publishing' will remove the published metadata (title and description) ");
                        printf("and stop sharing the job result. The job, its indata and result will remain unchanged by ");
                        printf("this operation and the job can be shared again later.</p>\n");
                        printf("<ul>\n");
                        printf("<li><a href=\"?action=edit&jobid=%s&result=%s\">Edit published job</a></li>\n", $jobid, $result);
                        printf("<li><a href=\"?action=remove&name=%s\">Stop publishing</a></li>\n", $_REQUEST['name']);
                        printf("</ul>\n");
                }
        }
}

function print_title()
{
        if (isset($_REQUEST['jobid'])) {
                printf("%s - Publish Job ID %s", HTML_PAGE_TITLE, $_REQUEST['jobid']);
        } else {
                printf("%s - Publish Jobs", HTML_PAGE_TITLE);
        }
}

function print_body()
{
        printf("<div class=\"publish\">\n");

        if (isset($_REQUEST['action'])) {
                switch ($_REQUEST['action']) {
                        case "show":
                                publish_show();
                                break;
                        case "list":
                                publish_list();
                                break;
                        case "add":
                        case "edit":
                                publish_result_form();
                                break;
                        default:
                                put_error(sprintf("Unknown action %s", $_REQUEST['action']));
                                break;
                }
        } else {
                publish_list();
        }

        if (has_errors()) {
                print_message_box("error", get_last_error());
                return;
        }

        printf("</div>\n");
}

function print_html($what)
{
        switch ($what) {
                case "body":
                        print_body();
                        break;
                case "title":
                        print_title();
                        break;
                default:
                        print_common_html($what);    // Use default callback.
                        break;
        }
}

// 
// Check required parameters.
// 
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "add" || $_REQUEST['action'] == "edit")) {
        if (!isset($_REQUEST['jobid']) || !isset($_REQUEST['result'])) {
                error_handler("params");
        }
        if (!isset($_COOKIE['hostid'])) {
                error_handler("hostid");
        }
        $GLOBALS['jobdir'] = sprintf("%s/jobs/%s/%s", CACHE_DIRECTORY, $_COOKIE['hostid'], $_REQUEST['result']);
}

// 
// Handle add/remove actions. This code will also check all request parameters.
// 
if (isset($_REQUEST['action'])) {
        switch ($_REQUEST['action']) {
                case "add":
                case "edit":
                        if (isset($_REQUEST['save'])) {
                                if (!isset($_REQUEST['title']) || !isset($_REQUEST['descr'])) {
                                        error_handler("params");
                                }
                                publish_action_handler();
                        }
                        break;
                case "remove":
                        if (!isset($_REQUEST['name'])) {
                                error_handler("params");
                        }
                        publish_action_handler();
                        break;
                case "download":
                        if (!isset($_REQUEST['name']) || !isset($_REQUEST['content'])) {
                                error_handler("params");
                        }
                        publish_action_handler();
                        break;
                case "show":
                        if (!isset($_REQUEST['name'])) {
                                error_handler("params");
                        }
                        break;
        }
}
load_ui_template("popup");

?>
