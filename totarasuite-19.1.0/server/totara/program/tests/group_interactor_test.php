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
 * @author  Murali Nair <murali.nair@totara.com>
 * @package totara_program
 */

use core\entity\tenant;
use core\entity\user;
use core_phpunit\testcase;
use core\testing\generator as core_generator;
use totara_program\assignment\group;
use totara_program\interactor\group_interactor;
use totara_program\testing\generator;
use totara_tenant\testing\generator as tenant_generator;

/**
 * @group totara_program
 */
class totara_program_group_interactor_test extends testcase {
    private function td_can_self_enrol_no_multitenancy(): array {
        return [
            'not logged on' =>
                fn(): array => [null, self::create_test_group(), 'loggedinnot']
            ,

            'enrolment disallowed' =>
                fn(): array => [
                    self::create_test_user(),
                    self::create_test_group(can_self_enrol: false),
                    'group:error:enrol:group_disallows_enrol'
                ],

            'already enrolled' =>
                function(): array {
                    $user = self::create_test_user();

                    return [
                        $user,
                        self::create_test_group(users: [$user]),
                        'group:error:enrol:already_enrolled'
                    ];
                },

            'program expired' =>
                fn(): array => [
                    self::create_test_user(),
                    self::create_test_group(live: false),
                    'group:error:enrol:prog_expired'
                ],

            'enrolment conditions fulfilled' =>
                fn(): array => [
                    self::create_test_user(), self::create_test_group(), true
                ]
        ];
    }
    public function test_can_self_enrol_no_multitenancy(): void {
        foreach ($this->td_can_self_enrol_no_multitenancy() as $generate_data) {
            [$user, $group, $expected] = $generate_data();

            self::setUser($user);
            $result = group_interactor::from(group: $group, actor: $user)
                ->can_self_enrol();

            if (is_bool($expected)) {
                self::assertTrue($result->success);
                self::assertNull($result->code);
                self::assertNull($result->message);
            } else {
                self::assertFalse($result->success);
                self::assertEquals($expected, $result->code);
            }
        }
    }

