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
 * Page for displaying user generated reports
 */

require_once(__DIR__ . '/../../config.php');
global $CFG, $PAGE, $DB, $SITE, $USER;
require_once($CFG->dirroot . '/totara/reportbuilder/lib.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

use core\lock\lock_config;
use totara_reportbuilder\event\report_viewed;

$id = required_param('id', PARAM_INT);
$sid = optional_param('sid', '0', PARAM_INT);
$debug = optional_param('debug', 0, PARAM_INT);
$format = optional_param('format', '', PARAM_COMPONENT);

require_login();

// We can rely on the report builder record existing here as there is no way to get directly to report.php.
$reportrecord = $DB->get_record('report_builder', array('id' => $id), '*', MUST_EXIST);

$context = context_system::instance();
if (!empty($reportrecord->tenantid)) {
    $context = core\record\tenant::fetch($reportrecord->tenantid)->category_context;
}
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/totara/reportbuilder/report.php', array('id' => $id)));
$PAGE->set_totara_menu_selected('\totara_core\totara\menu\myreports');
$PAGE->set_pagelayout('noblocks');


// Embedded reports can only be viewed through their embedded url.
if ($reportrecord->embedded) {
    print_error('cannotviewembedded', 'totara_reportbuilder');
}

// Verify global restrictions.
$globalrestrictionset = rb_global_restriction_set::create_from_page_parameters($reportrecord);

// New report object.
$config = new rb_config();
$config->set_sid($sid)->set_global_restriction_set($globalrestrictionset);
$report = reportbuilder::create($id, $config);

// We only allow one (non-embedded) report or export to run at once.
$is_locked_out = false;
if (!$report->embedded || $format != '') {
    // We only allow one (non-embedded) report or export to run at once. We create a user-specific lock so
    // that this user can't start another report until this one is complete.
    $key = "execute_report_" . $USER->id;
    $lock_factory = lock_config::get_lock_factory('report_builder');
    $lock = $lock_factory->get_lock($key, 2);
    $is_locked_out = ($lock == false); // If we failed to obtain a lock, because it is already in use, then we are locked out.
}

// We wrap whole the execution in a try/finally so that we can release the lock if there is a problem.
try {
    $report->handle_pre_display_actions();

    // If the lock is not available then we render the report page, rather than doing the export.
    if ($format != '' && $lock) {
        $report->export_data($format, true, $lock);
        die;
    }

    report_viewed::create_from_report($report)->trigger();

    $PAGE->requires->string_for_js('reviewitems', 'block_totara_alerts');
    $report->include_js();

    $fullname = format_string($report->fullname, true, ['context' => $context]);
    $pagetitle = get_string('report', 'totara_reportbuilder').': '.$fullname;

    $PAGE->set_title($pagetitle);
    $PAGE->set_button($report->edit_button());
    $PAGE->navbar->add(get_string('reports', 'totara_core'), new moodle_url('/my/reports.php'));
    $PAGE->navbar->add($fullname);
    $PAGE->set_heading($SITE->fullname);

    /** @var totara_reportbuilder_renderer $output */
    $output = $PAGE->get_renderer('totara_reportbuilder');

    echo $output->header();

    if ($report->has_disabled_filters()) {
        echo $output->notification(get_string('filterdisabledwarning', 'totara_reportbuilder'), 'warning');
    }

    $report->display_redirect_link();
    $report->display_restrictions();

    // Display heading including filtering stats.
    $heading = $fullname;
    echo $output->render_from_template('totara_reportbuilder/report_heading', [
        'reportid' => $id,
        'heading' => $heading,
        'fullname' => $report->fullname,
        'resultcount' => $output->result_count_info($report),
        'can_edit' => has_capability('totara/reportbuilder:managereports', $context),
        'heading_level' => 1,
    ]);

    // print report description if set
    echo $output->print_description($report->description, $report->_id);

    // Print saved search options and filters.
    $report->display_saved_search_options();
    $report->display_search();
    $report->display_sidebar_search();

    echo $output->result_count_heading($report, $output->showhide_button($report));

    ob_start();
    // Export button.
    $output->export_select($report, $sid);
    $export = ob_get_clean();

    $footer = $output->footer();

    // This must be done after the header and before any other use of the report.
    if ($is_locked_out) {
        echo html_writer::tag('p', get_string('execution_lock_warning', 'totara_reportbuilder'));
    } else {
        list($tablehtml, $debughtml) = $output->report_html($report, $debug);
        echo $debughtml . $tablehtml;
    }

    echo $export . $footer;
} finally {
    if (!empty($lock)) {
        // Release the lock we created earlier, to allow the user to run another report.
        $lock->release();
    }
}
