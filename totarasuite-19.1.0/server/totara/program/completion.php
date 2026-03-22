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
 * @author Nathan Lewis <nathan.lewis@totaralms.com>
 * @package totara
 * @subpackage totara_program
 */

use totara_program\program;
use totara_tui\output\component;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/program/lib.php');
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');

if (empty($CFG->enableprogramcompletioneditor)) {
    print_error('error:completioneditornotenabled', 'totara_program');
}

require_login();

// Page params.
$progid = required_param('id', PARAM_INT);

// Report params.
$sid = optional_param('sid', '0', PARAM_INT);
$format = optional_param('format', '', PARAM_TEXT);
$debug = optional_param('debug', 0, PARAM_INT);

// Admin check.
$program = new program($progid);
$url = new moodle_url('/totara/program/completion.php', array('id' => $progid));
if ($program->certifid) {
    check_certification_enabled();
    $progorcert = 'certification';
} else {
    check_program_enabled();
    $progorcert = 'program';
}

// Capability check.
$programcontext = $program->get_context();
if (!has_capability('totara/program:editcompletion', $programcontext)) {
    print_error('error:nopermissions', 'totara_program');
}

// Set up page.
$PAGE->set_url($url);
$PAGE->set_program($program);
$PAGE->set_title($program->fullname);
$PAGE->set_heading($program->fullname);

if (has_capability('totara/program:cloneprogram', $programcontext)) {
    $clone_button = new component('totara_program/components/clone/CloneButton', [
        'id' => $program->id,
        'fullname' => $program->fullname,
        'isCertif' => $program->is_certif()
    ]);
    $clone_button->register($PAGE);
    $PAGE->set_button($PAGE->button . $OUTPUT->render($clone_button));
}

/** @var totara_reportbuilder_renderer $renderer */
$renderer = $PAGE->get_renderer('totara_reportbuilder');

// Verify global restrictions.
$reportrecord = $DB->get_record('report_builder', array('shortname' => 'program_membership'));
$globalrestrictionset = rb_global_restriction_set::create_from_page_parameters($reportrecord);

// Load report.
$config = (new rb_config())
    ->set_sid($sid)
    ->set_embeddata(['programid' => $progid])
    ->set_global_restriction_set($globalrestrictionset);
if ($progorcert == 'certification') {
    if (!$report = reportbuilder::create_embedded('certification_membership', $config)) {
        print_error('error:couldnotgenerateembeddedreport', 'totara_reportbuilder');
    }
} else {
    if (!$report = reportbuilder::create_embedded('program_membership', $config)) {
        print_error('error:couldnotgenerateembeddedreport', 'totara_reportbuilder');
    }
}

if ($format != '') {
    $report->export_data($format);
    die;
}

$heading = format_string($program->fullname);
if ($program->certifid) {
    $heading = get_string('header:certification', 'totara_certification', $heading);
}

$data = $program->get_current_status();
$header = new component('totara_program/components/manage_program/Header', [
    'fullname' => $heading,
    'affected' => (array)$data,
]);
$header->register($PAGE);

echo $renderer->header();

echo $OUTPUT->render($header);
$exceptions = $program->get_exception_count();

$currenttab = 'completion';
require_once($CFG->dirroot . '/totara/program/tabs.php');

$checkallurl = new moodle_url('/totara/program/check_completion.php', array('progid' => $progid, 'progorcert' => $progorcert));
echo html_writer::tag('ul', html_writer::tag('li', html_writer::link($checkallurl,
    get_string('checkcompletions', 'totara_program'))));

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report, $debug);
echo $debughtml;

$report->display_restrictions();

echo $renderer->print_description($report->description, $report->_id);

$report->include_js();

// Print saved search options and filters.
$report->display_saved_search_options();
$report->display_search();
$report->display_sidebar_search();

echo $renderer->result_count_heading($report);

echo $reporthtml;

// Export button.
$renderer->export_select($report, $sid);

echo $renderer->footer();
