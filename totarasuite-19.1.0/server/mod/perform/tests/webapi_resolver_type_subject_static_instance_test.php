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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_job\job_assignment;
use mod_perform\testing\generator;
use mod_perform\entity\activity\subject_static_instance;
use mod_perform\models\activity\subject_instance;

/**
 * @group perform
 */
class mod_perform_webapi_resolver_type_subject_static_instance_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'mod_perform_subject_static_instance';

    public function test_invalid_input(): void {
        static::setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessageMatches("/subject_static_instance model/");

        $this->resolve_graphql_type(self::TYPE, 'subject_static_instance', new \stdClass());
    }

    public function test_invalid_field(): void {
        static::setAdminUser();
        $subject_instance = $this->create_data();

        $field = 'unknown';

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessageMatches("/$field/");

        $this->resolve_graphql_type(self::TYPE, $field, $subject_instance->get_subject_static_instances()->first());
    }

    public function test_resolve_job_assignment(): void {
        static::setAdminUser();
        $subject_instance = $this->create_data();

        $ja = $this->resolve_graphql_type(self::TYPE, 'job_assignment', $subject_instance->get_subject_static_instances()->first());
        static::assertInstanceOf(job_assignment::class, $ja);
        static::assertNotEmpty($ja->managerjaid);
        static::assertNotEmpty($ja->fullname);
    }

    public function test_resolve_former_manager(): void {
        static::setAdminUser();
        $subject_instance = $this->create_data();

        $value = $this->resolve_graphql_type(self::TYPE, 'former_manager', $subject_instance->get_subject_static_instances()->first());
        static::assertFalse($value);
    }

    private function create_data(): subject_instance {
        $generator = generator::instance();

        $subject_user = static::getDataGenerator()->create_user();
        $manager_user = static::getDataGenerator()->create_user();

        $subject_instance = $generator->create_subject_instance([
            'subject_is_participating' => true,
            'subject_user_id' => $subject_user->id,
            'other_participant_id' => null,
            'include_questions' => false,
        ]);

        $manager_ja = job_assignment::create([
            'userid' => $manager_user->id,
            'fullname' => 'manager_user_ja',
            'shortname' => 'manager_user_ja',
            'idnumber' => 'manager_user_ja',
            'managerjaid' => null,
        ]);

        $main_user_ja = job_assignment::create([
            'userid' => $manager_user->id,
            'fullname' => 'main_user_ja',
            'shortname' => 'main_user_ja',
            'idnumber' => 'main_user_ja',
            'managerjaid' => $manager_ja->id,
        ]);

        $static_instance = new subject_static_instance();
        $static_instance->subject_instance_id = $subject_instance->id;
        $static_instance->job_assignment_id = $main_user_ja->id;
        $static_instance->manager_job_assignment_id = $manager_ja->id;
        $static_instance->save();

        return subject_instance::load_by_id($static_instance->subject_instance_id);
    }
}