    private function td_can_self_unenrol_no_multitenancy(): array {
        return [
            'not logged in' =>
                fn(): array => [null, self::create_test_group(), 'loggedinnot'],

            'unenrolment disallowed' =>
                function (): array {
                    $user = self::create_test_user();
                    $group = self::create_test_group(can_self_unenrol: false);
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        'group:error:enrol:group_disallows_unenrol'
                    ];
                },

            'not enrolled' =>
                function (): array {
                    $user = self::create_test_user();
                    $another_user = self::create_test_user();

                    return [
                        $user,
                        self::create_test_group(users: [$another_user]),
                        'group:error:enrol:not_enrolled'
                    ];
                },

            'program expired' =>
                function (): array {
                    $user = self::create_test_user();

                    return [
                        $user,
                        self::create_test_group(live: false, users: [$user]),
                        'group:error:enrol:prog_expired'
                    ];
                },

            'unenrolment conditions fulfilled' =>
                function (): array {
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [$user, $group, true];
                }
        ];
    }

    public function test_can_self_unenrol_no_multitenancy(): void
    {
        foreach ($this->td_can_self_unenrol_no_multitenancy() as $generate_data) {
            [$user, $group, $expected] = $generate_data();

            self::setUser($user);
            $result = group_interactor::from(group: $group, actor: $user)
                ->can_self_unenrol();

            if (is_bool($expected)) {
                self::assertTrue($result->success);
                self::assertNull($result->code);
                self::assertNull($result->message);
            } else {
                self::assertFalse($result->success);
                self::assertEquals($expected, $result->code);
            }
        }
    }

    private function td_can_self_enrol_with_multitenancy(): array {
        // These test cases follow the multitenancy rules as listed in this page:
        // https://totara.help/docs/multitenancy-in-totara-learn#programs-and-certifications
        return [
            '[tenanted program][isolation on] global user' =>
                fn(): array => [
                    self::create_test_user(),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    true,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[tenanted program][isolation on] user in same tenancy' =>
                function(): array {
                    $tenant = self::create_test_tenant();

                    return [
                        self::create_test_user($tenant),
                        self::create_test_group(tenant: $tenant),
                        true,
                        true
                    ];
                },
            '[tenanted program][isolation on] user in different tenancy' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    true,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[tenanted program][isolation off] global user' =>
                fn(): array => [
                    self::create_test_user(),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    false,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[tenanted program][isolation off] user in same tenancy' =>
                function(): array {
                    $tenant = self::create_test_tenant();

                    return [
                        self::create_test_user($tenant),
                        self::create_test_group(tenant: $tenant),
                        false,
                        true
                    ];
                },
            '[tenanted program][isolation off] user in different tenancy' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    false,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[global program][isolation on] global user' =>
                function(): array {
                    self::enable_multitenancy();

                    return [
                        self::create_test_user(),
                        self::create_test_group(),
                        true,
                        true
                    ];
                },
            '[global program][isolation on] tenanted user' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(),
                    true,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[global program][isolation off] global user' =>
                function(): array {
                    self::enable_multitenancy();

                    return [
                        self::create_test_user(),
                        self::create_test_group(),
                        false,
                        true
                    ];
                },
            '[global program][isolation off] tenanted user' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(),
                    false,
                    true
                ]
        ];
    }

    public function test_can_self_enrol_with_multitenancy(): void {
        foreach ($this->td_can_self_enrol_with_multitenancy() as $scenario => $generate_data) {
            [$user, $group, $isolated, $expected] = $generate_data();
            if ($isolated) {
                tenant_generator::instance()->enable_tenant_isolation();
            } else {
                tenant_generator::instance()->disable_tenant_isolation();
            }

            self::setUser($user);
            $result = group_interactor::from(group: $group, actor: $user)
                ->can_self_enrol();

            if (is_bool($expected)) {
                self::assertTrue($result->success);
                self::assertNull($result->code);
                self::assertNull($result->message);
            } else {
                self::assertFalse($result->success);
                self::assertEquals($expected, $result->code);
            }
        }
    }

    private function td_can_self_unenrol_with_multitenancy(): array {
        // These test cases follow the multitenancy rules as listed in this page:
        // https://totara.help/docs/multitenancy-in-totara-learn#programs-and-certifications
        return [
            '[tenanted program][isolation on] global user' =>
                fn(): array => [
                    self::create_test_user(),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    true,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[tenanted program][isolation on] user in same tenancy' =>
                function (): array {
                    $tenant = self::create_test_tenant();
                    $user = self::create_test_user($tenant);
                    $group = self::create_test_group(tenant: $tenant);
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        true,
                        true
                    ];
                },
            '[tenanted program][isolation on] user in different tenancy' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    true,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[tenanted program][isolation off] global user' =>
                fn(): array => [
                    self::create_test_user(),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    false,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[tenanted program][isolation off] user in same tenancy' =>
                function (): array {
                    $tenant = self::create_test_tenant();
                    $user = self::create_test_user($tenant);
                    $group = self::create_test_group(tenant: $tenant);
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        false,
                        true
                    ];
                },
            '[tenanted program][isolation off] user in different tenancy' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(tenant: self::create_test_tenant()),
                    false,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[global program][isolation on] global user' =>
                function (): array {
                    self::enable_multitenancy();
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        true,
                        true
                    ];
                },
            '[global program][isolation on] tenanted user' =>
                fn(): array => [
                    self::create_test_user(self::create_test_tenant()),
                    self::create_test_group(),
                    true,
                    'group:error:enrol:prog_not_viewable'
                ],
            '[global program][isolation off] global user' =>
                function (): array {
                    self::enable_multitenancy();
                    $user = self::create_test_user();
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        false,
                        true
                    ];
                },
            '[global program][isolation off] tenanted user' =>
                function (): array {
                    $user = self::create_test_user(self::create_test_tenant());
                    $group = self::create_test_group();
                    self::add_user_to_group($user->id, $group->get_instanceid());

                    return [
                        $user,
                        $group,
                        false,
                        true
                    ];
                }
        ];
    }

    public function test_can_self_unenrol_with_multitenancy(): void
    {
        foreach ($this->td_can_self_unenrol_with_multitenancy() as $generate_data) {
            [$user, $group, $isolated, $expected] = $generate_data();
            if ($isolated) {
                tenant_generator::instance()->enable_tenant_isolation();
            } else {
                tenant_generator::instance()->disable_tenant_isolation();
            }

            self::setUser($user);

            $result = group_interactor::from(group: $group, actor: $user)
                ->can_self_unenrol();

            if (is_bool($expected)) {
                self::assertTrue($result->success);
                self::assertNull($result->code);
                self::assertNull($result->message);
            } else {
                self::assertFalse($result->success);
                self::assertEquals($expected, $result->code);
            }
        }
    }

    /**
     * Enables multitenancy.
     */
    private static function enable_multitenancy(): void {
        global $CFG;

        self::setAdminUser();

        $multitenancy_enabled = (bool)($CFG?->tenantsenabled);
        if (!$multitenancy_enabled) {
            tenant_generator::instance()->enable_tenants();
        }
    }

    /**
     * Creates a test tenant.
     *
     * @return null|user the created tenant.
     */
    private static function create_test_tenant(): tenant {
        self::enable_multitenancy();
        return new tenant(tenant_generator::instance()->create_tenant());
    }

    /**
     * Creates a test user.
     *
     * @param ?tenant $tenant if provided, creates a user under this tenant.
     *
     * @return user the created user.
     */
    private static function create_test_user(?tenant $tenant = null): user {
        self::setAdminUser();

        $user = core_generator::instance()
            ->create_user(['tenantid' => $tenant?->id]);

        return new user($user);
    }

    /**
     * Creates a test group.
     *
     * @param bool $live whether to create a live or expired program.
     * @param bool $can_self_enrol if the group allows enrollment.
     * @param bool $can_self_unenrol if the group allows unenrollment.
     * @param user[] $users users to assign to the group if any.
     * @param ?tenant $tenant if provided, creates a program under this tenant.
     *
     * @return group the created group.
     */
    private static function create_test_group(
        bool $live = true,
        bool $can_self_enrol = true,
        bool $can_self_unenrol = true,
        array $users = [],
        ?tenant $tenant = null
    ): group {
        self::setAdminUser();

        $generator = generator::instance();
        $program = $generator->create_program(
            [
                'availableuntil' => $live ? 0 : time() - YEARSECS,
                'category' => $tenant ? $tenant->categoryid : null
            ]
        );

        $group = $generator->create_group_assignment(
            program: $program,
            can_self_enrol: $can_self_enrol,
            can_self_unenrol: $can_self_unenrol
        );

        return $group->add_users($users);
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
