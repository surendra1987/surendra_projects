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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

use core\entity\user;
use core\testing\generator as core_generator;
use perform_goal\entity\goal_category;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\goal_raw_data;
use perform_goal\model\status\in_progress;
use perform_goal\model\target_type\date;

require_once(__DIR__.'/perform_goal_testcase.php');

abstract class perform_goal_create_goal_testcase extends perform_goal_testcase {
    protected const MONTHSECS = YEARSECS / 12;

    /**
     * Computes the expected results after a create perform goal operation.
     *
     * @param int $created_goal_id created goal id. Unfortunately, there is no
     *        real way to compute the database timestamps; so this method has to
     *        get them from the database via the given id.
     * @param stdClass $create_data the goal values being created. This comes
     *        from the 'input' section of the args returned from self::setup_env().
     *
     * @return mixed[] (expected goal, goal raw data, interactor) tuple.
     */
    final protected static function expected_results(
        int $created_goal_id,
        stdClass $create_data
    ): array {
        // These are currently hardcoded.
        $assignment_type = 'Self';
        $goal_category_id = goal_category::repository()
            ->where('id_number', 'system-basic')
            ->one(true)
            ->id;

        $db_goal = goal::load_by_id($created_goal_id);
        $context_id = $create_data->context_id;
        $owner_id = $create_data->owner['id'];
        $subject_id = $create_data->user['id'];

        $exp_goal = (object) [
            'assignment_type' => $assignment_type,
            'category_id' => $goal_category_id,
            'closed_at' => null,
            'context' => context::instance_by_id($context_id),
            'context_id' => $context_id,
            'created_at' => $db_goal->created_at,
            'current_value' => $create_data->current_value,
            'current_value_updated_at' => $db_goal->current_value_updated_at,
            'description' => $create_data->description,
            'id' => $created_goal_id,
            'id_number' => $create_data->id_number,
            'name' => $create_data->name,
            'owner' => $owner_id ? new user($owner_id) : null,
            'owner_id' => $owner_id,
            'start_date' => $create_data->start_date,
            'status' => goal::status_from_code($create_data->status),
            'status_updated_at' => $db_goal->status_updated_at,
            'target_date' => $create_data->target_date,
            'target_type' => $create_data->target_type,
            'target_value' => $create_data->target_value,
            'user' => $subject_id ? new user($subject_id) : null,
            'user_id' => $subject_id,
            'updated_at' => $db_goal->updated_at,
            'plugin_name' => 'basic'
        ];

        // The assumption here is the caller validates the expected goal first.
        // Since that used $created_goal_id, if the goal is ok, it means $db_goal
        // is really the created goal and therefore it is ok to use $db_goal in
        // other tests.
        return [
            $exp_goal,
            new goal_raw_data($db_goal),
            goal_interactor::from_goal($db_goal)
        ];
    }

    /**
     * Generates test data.
     *
     * @param null|stdClass $owner goal owner (from core_generator::create_user())
     *        if any. If not provided, will generate one. This user also has the
     *        staff manager role in $context.
     * @param null|stdClass $subject goal subject (from core_generator::create_user())
     *        if any. If not provided, will generate one.
     * @param null|context $context goal context. If unspecified, the goal to be
     *        created will have the subject user context.
     *
     * @return array<string,mixed> the graphql execution args.
     */
    final protected function setup_env(
        ?stdClass $owner = null,
        ?stdClass $subject = null,
        ?context $context = null
    ): array {
        $this->setAdminUser();

        $core_generator  = core_generator::instance();
        $owner_id = $owner ? $owner->id : $core_generator->create_user()->id;
        $subject_id = $subject ? $subject->id : $core_generator->create_user()->id;
        $final_context = $context ?? context_user::instance($subject_id);

        self::assign_as_staff_manager($owner_id, $final_context);

        $args = [
            'input' => [
                'context_id' => $final_context->id,
                'current_value' => 0.0,
                'description' => self::jsondoc_description(
                    '<h1>This is a <strong>test</strong> description</h1>'
                ),
                'id_number' => '12346',
                'name' => 'test_goal2',
                'owner' => [
                    'id' => $owner_id,
                ],
                'start_date' => time(),
                'status' => in_progress::get_code(),
                'target_date' => time() + (self::MONTHSECS * 3),
                'target_type' => date::get_type(),
                'target_value' => 100.0,
                'user' => [
                    'id' => $subject_id,
                ]
            ]
        ];

        return $args;
    }
}
