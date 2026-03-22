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
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

/**
 * Page containing performance report settings
 */

use core\record\tenant;

define('REPORTBUIDLER_MANAGE_REPORTS_PAGE', true);
define('REPORT_BUILDER_IGNORE_PAGE_PARAMETERS', true); // We are setting up report here, do not accept source params.

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/totara/core/lib/scheduler.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/report_forms.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

$id = required_param('id', PARAM_INT); // report id

// Set the page context to the report's context.
$rawreport = $DB->get_record('report_builder', array('id' => $id), '*', MUST_EXIST);

$context = empty($rawreport->tenantid) ? context_system::instance() : tenant::fetch($rawreport->tenantid)->category_context;

$PAGE->set_context($context);
$PAGE->set_title(get_string('edit_report', 'totara_reportbuilder'));

if ($context->contextlevel == CONTEXT_SYSTEM) {
    $adminpage = $rawreport->embedded ? 'rbmanageembeddedreports' : 'rbmanagereports';
    admin_externalpage_setup($adminpage, '', ['id' => $id], '/totara/reportbuilder/performance.php');
} else {
    require_capability('totara/reportbuilder:managereports', $context);
    $PAGE->set_url('/totara/reportbuilder/performance.php');
    $PAGE->set_pagelayout('admin');
}

$params = [];
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $params['contextid'] = $context->id;
}
$cancel_url = new moodle_url('/totara/reportbuilder/index.php', $params);
navigation_node::override_active_url($cancel_url);

$output = $PAGE->get_renderer('totara_reportbuilder');

$returnurl = new moodle_url('/totara/reportbuilder/performance.php', array('id' => $id));

$config = (new rb_config())->set_nocache(true);
$report = reportbuilder::create($id, $config, false); // No access control for managing of reports here.

$schedule = array();
if ($report->cache) {
    $cache = reportbuilder_get_cached($id);
    $scheduler = new scheduler($cache, array('nextevent' => 'nextreport'));
    $schedule = $scheduler->to_array();
}
// form definition
$mform = new report_builder_edit_performance_form(null, array('id' => $id, 'report' => $report, 'schedule' => $schedule));

// form results check
if ($mform->is_cancelled()) {
    redirect($cancel_url);
}
if ($fromform = $mform->get_data()) {

    if (empty($fromform->submitbutton)) {
        \core\notification::error(get_string('error:unknownbuttonclicked', 'totara_reportbuilder'));
        redirect($returnurl);
    }

    $todb = new stdClass();
    $todb->id = $id;
    $todb->cache = isset($fromform->cache) ? $fromform->cache : 0;
    // Only update this setting if we expect to be able to see it. Otherwise we could loose the setting.
    if (get_config('totara_reportbuilder', 'allowtotalcount')) {
        $todb->showtotalcount = !empty($fromform->showtotalcount) ? 1 : 0;
    }
    if (totara_is_clone_db_configured()) {
        $todb->useclonedb = empty($fromform->useclonedb) ? 0 : 1;
    }
    if (get_config('totara_reportbuilder', 'globalinitialdisplay') && !$report->embedded) {
        // Do nothing, don't overwrite initial value.
    } else {
        $todb->initialdisplay = isset($fromform->initialdisplay) ? $fromform->initialdisplay : 0;
    }

    // Export options.
    if (has_capability('totara/reportbuilder:overrideexportoptions', $report->get_context())) {
        $todb->overrideexportoptions = empty($fromform->overrideexportoptions) ? 0 : 1;

        foreach (reportbuilder::get_all_general_export_options(true) as $exporttype => $exportname) {
            $report->update_setting($report->_id, 'exportoption', $exporttype, $fromform->exportoptions[$exporttype]);
        }
    }
    $todb->userlinkcheck = empty($fromform->userlinkcheck) ? 0 : 1;
    $todb->timemodified = time();
    $DB->update_record('report_builder', $todb);

    if ($fromform->cache) {
        reportbuilder_schedule_cache($id, $fromform);
        if (isset($fromform->generatenow) && $fromform->generatenow) {
            reportbuilder_generate_cache($id);
        }
    } else {
        reportbuilder_purge_cache($id, true);
    }

    $config = (new rb_config())->set_nocache(true);
    $report = reportbuilder::create($id, $config, false); // No access control for managing of reports here.

    \totara_reportbuilder\event\report_updated::create_from_report($report, 'performance')->trigger();
    \core\notification::success(get_string('reportupdated', 'totara_reportbuilder'));
    redirect($returnurl);
}

echo $output->header();

echo $output->container_start('reportbuilder-navlinks');
echo $output->view_all_reports_link($report->embedded, $params['contextid'] ?? null);
echo $output->container_end();
echo $output->edit_report_heading($report);

$currenttab = 'performance';
require('tabs.php');

// display the form
$mform->display();

echo $output->footer();
