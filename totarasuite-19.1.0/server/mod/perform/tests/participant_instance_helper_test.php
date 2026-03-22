<?php
/**
 * This file is part of Totara Core
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core\entity\user;
use core_phpunit\testcase;
use mod_perform\entity\activity\participant_instance;
use mod_perform\models\activity\helpers\participant_instance_helper;
use mod_perform\testing\generator as perform_generator;

/**
 * Class mod_perform_participant_instance_helper_test
 *
 * @group perform
 */
class mod_perform_participant_instance_helper_test extends testcase {

    public function test_action_required_for_with_access_removed(): void {
        $this->setAdminUser();

        $subject_user = self::getDataGenerator()->create_user();
        $other_participant_user = self::getDataGenerator()->create_user();
        $perform_generator = perform_generator::instance();
        $activity = $perform_generator->create_activity_in_container();
        $subject_instance = $perform_generator->create_subject_instance([
            'activity_id' => $activity->id,
            'subject_username' => $subject_user->username,
            'other_participant_id' => $other_participant_user->id,
            'subject_is_participating' => true,
        ]);

        // Check with access still granted.
        $other_participant_instance = $subject_instance->participant_instances->filter('participant_id', $other_participant_user->id);
        static::assertCount(1, $other_participant_instance);
        $participant_instances_action_required = participant_instance_helper::action_required_for(new user($other_participant_user));
        static::assertCount(1, $participant_instances_action_required);
        static::assertEquals($other_participant_instance->first()->id, $participant_instances_action_required->first()->id);

        // Check with access removed.
        participant_instance::repository()
            ->where('id', $other_participant_instance->first()->id)
            ->update(['access_removed' => '1']);
        $participant_instances_action_required = participant_instance_helper::action_required_for(new user($other_participant_user));
        static::assertCount(0, $participant_instances_action_required);
    }
}
