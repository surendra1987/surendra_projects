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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use perform_goal\settings_helper;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use perform_goal\totara_comment\comment_resolver;
use totara_webapi\phpunit\webapi_phpunit_helper;
use core\json_editor\node\paragraph;

class perform_goal_webapi_resolver_mutation_create_comment_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'totara_comment_create_comment';

    public function test_create_comments_by_subject_user(): void {
        /** @var perform_goal\model\goal $goal */
        [$args, ] = $this->setup_env();

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        $comment = $this->get_webapi_operation_data($result);
        self::assertNotEmpty($comment);
    }

    public function test_create_comments_by_relationship_user(): void {
        /** @var perform_goal\model\goal $goal */
        [$args, $subject_user] = $this->setup_env();

        $participant = self::getDataGenerator()->create_user();
        $context = context_user::instance($subject_user->id);

        self::assign_as_staff_manager($participant->id, $context);
        self::setUser($participant);

        $result = $this->parsed_graphql_operation(self::MUTATION, $args);
        $this->assert_webapi_operation_successful($result);

        $comment = $this->get_webapi_operation_data($result);
        self::assertNotEmpty($comment);

    }

    public function test_create_comments_by_other_user(): void {
        /** @var perform_goal\model\goal $goal */
        [$args, ] = $this->setup_env();

        $other_user = self::getDataGenerator()->create_user();
        self::setUser($other_user);

        $this->assert_webapi_operation_failed(
            $this->parsed_graphql_operation(self::MUTATION, $args),
            'Cannot create a comment'
        );
    }

    /**
     * Generates a goal data
     * @return mixed
     */
    final protected function setup_env() {
        self::setAdminUser();

        settings_helper::enable_perform_goals();

        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $data = goal_generator_config::new(['user_id' => $user->id]);
        $goal = generator::instance()->create_goal($data);

        $args = [
            'instanceid' => $goal->get_id(),
            'component' => settings_helper::get_component(),
            'area' => comment_resolver::AREA,
            'content' => json_encode([
                'type' => 'doc',
                'content' => [paragraph::create_json_node_from_text('My Comment')]
            ], JSON_THROW_ON_ERROR),
            'format' => FORMAT_JSON_EDITOR
        ];

        return [$args, $user];
    }

    /**
     * Assigns the staff manager role in the given context to the specified user.
     *
     * @param int $user_id id of user with the staff manager role.
     * @param null|context $context context in which to assign role. If not given
     *        uses the system context.
     */
    final protected static function assign_as_staff_manager(
        int $user_id,
        ?context $context = null
    ): void {
        global $DB;
        $role_id = $DB->get_record('role', ['shortname' => 'staffmanager'])->id;

        role_assign(
            $role_id, $user_id, $context ?? context_system::instance()->id
        );
    }
}
