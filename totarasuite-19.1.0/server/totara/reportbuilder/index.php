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
 * @author Simon Coggins <simon.coggins@totaralearning.com>
 * @package totara_reportbuilder
 */

use totara_core\advanced_feature;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');
global $USER;

$debug  = optional_param('debug', 0, PARAM_INT);
$sid = optional_param('sid', '0', PARAM_INT);
$format = optional_param('format', '', PARAM_TEXT); // export format
$contextid = optional_param('contextid', 0, PARAM_INT);

$context = $contextid ? context::instance_by_id($contextid) : context_system::instance();

if ($context->contextlevel != CONTEXT_COURSECAT and $context->contextlevel != CONTEXT_SYSTEM) {
    print_error('invalidcontext');
}

require_capability('totara/reportbuilder:managereports', $context);
$PAGE->set_context($context);

$PAGE->set_title(get_string('manageuserreports','totara_reportbuilder'));

$pageparams = [
    'format' => $format,
    'debug' => $debug,
    'sid' => $sid,
];

if (!empty($context->tenantid)) {
    $pageparams['tenantid'] = $context->tenantid;
}

// Hide "add/remove from quick access menu" for tenant participants only: By providing actualurl (-__-)
$actual_url = '';
if ($context->contextlevel == CONTEXT_SYSTEM) {
    $actual_url = '/totara/reportbuilder/index.php';
    admin_externalpage_setup('rbmanagereports', '', null, $actual_url, array('pagelayout' => 'report'));
} else if ($context->contextlevel == CONTEXT_COURSECAT) {
    $category = $DB->get_record('course_categories', array('id' => $context->instanceid), '*', MUST_EXIST);
    if (!empty($USER->tenantid) and !has_capability('totara/reportbuilder:managereports', context_system::instance()) and !empty($context->tenantid) and $category->parent == 0) {
        admin_externalpage_setup('rbmanagereports', '', null, $actual_url, array('pagelayout' => 'report'));
    } else {
        $PAGE->set_pagelayout('report');
        $PAGE->set_url('/totara/reportbuilder/index.php', array('contextid' => $context->id));
    }
}

$shortname = 'manage_user_reports';
$embeddata = $pageparams;
$embeddata['context'] = $context;
$config = (new rb_config())->set_sid($sid)->set_embeddata($embeddata);
if (!$report = reportbuilder::create_embedded($shortname, $config)) {
    print_error('error:couldnotgenerateembeddedreport', 'totara_reportbuilder');
}

$PAGE->set_button($report->edit_button() . $PAGE->button);

/** @var totara_reportbuilder_renderer $renderer */
$renderer = $PAGE->get_renderer('totara_reportbuilder');

if ($format != '') {
    $report->export_data($format);
    die;
}

\totara_reportbuilder\event\report_viewed::create_from_report($report)->trigger();

$report->include_js();

echo $OUTPUT->header();

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report, $debug);
echo $debughtml;

if ($context->contextlevel != CONTEXT_COURSECAT) {
    $heading = get_string('manageuserreports','totara_reportbuilder');
} else {
    $heading = get_string('manageuserreportsin', 'totara_reportbuilder', $context->get_context_name());
}

$button = null;
if (!advanced_feature::is_disabled('user_reports')) {
    $params = [];
    if (!empty($context->tenantid)) {
        $params['tenantid'] = $context->tenantid;
    }
    $button = $renderer->single_button(
        new moodle_url('/totara/reportbuilder/create.php', $params),
        get_string('createreport', 'totara_reportbuilder'),
        'get'
    );
}

echo $OUTPUT->page_main_heading(get_string('manageuserreports','totara_reportbuilder'), $button);


$report->display_restrictions();

echo $renderer->print_description($report->description, $report->_id);

// Print saved search options and filters.
$report->display_saved_search_options();
$report->display_search();

echo $renderer->result_count_heading($report);

echo $reporthtml;

// Export button.
$renderer->export_select($report, $sid);

echo $OUTPUT->footer();
