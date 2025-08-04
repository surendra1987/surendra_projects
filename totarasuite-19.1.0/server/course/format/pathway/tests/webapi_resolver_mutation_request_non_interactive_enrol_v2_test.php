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
use core\entity\user_enrolment;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group format_pathway
 */
class format_pathway_webapi_resolver_mutation_request_non_interactive_enrol_v2_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'format_pathway_request_non_interactive_enrol_v2';

    private $course;

    public function setUp(): void {
        parent::setUp();

        $gen = self::getDataGenerator();
        $this->course = $gen->create_course(['format' => 'pathway']);

        $this->enable_non_interactive_enrol($this->course->id);

    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->course = null;
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_by_admin(): void {
        self::setAdminUser();

        $interactor = course_interactor::from_course_id($this->course->id);
        self::assertTrue($interactor->can_enrol());
        self::assertEquals(0, user_enrolment::repository()->count());

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );

        $result = $result['success'];
        self::assertTrue($result);
        self::assertFalse($interactor->can_enrol());
        self::assertEquals(1, user_enrolment::repository()->count());
        self::assertTrue(user_enrolment::repository()->where('userid', get_admin()->id)->exists());
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_by_admin_when_self_enrol_disabled(): void {
        self::setAdminUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['format' => 'pathway']);

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Not support non interactive enrol');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $course->id]
        );
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_by_course_guest_when_guest_access_enabled(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        $interactor = course_interactor::from_course_id($this->course->id);
        self::assertTrue($interactor->can_enrol());
        self::assertEquals(0, user_enrolment::repository()->count());

        $result = $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );

        $result = $result['success'];
        self::assertTrue($result);
        self::assertFalse($interactor->can_enrol());
        self::assertEquals(1, user_enrolment::repository()->count());
        self::assertTrue(user_enrolment::repository()->where('userid', $user->id)->exists());
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_by_course_guest_when_guest_access_disabled(): void {
        $gen = self::getDataGenerator();
        $course = $gen->create_course(['format' => 'pathway']);
        $user = $gen->create_user();
        self::setUser($user);

        self::expectException(require_login_exception::class);
        self::expectExceptionMessage('Course or activity not accessible. (Not enrolled)');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $course->id]
        );

    }

    /**
     * @return void
     */
    public function test_request_self_enrol_by_site_guest(): void {
        self::setGuestUser();
        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Site guest can not request self enrol');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_by_site_guest_when_guest_access_disabled(): void {
        self::setGuestUser();
        $gen = self::getDataGenerator();
        $course = $gen->create_course();

        self::expectException(require_login_exception::class);
        self::expectExceptionMessage('Course or activity not accessible. (Not enrolled)');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $course->id]
        );
    }


    /**
     * @return void
     */
    public function test_request_self_enrol_with_password_setting(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        $this->update_self_enrol_setting('password', 'aaaa');

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Not support non interactive enrol');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_with_new_enrol_setting(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        $this->update_self_enrol_setting('customint6', 0);

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Not support non interactive enrol');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_with_enrol_date_duration(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        $this->update_self_enrol_setting('enrolstartdate', time() - 10);
        $this->update_self_enrol_setting('enrolenddate', time() - 5);

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Not support non interactive enrol');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );
    }

    /**
     * @return void
     */
    public function test_request_self_enrol_with_audience_enabled(): void {
        $gen = self::getDataGenerator();
        $user = $gen->create_user();
        self::setUser($user);

        $this->update_self_enrol_setting('customint5', 1);

        self::expectException(coding_exception::class);
        self::expectExceptionMessage('Not support non interactive enrol');
        $this->resolve_graphql_mutation(
            self::MUTATION,
            ['course_id' => $this->course->id]
        );
    }

    /**
     * @param string $key
     * @param string $value
     *
     */
    private function update_self_enrol_setting(string $key, string $value): void {
        global $DB;

        $instance = $DB->get_record(
            'enrol',
            [
                'courseid' => $this->course->id,
                'enrol' => 'self'
            ],
            '*',
            MUST_EXIST
        );

        $instance->{$key} = $value;
        $DB->update_record('enrol', $instance);
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