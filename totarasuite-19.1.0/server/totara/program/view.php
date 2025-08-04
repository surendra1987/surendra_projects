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
 * @author Ben Lobo <ben.lobo@kineo.com>
 * @package totara
 * @subpackage program
 */

/**
 * Program progress view page
 */

use core\format;
use core\webapi\formatter\field\string_field_formatter;
use totara_program\event\program_viewed;
use totara_program\program;

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

require_login();

$id = required_param('id', PARAM_INT); // program id

if (!$program = new program($id)) {
    print_error('error:programid', 'totara_program');
}

if (!$program->is_viewable()) {
    print_error('error:inaccessible', 'totara_program');
}

// Check if programs or certifications are enabled.
if ($program->certifid) {
    check_certification_enabled();
    $identifier = 'editcertif';
    $component = 'totara_certification';
    $viewtype = 'certification';
} else {
    check_program_enabled();
    $identifier = 'editprogramdetails';
    $component = 'totara_program';
    $viewtype = 'program';
}

$PAGE->set_program($program);
$PAGE->set_url('/totara/program/view.php', array('id' => $id, 'viewtype' => $viewtype));
$PAGE->set_pagelayout('noblocks');

// Trigger event.
$data = array('id' => $program->id, 'other' => array('section' => 'general'));
program_viewed::create_from_data($data)->trigger();

///
/// Display
///

$isadmin = has_capability('moodle/category:manage', context_coursecat::instance($program->category));

$category_breadcrumbs = prog_get_category_breadcrumbs($program->category, $viewtype);
$string_field_formatter = new string_field_formatter(format::FORMAT_PLAIN, $program->get_context());
$heading = $string_field_formatter->format($program->fullname);
$pagetitle = get_string('program', 'totara_program') . ': ' . $heading;
if ($isadmin) {
    $str = ($viewtype == 'program') ? get_string('manageprograms', 'admin') : get_string('managecertifications', 'totara_core');
    $PAGE->navbar->add($str, new moodle_url('/totara/program/manage.php', array('viewtype' => $viewtype)));
} else {
    $str = ($viewtype == 'program') ? get_string('findprograms', 'totara_program') : get_string('findcertifications', 'totara_certification');
    $PAGE->navbar->add($str, new moodle_url('/totara/program/index.php', array('viewtype' => $viewtype)));
}

foreach ($category_breadcrumbs as $crumb) {
    $PAGE->navbar->add($crumb['name'], $crumb['link']);
}

$PAGE->navbar->add($heading);

$PAGE->set_title($pagetitle);
$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();

if ($program->has_capability_for_overview_page()) {
    echo $OUTPUT->single_button(new moodle_url('/totara/program/edit.php', array('id' => $program->id)),
        get_string($identifier, $component), 'GET', array('class' => 'navbutton'));
}

// Program page content.
echo $OUTPUT->container_start('', 'view-program-content');

// A user assigned this program should always see their progress.
if (!empty($CFG->audiencevisibility)) {
    if ($program->user_is_assigned($USER->id)) {
        echo $program->display($USER->id);
    } else if ($program->is_viewable()) {
        echo $program->display();
    } else {
        echo $OUTPUT->notification(get_string('error:inaccessible', 'totara_program'));
    }
} else {
    if ($program->user_is_assigned($USER->id)) {
        echo $program->display($USER->id);
    } else {
        echo $program->display();
    }
}

echo $OUTPUT->container_end();

echo $OUTPUT->footer();
