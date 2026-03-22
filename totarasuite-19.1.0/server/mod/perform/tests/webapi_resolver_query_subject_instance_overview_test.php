<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

use core\date_format;
use core\webapi\formatter\field\date_field_formatter;
use core_my\perform_overview_util;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\subject_instance;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__ . '/subject_instance_testcase.php');

/**
 * @group perform
 */
class mod_perform_webapi_resolver_query_subject_instance_overview_test extends mod_perform_subject_instance_testcase {
    private const QUERY = 'mod_perform_subject_instance_overview';

    use webapi_phpunit_helper;

    protected function setUp(): void {
        parent::setUp();
        perform_overview_util::reset_permission_cache();
    }

    protected function tearDown(): void {
        perform_overview_util::reset_permission_cache();
        parent::tearDown();
    }

    public function test_query_successful(): void {

        // Progress one subject instance and add a due date, so we can check the "last updated" and "due" elements.
        $another_subject_instance = $this->create_subject_instance_with_subject_participating();
        $this->advance_subject_progress($another_subject_instance);
        $due_date = time() - DAYSECS;
        /** @var subject_instance $subject_instance_entity */
        $subject_instance_entity = $another_subject_instance->get_entity_copy();
        $subject_instance_entity->due_date = $due_date;
        $subject_instance_entity->save();

        $args = [
            'input' => [
                'filters' => [
                    'id' => self::$user->id,
                    'period' => 14,
                ]
            ]
        ];

        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $this->assert_webapi_operation_successful($result);
        $actual = $this->get_webapi_operation_data($result);

        /** @var participant_instance $participant_instance1 */
        $participant_instance1 = self::$about_user_and_participating
            ->participant_instances
            ->filter('participant_id', self::$user->id)
            ->first();
        $participant_instance2 = $another_subject_instance
            ->participant_instances
            ->filter('participant_id', self::$user->id)
            ->first();
        $formatter = new date_field_formatter(date_format::FORMAT_DATELONG, context_system::instance());
        $last_update_date = $formatter->format(
            $participant_instance2->get_most_recently_progressed_participant_section()->updated_at
        );
        $assignment_date1 = $formatter->format($participant_instance1->created_at);
        $assignment_date2 = $formatter->format($participant_instance2->created_at);
        $due_date_activity2 = $formatter->format($due_date);

        self::assertEqualsCanonicalizing([
            'total' => 2,
            'due_soon' => 0,
            'state_counts' => [
                'completed' => 0,
                'progressed' => 1,
                'not_progressed' => 0,
                'not_started' => 1,
            ],
            'activities' => [
                'completed' => [],
                'progressed' => [
                    0 => [
                        '__typename' => 'mod_perform_subject_instance_overview_item',
                        'id' => $another_subject_instance->id,
                        'name' => 'another activity',
                        'url' => 'https://www.example.com/moodle/mod/perform/activity/view.php?participant_instance_id='
                            . $participant_instance2->id,
                        'completion_date' => null,
                        'description' => 'test description',
                        'assignment_date' => $assignment_date2,
                        'job_assignment' => null,
                        'last_update' =>
                            [
                                'date' => $last_update_date,
                                'description' => 'Activity was viewed.',
                            ],
                        'due' =>
                            [
                                'date' => $due_date_activity2,
                                'due_soon' => false,
                                'overdue' => true,
                            ],
                    ]
                ],
                'not_progressed' => [],
                'not_started' => [
                    0 => [
                        '__typename' => 'mod_perform_subject_instance_overview_item',
                        'id' => self::$about_user_and_participating->id,
                        'name' => 'appraisal activity about target user',
                        'url' => 'https://www.example.com/moodle/mod/perform/activity/view.php?participant_instance_id='
                            . $participant_instance1->id,
                        'completion_date' => null,
                        'description' => 'test description',
                        'assignment_date' => $assignment_date1,
                        'job_assignment' => null,
                        'last_update' =>
                            [
                                'date' => null,
                                'description' => null,
                            ],
                        'due' =>
                            [
                                'date' => null,
                                'due_soon' => false,
                                'overdue' => false,
                            ],
                    ]
                ],
            ]
        ], $actual);
    }

    public function test_user_needs_capability(): void {
        global $DB;

        $manager = $this->getDataGenerator()->create_user();
        self::setUser($manager);

        $this->setup_manager_employee_job_assignment($manager, self::$user);

        $args = [
            'input' => [
                'filters' => [
                    'id' => self::$user->id,
                    'period' => 14,
                ]
            ]
        ];
        $result = $this->resolve_graphql_query(self::QUERY, $args);

        // Make sure we get a result.
        self::assertNotEmpty($result['activities']);

        $manager_role = $DB->get_record('role', ['shortname' => 'staffmanager']);
        unassign_capability('mod/perform:manage_subject_user_participation', $manager_role->id, SYSCONTEXTID);
        
        $user_role = $DB->get_record('role', ['shortname' => 'user']);
        unassign_capability('mod/perform:manage_staff_participation', $user_role->id);

        perform_overview_util::reset_permission_cache();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('No permissions to get overview data for this user');

        $this->resolve_graphql_query(self::QUERY, $args);
    }

    public function test_with_participant_access_removed(): void {
        $subject_instance = $this->create_subject_instance_with_subject_participating();
        $args = [
            'input' => [
                'filters' => [
                    'id' => $subject_instance->subject_user_id, // self::$user->id,
                    'period' => 14,
                ]
            ]
        ];

        self::setUser($subject_instance->subject_user_id);
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $actual = $this->get_webapi_operation_data($result);
        self::assertCount(2, $actual['activities']['not_started']);

        self::set_participant_instance_access_removed($subject_instance, true);
        // The participant_instance with its access_removed set should now be excluded in the response.
        $result = $this->parsed_graphql_operation(self::QUERY, $args);
        $actual = $this->get_webapi_operation_data($result);
        self::assertCount(1, $actual['activities']['not_started']);
    }
}
