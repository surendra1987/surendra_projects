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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

/**
 * Page containing general report settings
 */

use core\record\tenant;

define('REPORTBUIDLER_MANAGE_REPORTS_PAGE', true);
define('REPORT_BUILDER_IGNORE_PAGE_PARAMETERS', true); // We are setting up report here, do not accept source params.

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/report_forms.php');
require_once($CFG->dirroot . '/totara/core/lib/scheduler.php');

$id = required_param('id', PARAM_INT); // Report builder id.

// Set the page context to the report's context.
$rawreport = $DB->get_record('report_builder', array('id' => $id), '*', MUST_EXIST);

$context = empty($rawreport->tenantid) ? context_system::instance() : tenant::fetch($rawreport->tenantid)->category_context;

$PAGE->set_context($context);
$PAGE->set_title(get_string('edit_report', 'totara_reportbuilder'));

if ($context->contextlevel == CONTEXT_SYSTEM) {
    $adminpage = $rawreport->embedded ? 'rbmanageembeddedreports' : 'rbmanagereports';
    admin_externalpage_setup($adminpage, '', ['id' => $id], '/totara/reportbuilder/general.php');
} else {
    require_capability('totara/reportbuilder:managereports', $context);
    $PAGE->set_url('/totara/reportbuilder/general.php');
    $PAGE->set_pagelayout('admin');
}

$params = [];
if ($context->contextlevel == CONTEXT_COURSECAT) {
    $params['contextid'] = $context->id;
}
$cancel_url = new moodle_url('/totara/reportbuilder/index.php', $params);
navigation_node::override_active_url($cancel_url);

$config = (new rb_config())->set_nocache(true);
$report = reportbuilder::create($id, $config, false); // No access control for managing of reports here.

$output = $PAGE->get_renderer('totara_reportbuilder');

$returnurl = $CFG->wwwroot . "/totara/reportbuilder/general.php?id=$id";

$schedule = array();
if ($report->cache) {
    $cache = reportbuilder_get_cached($id);
    $scheduler = new scheduler($cache, array('nextevent' => 'nextreport'));
    $schedule = $scheduler->to_array();
}

// Form definition.
$record = new stdClass();
$record->id = $report->_id;
$record->fullname = $report->fullname;
$record->summary = $report->summary;
$record->description = $report->description;
$record->descriptionformat = FORMAT_HTML;
$record->hidden = $report->hidden;
$record->recordsperpage = $report->recordsperpage;
if ($report->src->has_hardcoded_visibility) {
    $record->disablehardcodedvisibility = \reportbuilder::get_setting($record->id, 'disable_hardcoded_visibility', 'disable');
}
$record = file_prepare_standard_editor($record, 'description', $TEXTAREA_OPTIONS, context_system::instance(),
    'totara_reportbuilder', 'report_builder', $record->id);

$mform = new report_builder_edit_form(null, array('report' => $report, 'record' => $record));

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
    $todb->timemodified = time();
    $todb->fullname = $fromform->fullname;
    $todb->hidden = $fromform->hidden;
    $todb->summary = $fromform->summary;
    $todb->description_editor = $fromform->description_editor;
    // ensure we show between 1 and 9999 records
    $rpp = min(9999, max(1, (int) $fromform->recordsperpage));
    $todb->recordsperpage = $rpp;
    $todb = file_postupdate_standard_editor($todb, 'description', $TEXTAREA_OPTIONS, $TEXTAREA_OPTIONS['context'],
        'totara_reportbuilder', 'report_builder', $todb->id);

    $DB->update_record('report_builder', $todb);

    // Now make sure we update this setting
    if ($report->src->has_hardcoded_visibility) {
        $disable_hardcoded_visibility = $fromform->disablehardcodedvisibility ?? 0;
        reportbuilder::update_setting($id, 'disable_hardcoded_visibility', 'disable', $disable_hardcoded_visibility);
    }

    $config = (new rb_config())->set_nocache(true);
    $report = reportbuilder::create($id, $config, false); // No access control for managing of reports here.

    \totara_reportbuilder\event\report_updated::create_from_report($report, 'general')->trigger();
    \core\notification::success(get_string('reportupdated', 'totara_reportbuilder'));
    redirect($returnurl);
}

echo $output->header();

echo $output->container_start('reportbuilder-navlinks');
echo $output->view_all_reports_link($report->embedded, $params['contextid'] ?? null);
echo $output->container_end();
echo $output->edit_report_heading($report);

if ($report->get_cache_status() > 0) {
    echo $output->cache_pending_notification($id);
}

$currenttab = 'general';
require('tabs.php');

// display the form
$mform->display();

echo $output->footer();
