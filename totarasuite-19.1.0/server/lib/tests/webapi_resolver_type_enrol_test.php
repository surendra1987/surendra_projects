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
 * @package enrol_manual
 */


use core\model\enrol;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * core_webapi_resolver_type_enrol_test
 */
class core_webapi_resolver_type_enrol_test extends testcase {

    use webapi_phpunit_helper;

    /**
     * @return void
     */
    public function test_enrol_with_capability(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        $role_id = $this->getDataGenerator()->create_role();
        $system_context = context_system::instance();
        assign_capability('moodle/course:enrolreview', CAP_ALLOW, $role_id, $system_context);
        role_assign($role_id, $user->id, $system_context);
        self::setUser($user);

        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'id',
            $model,
        );

        self::assertEquals($model->id, $result);
    }

    /**
     * @return void
     */
    public function test_enrol_without_capability(): void {
        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'id',
            $model,
        );

        self::assertNull($result);
    }

    /**
     * @return void
     */
    public function test_enrol_id(): void {
        self::setAdminUser();
        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'id',
            $model,
        );

        self::assertEquals($model->id, $result);
    }

    /**
     * @return void
     */
    public function test_enrol_enrol(): void {
        self::setAdminUser();
        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'enrol',
            $model,
            ['format' => 'PLAIN'],
        );

        self::assertEquals($model->enrol, $result);
    }

    /**
     * @return void
     */
    public function test_enrol_course(): void {
        self::setAdminUser();
        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'course',
            $model,
        );

        self::assertEquals($model->course->id, $result->id);
    }

    /**
     * @return void
     */
    public function test_enrol_status(): void {
        self::setAdminUser();
        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'status',
            $model,
        );

        self::assertEquals('ENROL_INSTANCE_ENABLED', $result);
    }

    /**
     * @return void
     */
    public function test_enrol_sortorder(): void {
        self::setAdminUser();
        $model = $this->create_enrol();
        $result = $this->resolve_graphql_type(
            'core_enrol',
            'sortorder',
            $model,
        );

        self::assertEquals($model->sortorder, $result);
    }

    /**
     * @return enrol
     */
    protected function create_enrol(): enrol {
        $course = $this->getDataGenerator()->create_course();
        $enrol_instances = enrol_get_instances($course->id, true);
        $enrol_instance = reset($enrol_instances);
        return enrol::load_by_id($enrol_instance->id);
    }
}