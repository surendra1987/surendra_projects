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

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\testing\activity_generator_configuration;
use mod_perform\testing\generator as perform_generator;
use mod_perform\usagedata\participant_instances;

defined('MOODLE_INTERNAL') || die();

/**
 * @group perform
 */
class mod_perform_usagedata_participant_instances_test extends testcase {

    public function test_empty_result(): void {
        static::assertEquals([
            'count_of_participant_instances_with_access_removed' => 0,
        ], (new participant_instances())->export());
    }

    public function test_count_of_participant_instances_with_access_removed(): void {
        static::setAdminUser();

        $generator = perform_generator::instance();
        $config = activity_generator_configuration::new()
            ->set_number_of_users_per_user_group_type(1)
            ->set_number_of_sections_per_activity(1)
            ->set_relationships_per_section([constants::RELATIONSHIP_SUBJECT, constants::RELATIONSHIP_MANAGER])
            ->enable_manager_for_each_subject_user()
            ->set_number_of_activities(2);
        $generator->create_full_activities($config);

        $participant_instances = participant_instance::repository()->get();

        static::assertCount(4, $participant_instances);

        // No access removed
        $export_data = (new participant_instances())->export();
        static::assertEquals([
            'count_of_participant_instances_with_access_removed' => 0,
        ], $export_data);

        $p_entity = $participant_instances->first();
        $p_entity->access_removed = 1;
        $p_entity->save();

        static::assertEquals([
            'count_of_participant_instances_with_access_removed' => 1,
        ], (new participant_instances())->export());

        $p_entity = $participant_instances->last();
        $p_entity->access_removed = 1;
        $p_entity->save();

        static::assertEquals([
            'count_of_participant_instances_with_access_removed' => 2,
        ], (new participant_instances())->export());
    }
}
