<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_program
 */

require_once __DIR__ . '/../../../config.php';
require_once $CFG->libdir . '/adminlib.php';
require_once 'edit_group_form.php';
require_once $CFG->dirroot . '/totara/core/js/lib/setup.php';

use core\orm\query\builder;
use totara_program\assignment\external;
use totara_program\assignment\group;

/** @var core_config $CFG */
/** @var object $OUTPUT */
/** @var moodle_page $PAGE */

require_login();

// Get program id and check capabilities.
$program_id = required_param('program_id', PARAM_INT);
$program_context = context_program::instance($program_id);
require_capability('totara/program:configureassignments', $program_context);

// Check if we're editing an existing group.
$assignment_id = optional_param('assignment_id', null, PARAM_INT);

// Set up the page.
$PAGE->set_context($program_context);
$PAGE->set_url('/totara/program/assignment/edit_group.php', [
    'program_id' => $program_id,
    'assignment_id' => $assignment_id,
]);

// Load the form.
$form = new edit_group_form(qualified_me(), [
    'program_id' => $program_id,
    'assignment_id' => $assignment_id,
]);

if ($assignment_id) {
    $group =  \core\orm\query\builder::table(\totara_program\entity\prog_group::TABLE, 'g')
        ->select('g.*')
        ->join([\totara_program\entity\program_assignment::TABLE, 'a'], 'g.id', '=', 'a.assignmenttypeid')
        ->where('a.assignmenttype', '=', group::ASSIGNTYPE_GROUP)
        ->where('a.id', '=', $assignment_id)
        ->map_to(\totara_program\entity\prog_group::class)
        ->one(true);
} else {
    $group = new \totara_program\entity\prog_group();
}

// Check for form submission.
if ($data = $form->get_data()) {
    $assignment = builder::get_db()->transaction(function () use ($data, $group, $program_id): array {
        $group->name = $data->name;
        $group->can_self_enrol = $data->can_self_enrol ?? 0;
        $group->can_self_unenrol = $data->can_self_unenrol ?? 0;
        $group->save();

        $group->description = $data->description;
        $group->save();

        return external::add_assignments($program_id, group::ASSIGNTYPE_GROUP, [$group->id]);
    });

    if ($assignment_id) {
        $assignment['items'][0]['id'] = $assignment_id;
    }

    echo json_encode($assignment);
    return;
}

if ($assignment_id && !$form->is_submitted()) {
    $group = (object) $group->to_array();

    // Reload the form to add the assignment id.
    $form = new edit_group_form(qualified_me(), [
        'program_id' => $program_id,
        'assignment_id' => $assignment_id,
    ]);

    $form->set_data($group);
}

$form->display();
