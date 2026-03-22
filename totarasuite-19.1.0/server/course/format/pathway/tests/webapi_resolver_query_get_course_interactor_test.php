<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package format_pathway
 */

use container_course\interactor\course_interactor;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group format_pathway
 */
class format_pathway_webapi_resolver_query_get_course_interactor_test extends testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'format_pathway_get_course_interactor';

    /**
     * @return void
     */
    public function test_get_course_interactor(): void {
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['format' => 'pathway']);

        self::setAdminUser();

        /** @var course_interactor $result **/;
        $result = $this->resolve_graphql_query(
            self::QUERY,
            ['course_id' => $course->id]
        );

        self::assertTrue($result->can_enrol());
        self::assertTrue($result->can_view());
        self::assertFalse($result->supports_non_interactive_enrol());
        self::assertFalse($result->non_interactive_enrol_instance_enabled());

        $this->enable_non_interactive_enrol($course->id);

        /** @var course_interactor $result **/;
        $result = $this->resolve_graphql_query(
            self::QUERY,
            ['course_id' => $course->id]
        );

        self::assertTrue($result->supports_non_interactive_enrol());
        self::assertTrue($result->non_interactive_enrol_instance_enabled());
    }

    /**
     * @return void
     */
    public function test_get_course_interactor_with_admin(): void {
        $gen = self::getDataGenerator();

        $course = $gen->create_course(['format' => 'pathway']);

        self::setAdminUser();
        $result = $this->resolve_graphql_query(
            self::QUERY,
            ['course_id' => $course->id]
        );

        self::assertNotEmpty($result);
    }

    /**
     * @return void
     */
    public function test_get_course_interactor_without_logged_user(): void {
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['format' => 'pathway']);

        self::expectException(require_login_exception::class);
        $this->resolve_graphql_query(
            self::QUERY,
            ['course_id' => $course->id]
        );
    }

    /**
     * @param int $course_id
     * @return void
     */
    private function enable_non_interactive_enrol(int $course_id): void {
        global $DB;

        // Enabled self enrolment.
        $self_plugin = enrol_get_plugin('self');
        $instance = $DB->get_record(
            'enrol',
            [
                'courseid' => $course_id,
                'enrol' => 'self'
            ],
            '*',
            MUST_EXIST
        );

        $self_plugin->update_status($instance, ENROL_INSTANCE_ENABLED);

        // Enabled guest access.
        $enrol_instance = $DB->get_record(
            'enrol',
            [
                'enrol' => 'guest',
                'courseid' => $course_id
            ],
            '*',
            MUST_EXIST
        );

        $guest_plugin = enrol_get_plugin('guest');
        $guest_plugin->update_status($enrol_instance, ENROL_INSTANCE_ENABLED);
    }
}