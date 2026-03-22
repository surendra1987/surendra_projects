<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

use mod_perform\controllers\activity\view_user_activity;
use mod_perform\models\activity\participant_instance as participant_instance_model;

/**
 * @group perform
 */
class mod_perform_view_user_activity_controller_test extends \core_phpunit\testcase {

    public function test_for_access_removed(): void {
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
        (new view_user_activity())->process();
        ob_get_clean();

        $pi = participant_instance_model::load_by_id($subjects_participant_instance->id);
        $pi->manually_close();
        $pi->set_access_removed(true);

        // Now there should be an exception because the participant's access is removed.
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('You no longer have access to this activity.');
        (new view_user_activity())->process();
    }
}
