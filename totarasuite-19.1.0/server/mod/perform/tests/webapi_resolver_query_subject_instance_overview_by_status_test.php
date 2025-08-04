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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package mod_perform
 */

use core_my\perform_overview_util;
use core_phpunit\testcase;
use mod_perform\data_providers\activity\subject_instance_overview;
use mod_perform\entity\activity\subject_instance;
use mod_perform\state\subject_instance\closed as subject_instance_close;
use mod_perform\state\subject_instance\complete;
use mod_perform\state\subject_instance\in_progress;
use mod_perform\state\subject_instance\not_submitted;
use mod_perform\state\participant_instance\complete as participant_complete;
use mod_perform\state\participant_instance\in_progress as participant_in_progress;
use totara_job\job_assignment;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_perform\models\activity\participant_instance as participant_instance_model;

require_once(__DIR__ . '/subject_instance_testcase.php');

class mod_perform_webapi_resolver_query_subject_instance_overview_by_status_test extends testcase {
    private const QUERY = 'mod_perform_subject_instance_overview_by_status';

    use webapi_phpunit_helper;

    private $user;

    protected function setUp(): void {
        parent::setUp();
        perform_overview_util::reset_permission_cache();

        self::setAdminUser();
        $this->user = self::getDataGenerator()->create_user([
            'firstname' => 'Actinguser',
            'middlename' => 'Manfred',
            'lastname' => 'Ziller',
        ]);

        $other_participant = self::getDataGenerator()->create_user([
            'firstname' => 'testor',
            'middlename' => 'Manfred',
            'lastname' => 'Ziller',
        ]);

        $perform_generator = \mod_perform\testing\generator::instance();
        $test_activities = [
            [
                'activity_name' => 'Not started activity 1',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
            ],
            [
                'activity_name' => 'Not started activity 2',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
            ],
            [
                // Not expected to show up because it doesn't have section progress for the subject participant.
                'activity_name' => 'In progress activity 1',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => in_progress::get_code(),
                'subjects_participant_progress' => participant_in_progress::get_code(),
            ],
            [
                'activity_name' => 'In progress activity 2',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => in_progress::get_code(),
                'update_participant_sections_status' => true,
                'subjects_participant_progress' => participant_in_progress::get_code(),
            ],
            [
                'activity_name' => 'Not submit activity 1',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => not_submitted::get_code(),
                'subjects_participant_progress' => participant_in_progress::get_code(),
            ],
            [
                'activity_name' => 'Complete activity 1',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => in_progress::get_code(),
                'subjects_participant_progress' => participant_complete::get_code(),
                'other_participant_id' => $other_participant->id,
            ],
            [
                'activity_name' => 'Complete activity 2',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => in_progress::get_code(),
                'subjects_participant_progress' => participant_complete::get_code(),
                'other_participant_id' => $other_participant->id,
            ],
            [
                'activity_name' => 'Overall complete activity 1',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => complete::get_code(),
                'subject_instance_completed_at' => time() - DAYSECS,
                'subject_instance_availability' => subject_instance_close::get_code()
            ],
            [
                'activity_name' => 'Overall complete activity 2',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => complete::get_code(),
                'subject_instance_completed_at' => time() - DAYSECS * 2,
                'subject_instance_availability' => subject_instance_close::get_code()
            ],
            [
                'activity_name' => 'Overall complete activity 3',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => complete::get_code(),
                'subject_instance_completed_at' => time() - DAYSECS * 30,
                'subject_instance_availability' => subject_instance_close::get_code()
            ],
            [
                'activity_name' => 'Overall complete activity 4',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => complete::get_code(),
                'subject_instance_completed_at' => time() - DAYSECS * 31,
                'subject_instance_availability' => subject_instance_close::get_code()
            ],
            [
                'activity_name' => 'Overall complete activity 5',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => complete::get_code(),
                'subject_instance_completed_at' => time() - DAYSECS * 32,
                'subject_instance_availability' => subject_instance_close::get_code()
            ],
            [
                'activity_name' => 'Overall complete activity 6',
                'subject_user_id' => $this->user->id,
                'subject_is_participating' => true,
                'subject_instance_progress' => complete::get_code(),
                'subject_instance_completed_at' => time() - DAYSECS * 33,
                'subject_instance_availability' => subject_instance_close::get_code()
            ],
        ];

        $now = time();
        $subject_instances = [];
        foreach ($test_activities as $activity) {
            $subject_instances[$activity['activity_name']] = $perform_generator->create_subject_instance($activity);
        }

        // Adjust a timestamp so the order for the "not started" subject instances becomes predictable.
        subject_instance::repository()
            ->where('id', $subject_instances['Not started activity 2']->id)
            ->update(['created_at' => $now - 10]);

        self::setUser($this->user);
    }

