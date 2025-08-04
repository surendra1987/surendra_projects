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
 * @author Aaron Wells <aaronw@catalyst.net.nz>
 * @author Russell England <russell.england@totaralms.com>
 * @package totara
 * @subpackage plan
 */

use totara_core\advanced_feature;
use totara_reportbuilder\event\report_viewed;

require_once(__DIR__ . '/../../../../config.php');
global $CFG, $USER, $DB, $PAGE, $SITE, $OUTPUT;
require_once($CFG->dirroot.'/totara/reportbuilder/lib.php');
require_once($CFG->dirroot.'/totara/plan/lib.php'); // Is this needed?

require_login();

if (advanced_feature::is_disabled('recordoflearning')) {
    print_error('error:recordoflearningdisabled', 'totara_plan');
}

$userid = optional_param('userid', $USER->id, PARAM_INT); // Which user to show, default to current user.
$sid = optional_param('sid', '0', PARAM_INT);
$format = optional_param('format', '', PARAM_TEXT); // Export format.
$debug  = optional_param('debug', 0, PARAM_INT);
// Set user.
if (!$user = $DB->get_record('user', array('id' => $userid))) {
    print_error('error:usernotfound', 'totara_plan');
}

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('report');
$PAGE->set_url('/totara/plan/record/evidence/index.php', array('userid' => $userid, 'format' => $format));

$menunavitem = '';
$url = null;
if ($USER->id == $userid) {
    $strheading = get_string('recordoflearning', 'totara_core');
    $usertype = 'learner';
    $menuitem = '\totara_plan\totara\menu\recordoflearning';
} else {
    $strheading = get_string('recordoflearningforname', 'totara_core', fullname($user, true));
    $usertype = 'manager';
    $menuitem = '\totara_core\totara\menu\myteam';
    if (advanced_feature::is_enabled('myteam')) {
        $menunavitem = 'team';
        $url = new moodle_url('/my/teammembers.php');
    }
}

$shortname = 'evidence_record_of_learning';
$reportrecord = $DB->get_record('report_builder', ['shortname' => $shortname]);
$globalrestrictionset = rb_global_restriction_set::create_from_page_parameters($reportrecord);
$config = (new rb_config())
    ->set_sid($sid)
    ->set_embeddata(['user_id' => $userid])
    ->set_reportfor($userid)
    ->set_global_restriction_set($globalrestrictionset);
$report = reportbuilder::create_embedded($shortname, $config);

$logurl = $PAGE->url->out_as_local_url();
if ($format != '') {
    $report->export_data($format);
    die;
}

report_viewed::create_from_report($report)->trigger();

$report->include_js();

// Display the page.
$strsubheading = get_string('allevidence', 'totara_plan');
if ($url) {
    $PAGE->navbar->add(get_string($menunavitem, 'totara_core'), $url);
}
$PAGE->navbar->add($strheading, new moodle_url('/totara/plan/record/index.php', array('userid' => $userid)));
$PAGE->navbar->add($strsubheading);
$PAGE->set_title($strheading);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_button($report->edit_button());
$PAGE->set_totara_menu_selected($menuitem);
dp_display_plans_menu($userid, 0, $usertype, 'evidence/index', 'none', false);

/** @var totara_reportbuilder_renderer $renderer */
$renderer = $PAGE->get_renderer('totara_reportbuilder');

echo $OUTPUT->header();

// This must be done after the header and before any other use of the report.
list($reporthtml, $debughtml) = $renderer->report_html($report, $debug);
echo $debughtml;

echo $OUTPUT->container_start('', 'dp-plan-content');

echo $OUTPUT->page_main_heading($strheading.': '.$strsubheading);

dp_print_rol_tabs(null, 'evidence', $userid);

$report->display_restrictions();


echo $renderer->print_description($report->description, $report->_id);

// Print saved search options and filters.
$report->display_saved_search_options();
$report->display_search();
$report->display_sidebar_search();

echo $renderer->result_count_heading($report, $renderer->showhide_button($report));

echo $reporthtml;

// Export button.
$renderer->export_select($report, $sid);

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
