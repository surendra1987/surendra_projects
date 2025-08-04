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

namespace perform_goal\webapi\resolver\mutation;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\mutation_resolver;
use perform_goal\model\goal_raw_data;
use perform_goal\interactor\goal_interactor;
use perform_goal\webapi\middleware\require_perform_goal;

/**
 * This resolver is for editing a totara perform_goal (introduced 2023).
 */
class update_goal extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $params = $args['input'] ?? [];

        $name = $params['name'] ?? null;
        $id_number = $params['id_number'] ?? null;
        $description = $params['description'] ?? null;
        $start_date = $params['start_date'] ?? null;
        $target_type = $params['target_type'] ?? null;
        $target_date = $params['target_date'] ?? null;
        $target_value = $params['target_value'] ?? null;

        // To find a goal to update, there must be either a goal id or id_number passed in as a goal_reference. This is checked by middleware.
        $goal_model = $args['goal'];

        // Validate the goal is a personal goal.
        if (is_null($goal_model->user_id)) {
            throw new coding_exception('Only personal goals are implemented currently.');
        }

        $interactor = goal_interactor::from_goal($goal_model);
        if (!$interactor->can_manage()) {
            throw new coding_exception(
                'Sorry, but you do not currently have permissions to do that (edit a goal in this context).'
            );
        }

        $goal_updated = $goal_model->update(
            $name,
            $id_number,
            $description,
            $start_date,
            $target_type,
            $target_date,
            $target_value
        );

        return [
            'goal' => $goal_updated,
            'raw' => new goal_raw_data($goal_updated),
            'permissions' => $interactor
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'), // Totara perform_goals must be enabled
            require_perform_goal::create()
        ];
    }
}
