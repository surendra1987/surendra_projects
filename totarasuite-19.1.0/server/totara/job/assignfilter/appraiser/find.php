<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author David Curry <david.curry@totaralearning.com>
 * @package totara_job
 */

use core\record\tenant;

require_once(__DIR__ . '/../../../../config.php');

global $CFG, $DB, $PAGE, $USER;

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/totara/core/dialogs/dialog_content_users.class.php');
require_once($CFG->dirroot.'/totara/core/js/lib/setup.php');
require_once($CFG->dirroot.'/totara/reportbuilder/lib.php');

// Page title
$pagetitle = 'selectappraisers';

///
/// Params
///

// Only return generated tree html
$treeonly = optional_param('treeonly', false, PARAM_BOOL);

///
/// Permissions checks
///

require_login();
$reportid = required_param('reportid', PARAM_INT);
$rawreport = $DB->get_record('report_builder', array('id' => $reportid), '*', MUST_EXIST);
$context = empty($rawreport->tenantid) ? context_system::instance() : tenant::fetch($rawreport->tenantid)->category_context;
$PAGE->set_context($context);

// Check that the user can view the report specified and that the report contains the filter which uses this page.
// If not, then they are not permitted to view all users here.
$canviewreport = reportbuilder::is_capable($reportid, $USER->id);
$reporthasfilter = reportbuilder::contains_filter($reportid, 'job_assignment', 'allappraisers');
if (!($canviewreport and $reporthasfilter)) {
    print_error('accessdenied', 'admin');
}

///
/// Display page
///

// Load dialog content generator
$dialog = new totara_dialog_content_users();
$dialog->set_context($context);
$dialog->urlparams = array('reportid' => $reportid);

// Toggle treeview only display
$dialog->show_treeview_only = $treeonly;

// Load items to display
$dialog->load_items(0);

// Set title
$dialog->selected_title = 'itemstoadd';
$dialog->select_title = '';

// Display
echo $dialog->generate_markup();