    protected function tearDown(): void {
        $this->user = null;
        perform_overview_util::reset_permission_cache();
        parent::tearDown();
    }

    public function test_user_needs_capability(): void {
        global $DB;

        $manager = $this->getDataGenerator()->create_user();
        self::setUser($manager);

        /** @var job_assignment $manager_job_assignment */
        $manager_job_assignment = job_assignment::create([
            'userid' => $manager->id,
            'idnumber' => $manager->id,
        ]);

        job_assignment::create([
            'userid' => $this->user->id,
            'idnumber' => $this->user->id,
            'managerjaid' => $manager_job_assignment->id,
        ]);

        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 365,
                    'status' => subject_instance_overview::STATUS_COMPLETED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];

        perform_overview_util::reset_permission_cache();
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

        perform_overview_util::reset_permission_cache();
        $this->resolve_graphql_query(self::QUERY, $args);
    }

    public function test_bad_status(): void {
        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 365,
                    'status' => 'bad_status'
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];

        $this->expectException(invalid_parameter_exception::class);
        $this->expectExceptionMessage('Invalid status');

        perform_overview_util::reset_permission_cache();
        $this->resolve_graphql_query(self::QUERY, $args);
    }

    public function test_query_completed_activities_with_pagination(): void {
        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 365,
                    'status' => subject_instance_overview::STATUS_COMPLETED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assert_subject_instances([
            'Overall complete activity 1',
            'Overall complete activity 2',
            'Overall complete activity 3',
            'Overall complete activity 4',
            'Overall complete activity 5',
        ], $result);
        $this->assertEquals(6, $result['total']);

        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 365,
                    'status' => subject_instance_overview::STATUS_COMPLETED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 2
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assert_subject_instances([
            'Overall complete activity 6',
        ], $result);
        $this->assertEquals(6, $result['total']);

        // Check period limit
        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 14,
                    'status' => subject_instance_overview::STATUS_COMPLETED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assert_subject_instances([
            'Overall complete activity 1',
            'Overall complete activity 2',
        ], $result);
        $this->assertEquals(2, $result['total']);
    }

    public function test_query_not_started_activities_with_pagination(): void {

        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 14,
                    'status' => subject_instance_overview::STATUS_NOT_STARTED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);

        $this->assert_subject_instances([
            'Not started activity 1',
            'Not started activity 2',
        ], $result);

        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 14,
                    'status' => subject_instance_overview::STATUS_NOT_STARTED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 2
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertEquals(2, $result['total']);
    }

    public function test_query_progressed_activities_with_pagination(): void {
        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 14,
                    'status' => subject_instance_overview::STATUS_PROGRESSED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assert_subject_instances([
            'In progress activity 2',
        ], $result);

        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 14,
                    'status' => subject_instance_overview::STATUS_PROGRESSED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 2
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertEquals(1, $result['total']);
    }

    public function test_query_for_access_removed(): void {
        $args = [
            'input' => [
                'filters' => [
                    'id' => $this->user->id,
                    'period' => 14,
                    'status' => subject_instance_overview::STATUS_NOT_STARTED
                ],
                'pagination' => [
                    'limit' => 5,
                    'page' => 1
                ],
            ]
        ];
        perform_overview_util::reset_permission_cache();
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertCount(2, $result['activities']);

        // Extract one of the participant_instance_ids from the response, and set access_removed true on the instance.
        $pi_url = $result['activities'][0]->url;
        $start_pos = stripos($pi_url, '=') + 1;
        $pi_id = (int)substr($pi_url, $start_pos);
        $pi = participant_instance_model::load_by_id($pi_id);
        $pi->manually_close();
        $pi->set_access_removed(true);
        // We expect this to now exclude one record, since the participant (test acting user) has had their access removed.
        $result = $this->resolve_graphql_query(self::QUERY, $args);
        $this->assertCount(1, $result['activities']);
    }

    private function assert_subject_instances(array $expected_names, array $actual_result): void {
        $actual_names = array_map(static fn ($overview_item) => $overview_item->name, $actual_result['activities']);
        $this->assertEquals($expected_names, $actual_names);
    }
}