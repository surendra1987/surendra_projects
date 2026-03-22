<?php

/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author  Rami Habib <rami.habib@totara.com>
 * @package totara_program
 */

use core\entity\user;
use core_phpunit\testcase;
use core\testing\generator as core_generator;
use totara_core\advanced_feature;
use totara_program\assignment\group;
use totara_program\testing\generator;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group totara_program
 */
class totara_program_webapi_resolver_mutation_group_self_unenrol_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'totara_program_group_self_unenrol';

    private function td_successful_ajax_call(): array
    {
        return [
            'self unenrol via group id' =>
                function (): array {
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        ['id' => $group->get_instanceid()]
                    ];
                },
            'self unenrol via assignment id' =>
                function (): array {
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        ['assignment_id' => $group->get_id()]
                    ];
                },
            'self unenrol via corresponding id and assignment id' =>
                function (): array {
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        [
                            'id' => $group->get_instanceid(),
                            'assignment_id' => $group->get_id()
                        ]
                    ];
                },
            'self unenrol via differing id and assignment id' =>
                function (): array {
                    $group = self::create_test_group();
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        [
                            'id' => $group->get_instanceid(),
                            'assignment_id' => '111'
                        ]
                    ];
                }

        ];
    }


    public function test_successful_ajax_call(): void {
        foreach ($this->td_successful_ajax_call() as $generate_data) {
            [$user, $group, $ref_args] = $generate_data();
            $args = ['input' => $ref_args];

            self::assertEquals(1, $group->get_user_count());

            self::setUser($user);
            $result = $this->parsed_graphql_operation(self::MUTATION, $args);
            $this->assert_webapi_operation_successful($result);

            self::assert_pass($this->get_webapi_operation_data($result));

            self::assertNotEqualsCanonicalizing(
                [$user->id],
                $group->get_users()->pluck('id'),
                'user unrolled'
            );

            self::assertEquals(0, $group->get_user_count());
        }
    }

    public function test_unenrolment_disallowed(): void {
        // This test just exercises one unenrolment condition only to verify an error message is returned.
        // It is assumed that comprehensive testing of unenrolment conditions is done elsewhere (e.g. in the group_interactor class tests).
        $user = self::create_test_user();
        $group = self::create_test_group(false);
        self::add_user_to_group($user->id, $group->get_instanceid());

        $args = ['input' => ['id' => $group->get_instanceid()]];

        self::assertEquals(1, $group->get_user_count());

        self::setUser($user);
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        self::assert_error(
            $this->get_webapi_operation_data($result),
            'group:error:enrol:group_disallows_unenrol'
        );

        self::assertEquals(1, $group->get_user_count());
    }

    public function test_failed_middleware(): void {
        $args = ['input' => ['id' => $this->create_test_group()->get_instanceid()]];

        $try = function (string $err) use ($args): void {
            try {
                $this->resolve_graphql_mutation(self::MUTATION, $args);
                self::fail('mutation should have thrown an exception');
            } catch (moodle_exception $e) {
                self::assertStringContainsString($err, $e->getMessage());
            }
        };

        $feature = 'programs';
        advanced_feature::disable($feature);
        $try('Feature programs is not available');
        advanced_feature::enable($feature);

        self::setUser();
        $try('You are not logged in');

        self::setGuestUser();
        $try('Must be an authenticated user');
    }

    /**
     * Checks if an operation return result passed.
     *
     * @param array<string,mixed> $result the operation result.
     * @param string $error expected error code.
     */
    private static function assert_pass(array $result): void {
        self::assertTrue($result['success'], 'failed when it should pass');
        self::assertNull($result['message'], 'non null message');
        self::assertNull($result['code'], 'non null code');
    }

    /**
     * Checks if an operation return result failed.
     *
     * @param array<string,mixed> $result the operation result.
     * @param string $error expected error code.
     */
    private static function assert_error(
        array $result,
        string $error
    ): void {
        self::assertFalse($result['success'], 'passed when it should fail');
        self::assertNotNull($result['message'], 'null message');
        self::assertEquals($error, $result['code'], 'wrong error code');
    }

    /**
     * Creates a test user.
     *
     * @return user the created user.
     */
    private static function create_test_user(): user {
        self::setAdminUser();
        return new user(core_generator::instance()->create_user());
    }

    /**
     * Creates a test group.
     *
     * @return group the test group.
     */
    private static function create_test_group(
        bool $can_self_unenrol = true
    ): group {
        self::setAdminUser();
        $generator = generator::instance();

        return $generator->create_group_assignment(
            program: $generator->create_program(),
            can_self_unenrol: $can_self_unenrol
        );
    }

    /**
     * Add a user to a group in a way that doesn't
     * rely on the group's 'add_users' function
     *
     * @param integer $user_id the id of the user
     * @param integer $group_id the id of the group
     * @return void
     */
    private static function add_user_to_group(
        int $user_id,
        int $group_id
    ): void {
        self::setAdminUser();
        $generator = generator::instance();

        $generator->create_group_user(
            $user_id,
            $group_id
        );
    }
}
