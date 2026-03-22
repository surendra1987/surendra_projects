<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

use totara_webapi\phpunit\webapi_phpunit_helper;
use perform_goal\model\perform_status_change as perform_status_change_model;

require_once(__DIR__.'/perform_goal_perform_status_change_testcase.php');

/**
 * Unit tests for the 'perform_goal_perform_change_status' API operation request, using the AJAX API.
 */
class perform_goal_webapi_resolver_mutation_perform_change_status_test extends perform_goal_perform_status_change_testcase {
    private const MUTATION = 'perform_goal_perform_change_status';

    use webapi_phpunit_helper;

    public function test_for_already_existing_record_ajax(): void {
        global $DB;
        $data = $this->create_activity_data();
        $role = $DB->get_record('role', ['shortname' => 'staffmanager']);
        role_assign($role->id, $data->manager_user->id, context_system::instance()->id);
        self::setUser($data->manager_user);
        // Set up - create a perform_status_change record.
        $result = perform_status_change_model::create(
            $data->manager_participant_instance1->id,
            $data->section_element->id,
            'not_started',
            25.0,
            $data->goal1->id
        );

        $args = [
            'input' => [
                'participant_instance_id' => $data->manager_participant_instance1->id,
                'goal_id' => $data->goal1->id,
                'current_value' => 26.0,
                'status' => 'in_progress',
                'section_element_id' => $data->section_element->id
            ]
        ];
        // Operate.
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        // Data should not be updated - initially saved data expected.
        $result_perform_status_change = $result[0]['perform_status_change'];
        self::assertEquals(25.0, $result_perform_status_change['current_value']);
        self::assertEquals('not_started', $result_perform_status_change['status']['id']);
        self::assertEquals($data->manager_user->fullname,  $result_perform_status_change['status_changer_user']['fullname']);
        self::assertTrue($result[0]['already_exists']);
    }

    public function test_with_valid_inputs_ajax(): void {
        global $DB;
        $data = $this->create_activity_data();
        $role = $DB->get_record('role', ['shortname' => 'staffmanager']);
        role_assign($role->id, $data->manager_user->id, context_system::instance()->id);
        self::setUser($data->manager_user);
        $now = time();

        $args = [
            'input' => [
                'participant_instance_id' => $data->manager_participant_instance1->id,
                'goal_id' => $data->goal1->id,
                'current_value' => 26.0,
                'status' => 'in_progress',
                'section_element_id' => $data->section_element->id
            ]
        ];
        // Operate.
        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        $result_perform_status_change = $result[0]['perform_status_change'];
        self::assertEquals($args['input']['current_value'], $result_perform_status_change['current_value']);
        self::assertEquals($args['input']['status'], $result_perform_status_change['status']['id']);
        self::assertEquals($data->manager_user->fullname,  $result_perform_status_change['status_changer_user']['fullname']);
        self::assertFalse($result[0]['already_exists']);

        $result_date_for_comparison = DateTime::createFromFormat('j/m/Y', $result_perform_status_change['created_at'])->getTimestamp();
        self::assertGreaterThanOrEqual($now, $result_date_for_comparison);
    }

    public function test_with_invalid_permissions_ajax(): void {
        $data = $this->create_activity_data();
        self::setUser($data->another_user);
        // Build a graphQL request string.
        $args = [
            'input' => [
                'participant_instance_id' => $data->manager_participant_instance1->id,
                'goal_id' => $data->goal1->id,
                'current_value' => 26.0,
                'status' => 'in_progress',
                'section_element_id' => $data->section_element->id
            ]
        ];

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        self::assert_webapi_operation_failed($result,
            'You do not currently have permissions to do that (update status in this context.)'
        );
    }
}
