<?php
/**
 * This file is part of Totara Core
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
 * @package core_enrol
 */

use core\entity\user_enrolment;
use core_enrol\event\user_enrolment_application_approved;
use core_enrol\model\user_enrolment_application;
use core_phpunit\testcase;
use mod_approval\testing\approval_workflow_test_setup;

class core_enrol_user_enrolment_application_approved_test extends testcase {

    use approval_workflow_test_setup;

    public function test_create_from_random_application() {
        $user_rec = $this->getDataGenerator()->create_user();
        $user = new \core\entity\user($user_rec->id);
        list($workflow, $framework, $assignment) = $this->create_workflow_and_assignment();
        $application = $this->create_application($workflow, $assignment, $user);
        $this->setUser($user->id);

        try {
            user_enrolment_application_approved::create_from_application_id($application->id);
            $this->fail('Expected exception');
        } catch (coding_exception $e) {
            $this->assertStringContainsString('No user_enrolment_application', $e->getMessage());
        }
    }

    public function test_create_from_enrolment_approval_application() {
        $user_rec = $this->getDataGenerator()->create_user();
        $user = new \core\entity\user($user_rec->id);
        list($workflow, $framework, $assignment) = $this->create_workflow_and_assignment();

        $course = $this->getDataGenerator()->create_course();
        $enrol_entity = \core\entity\enrol::repository()->find_enrol('self', $course->id);
        $enrol_entity->workflow_id = $workflow->id;
        $enrol_entity->save();

        $enrol_plugin = enrol_get_plugin('self');
        $enrol_plugin->enrol_user((object)$enrol_entity->to_array(), $user->id);

        $user_enrolment = user_enrolment::repository()->where('userid', '=', $user->id)->one(true);
        $user_enrolment_application = user_enrolment_application::find_with_user_enrolment_id($user_enrolment->id);
        $application = $user_enrolment_application->get_approval_application();

        $event = user_enrolment_application_approved::create_from_application_id($application->id);
        $data = $event->get_data();
        $this->assertEquals($user_enrolment_application->id, $data['objectid']);
        $this->assertEquals($application->id, $data['other']['approval_application_id']);
        $this->assertEquals($user_enrolment->id, $data['other']['user_enrolments_id']);
    }
}