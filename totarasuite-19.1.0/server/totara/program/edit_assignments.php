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
 * @author Jake Salmon <jake.salmon@kineo.com>
 * @package totara
 * @subpackage program
 */

/**
 * Program view page
 */

use totara_program\assignment\helper as assignment_helper;
use totara_program\event\program_viewed;
use totara_program\output\assignments;
use totara_tui\output\component;
use totara_program\program;

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot . '/totara/program/lib.php');
require_once($CFG->dirroot.'/totara/certification/lib.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

require_login();

$id = required_param('id', PARAM_INT);

$program = new program($id);
$iscertif = $program->is_certif();
$programcontext = $program->get_context();

require_capability('totara/program:configureassignments', $programcontext);
$program->check_enabled();

$PAGE->set_url(new moodle_url('/totara/program/edit_assignments.php', array('id' => $id)));
$PAGE->set_program($program);
$PAGE->set_title($program->fullname);
$PAGE->set_heading($program->fullname);

// Javascript include.
local_js(array(
TOTARA_JS_DIALOG,
TOTARA_JS_TREEVIEW,
TOTARA_JS_DATEPICKER
));

// new UI needs M.totara_core.build_datepicker
$jsmodule = array(
    'name' => 'totara_core',
    'fullpath' => '/totara/core/module.js',
    'requires' => array('json')
);
$PAGE->requires->js_init_call('M.totara_core.init', ['args' => ''], false, $jsmodule);

// Define the categorys to appear on the page
$categories = totara_program\assignments\category::get_categories(true);

// Trigger event.
$dataevent = array('id' => $program->id, 'other' => array('section' => 'assignments'));
program_viewed::create_from_data($dataevent)->trigger();

// Display.
$heading = format_string($program->fullname);

if ($iscertif) {
    $heading = get_string('header:certification', 'totara_certification', $heading);
}

$data = $program->get_current_status();
$header = new component('totara_program/components/manage_program/Header', [
    'fullname' => $heading,
    'affected' => (array)$data,
]);
$header->register($PAGE);

if (has_capability('totara/program:cloneprogram', $programcontext)) {
    $clone_button = new component('totara_program/components/clone/CloneButton', [
        'id' => $program->id,
        'fullname' => $program->fullname,
        'isCertif' => $program->is_certif()
    ]);
    $clone_button->register($PAGE);
    $PAGE->set_button($PAGE->button . $OUTPUT->render($clone_button));
}

echo $OUTPUT->header();

echo $OUTPUT->container_start('program assignments', 'program-assignments');

/** @var totara_program_renderer $renderer */
$renderer = $PAGE->get_renderer('totara_program');
echo $OUTPUT->render($header);

$exceptions = $program->get_exception_count();
$currenttab = 'assignments';
require('tabs.php');

$results = assignment_helper::get_assignments($program->id);

$program_assignments = assignments::create_from_assignments($results['assignments'], $program->id, $results['toomany']);
$program_assignments->set_categories($categories);
$assign_data = $program_assignments->get_template_data();

echo $OUTPUT->render_from_template('totara_program/assignments', $assign_data);

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
