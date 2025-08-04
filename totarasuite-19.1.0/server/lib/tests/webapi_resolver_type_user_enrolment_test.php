<?php
/**
 * This file is part of Totara TXP
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package core
 */


use core\model\user_enrolment;
use core\orm\query\builder;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/*
 * core_webapi_resolver_type_user_enrolment_test
 */
class core_webapi_resolver_type_user_enrolment_test extends testcase {

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_user_enrolment_with_capability(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $role_id = $this->getDataGenerator()->create_role();
        $system_context = context_system::instance();
        assign_capability('moodle/course:enrolreview', CAP_ALLOW, $role_id, $system_context);
        role_assign($role_id, $user->id, $system_context);
        self::setUser($user);

        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'id',
            $model,
        );

        self::assertEquals($model->id, $result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_without_capability(): void {
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'id',
            $model,
        );

        self::assertNull($result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_id(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'id',
            $model,
        );

        self::assertEquals($model->id, $result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_timestart(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'timestart',
            $model,
            ['format' => 'TIMESTAMP'],
        );

        self::assertEquals($model->timestart, $result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_timeend(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'timeend',
            $model,
            ['format' => 'ISO8601'],
        );
        $date = new DateTime('@' . $model->timeend);
        $date->setTimezone(core_date::get_user_timezone_object());

        self::assertEquals($date->format(DateTime::ISO8601), $result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_timecreated(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'timecreated',
            $model,
            ['format' => 'TIMESTAMP'],
        );

        self::assertEquals($model->timecreated, $result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_timemodified(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'timemodified',
            $model,
            ['format' => 'TIMESTAMP'],
        );

        self::assertEquals($model->timemodified, $result);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_modifiedby(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'modified_by',
            $model,
        );

        self::assertEquals($model->modified_by->id, $result->id);
    }

    /**
     * @return void
     */
    public function test_user_enrolment_status(): void {
        self::setAdminUser();
        $model = $this->create_user_enrolment();
        $result = $this->resolve_graphql_type(
            'core_user_enrolment',
            'status',
            $model,
        );

        self::assertEquals('ENROL_USER_ACTIVE', $result);
    }

    /**
     * @return user_enrolment
     */
    protected function create_user_enrolment(): user_enrolment {
        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();

        $time = time();
        $this->getDataGenerator()->enrol_user(
            $user->id,
            $course->id,
            'student',
            'manual',
            $time - 86400,
            $time + 186400
        );
        $record = builder::get_db()->get_record('user_enrolments', ['userid' => $user->id]);
        return user_enrolment::load_by_id($record->id);
    }
}