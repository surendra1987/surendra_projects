<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package approvalform_enrol
 */

use approvalform_enrol\observer\application_completed_observer;
use approvalform_enrol\observer\user_enrolment_application_created_observer;
use approvalform_enrol\observer\user_enrolment_application_deleted_observer;
use core_enrol\event\user_enrolment_application_created;
use core_enrol\observer\approval_workflow;
use mod_approval\event\application_completed;
use mod_approval\event\application_deleted;
use mod_approval\event\workflow_deleted;

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname' => application_completed::class,
        'callback' => [application_completed_observer::class, 'trigger_enrolment_approved'],
    ],
    [
        'eventname' => user_enrolment_application_created::class,
        'callback' => [user_enrolment_application_created_observer::class, 'update_draft_application_course_fields'],
    ],
    [
        'eventname' => workflow_deleted::class,
        'callback' => [approval_workflow::class, 'approval_workflow_deleted'],
    ],
    [
        'eventname' => application_deleted::class,
        'callback' => [user_enrolment_application_deleted_observer::class, 'delete_enrolment'],
    ],
];