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

use core\testing\generator as core_generator;
use perform_goal\entity\goal as goal_entity;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\goal_raw_data;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;

require_once(__DIR__.'/perform_goal_testcase.php');

abstract class perform_goal_update_goal_testcase extends perform_goal_testcase {
    protected const MONTHSECS = YEARSECS / 12;

    /**
     * Computes the expected results after an edit perform goal operation.
     *
     * @param goal $original_goal the goal being edited.
     * @param stdClass $edit_data the goal values being changed. This comes from
     *        the 'input' section of the args returned from self::setup_env().
     *
     * @return mixed[] (expected goal, goal raw data, interactor) tuple.
     */
    final protected static function expected_results(
        goal $original_goal,
        stdClass $edit_data
    ): array {
        // Need to get the updated database timestamps for the expected updated
        // goal; no real way to compute it otherwise.
        $db_goal = goal::load_by_id($original_goal->id);

        $exp_goal = (object) [
            'assignment_type' => $original_goal->assignment_type,
            'plugin_name' => $original_goal->plugin_name,
            'category_id' => $original_goal->category_id,
            'closed_at' => $original_goal->closed_at,
            'context' => $original_goal->context,
            'context_id' => $original_goal->context_id,
            'created_at' => $db_goal->created_at,
            'current_value' => $original_goal->current_value,
            'current_value_updated_at' => $db_goal->current_value_updated_at,
            'description' => $edit_data->description,
            'id' => $original_goal->id,
            'id_number' => $edit_data->id_number,
            'name' => $edit_data->name,
            'owner' => $original_goal->owner,
            'owner_id' => $original_goal->owner->id,
            'start_date' => $edit_data->start_date,
            'status' => $original_goal->status,
            'status_updated_at' => $db_goal->status_updated_at,
            'target_date' => $edit_data->target_date,
            'target_type' => $edit_data->target_type,
            'target_value' => $edit_data->target_value,
            'user' => $original_goal->user,
            'user_id' => $original_goal->user->id,
            'updated_at' => $db_goal->updated_at
        ];

        // The assumption here is the caller validates the expected goal first.
        // Since that derives from the original goal with timestamps from the
        // database, if that is ok, it implies $db_goal is really the updated
        // goal and therefore it is ok to use $db_goal in other tests.
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
     * @param null|context $context goal context. If unspecified, the goal is
     *        created with the subject user context.
     *
     * @return mixed[] (graphql execution args, goal) tuple.
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

        $cfg = [
            'description' => '{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"test description"}]}]}',
            'owner_id' => $owner_id,
            'user_id' => $subject_id,
            'target_type' => date::get_type(),
            'context' => $final_context
        ];

        $goal = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        // Unfortunately the goal generator requires a valid target type to
        // create a goal. For the purposes of testing updates, we change it here
        // directly in the database to 'simulate' another target type.
        $goal_entity = goal_entity::repository()->one($goal->id, true);
        $goal_entity->target_type = 'test target type, alter me';
        $goal_entity->save();

        $args = [
            'goal_reference' => ['id' => $goal->id],
            'input' => [
                'name' => $goal->name  . '_altered',
                'id_number' => $goal->id_number . '_altered',
                'description' => self::jsondoc_description(
                    '<h1>This is a <strong>test</strong> description</h1>'
                ),
                'start_date' => $goal->start_date + self::MONTHSECS,
                'target_date' => $goal->target_date + self::MONTHSECS * 2,
                'target_type' => date::get_type(),
                'target_value' => $goal->target_value - 1
            ]
        ];

        return [$args, $goal->refresh(true)];
    }
}
