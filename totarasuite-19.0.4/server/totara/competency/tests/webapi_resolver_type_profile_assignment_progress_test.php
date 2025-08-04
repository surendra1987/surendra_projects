<?php
/**
 * This file is part of Totara Perform
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package totara_competency
 */

use core\format;
use core_phpunit\testcase;
use totara_competency\user_groups;
use totara_competency\models\assignment;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_competency\testing\assignment_generator;

class totara_competency_webapi_resolver_type_profile_assignment_progress_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY_TYPE = 'totara_competency_profile_assignment_progress';

    public function test_resolve_invalid_object(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Accepting only assignment models.');

        $this->resolve_graphql_type(self::QUERY_TYPE, 'id', new stdClass());
    }

    public function test_reason_assigned_format_successful(): void {
        $assignment = $this->create_data();

        $this->assertEquals(
            'Research & Development (Audience)',
            $this->resolve_graphql_type(self::QUERY_TYPE, 'reason_assigned', $assignment, ['format' => format::FORMAT_PLAIN])
        );
    }

    private function create_data(): assignment {
        $generator = static::getDataGenerator()->get_plugin_generator('totara_competency');
        /** @var assignment_generator $assignment_generator */
        $assignment_generator = static::getDataGenerator()->get_plugin_generator('totara_competency')->assignment_generator();
        $competency = $generator->create_competency();
        $user = static::getDataGenerator()->create_user();
        static::setUser($user);

        $cohort_id = $assignment_generator->create_cohort(['name' => 'Research & Development'])->id;

        $attributes['competency_id'] = $competency->id;
        $attributes['user_group_id'] = $cohort_id;
        $attributes['user_group_type'] = user_groups::COHORT;
        $user_assignment = $assignment_generator->create_assignment($attributes);

        return assignment::load_by_id($user_assignment->id);
    }
}
