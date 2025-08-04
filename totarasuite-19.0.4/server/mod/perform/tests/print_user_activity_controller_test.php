<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totaralearning.com>
 * @package mod_perform
 */

use mod_perform\controllers\activity\print_user_activity;
use mod_perform\entity\activity\participant_instance;
use mod_perform\models\activity\participant_instance as participant_instance_model;

/**
 * @group perform
 */
class mod_perform_print_user_activity_controller_test extends \core_phpunit\testcase {

    public function test_user_must_be_participant_to_access_page(): void {
        /** @var \mod_perform\testing\generator $generator */
        $generator = \mod_perform\testing\generator::instance();

        $subject_user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();

        self::setAdminUser();
        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject_user->id,
            'other_participant_id' => $other_user->id,
            'include_questions' => true,
            'anonymous_responses' => true,
        ]);

        /** @var participant_instance $subjects_participant_instance */
        $subjects_participant_instance = $subject_instance->participant_instances->find('participant_id', $subject_user->id);
        $subjects_participant_section_id = $subjects_participant_instance->participant_sections->first()->id;

        /** @var participant_instance $other_user_participant_instance */
        $other_user_participant_instance = $subject_instance->participant_instances->find('participant_id', $other_user->id);
        $other_user_participant_section_id = $other_user_participant_instance->participant_sections->first()->id;

        // No problems for the subject-participant to print their own activity.
        self::setUser($subject_user);
        $_POST['participant_section_id'] = $subjects_participant_section_id;

        ob_start();
        (new print_user_activity())->process();
        ob_get_clean();

        // No problems for the other participant to print their own activity.
        self::setUser($other_user);
        $_POST['participant_section_id'] = $other_user_participant_section_id;

        ob_start();
        (new print_user_activity())->process();
        ob_get_clean();

        // Problems for the subject-participant to print the other participant's activity.
        self::setUser($subject_user);
        $_POST['participant_section_id'] = $other_user_participant_section_id;

        try {
            (new print_user_activity())->process();
            $this->fail('Should have thrown a moodle exception');
        } catch (moodle_exception $e) {
            self::assertEquals('Invalid activity', $e->getMessage());
        }

        // Problems for the other participant to print the subject's activity.
        self::setUser($other_user);
        $_POST['participant_section_id'] = $subjects_participant_section_id;

        try {
            (new print_user_activity())->process();
            $this->fail('Should have thrown a moodle exception');
        } catch (moodle_exception $e) {
            self::assertEquals('Invalid activity', $e->getMessage());
        }
    }

    public function test_subject_must_not_be_deleted_to_access_page(): void {
        /** @var \mod_perform\testing\generator $generator */
        $generator = \mod_perform\testing\generator::instance();

        $subject_user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();

        self::setAdminUser();
        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject_user->id,
            'other_participant_id' => $other_user->id,
            'include_questions' => true,
            'anonymous_responses' => true,
        ]);

        /** @var participant_instance $other_user_participant_instance */
        $other_user_participant_instance = $subject_instance->participant_instances->find('participant_id', $other_user->id);
        $other_user_participant_section_id = $other_user_participant_instance->participant_sections->first()->id;

        self::setUser($other_user);
        $_POST['participant_section_id'] = $other_user_participant_section_id;

        // No problem while the subject is not deleted.
        ob_start();
        (new print_user_activity())->process();
        ob_get_clean();

        // We should get an exception now that the subject is deleted.
        delete_user($subject_user);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Invalid activity');
        (new print_user_activity())->process();
    }

    public function test_with_participant_access_removed(): void {
        $generator = \mod_perform\testing\generator::instance();
        $subject_user = self::getDataGenerator()->create_user();
        $other_user = self::getDataGenerator()->create_user();
        self::setAdminUser();
        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject_user->id,
            'other_participant_id' => $other_user->id,
            'include_questions' => true,
            'anonymous_responses' => true,
        ]);
        $subjects_participant_instance = $subject_instance->participant_instances->find('participant_id', $subject_user->id);
        $subjects_participant_section_id = $subjects_participant_instance->participant_sections->first()->id;

        // No problems for the subject-participant to print their own activity.
        self::setUser($subject_user);
        $_POST['participant_section_id'] = $subjects_participant_section_id;

        // There should be no problem.
        ob_start();
        (new print_user_activity())->process();
        ob_get_clean();

        $pi = participant_instance_model::load_by_id($subjects_participant_instance->id);
        $pi->manually_close();
        $pi->set_access_removed(true);

        // Now there should be an exception because the participant's access is removed.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('You no longer have access to this activity.');
        (new print_user_activity())->process();
    }
}
