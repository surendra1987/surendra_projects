<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use perform_goal\entity\goal;
use perform_goal\event\personal_goal_viewed;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal_raw_data;
use perform_goal\webapi\middleware\require_perform_goal;

/**
 * Handles the "perform_goal_view_goal" GraphQL query.
 */
class view_goal extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        // The require_perform_goal middleware retrieves the goal and passes it
        // via $args.
        $goal = $args[require_perform_goal::GOAL_KEY];

        if ($goal) {
            $interactor = goal_interactor::from_goal($goal);

            if ($interactor->can_view()) {
                personal_goal_viewed::create_from_instance(new goal($goal->id))
                    ->trigger();

                return [
                    'goal' => $goal,
                    'raw' => new goal_raw_data($goal),
                    'permissions' => $interactor
                ];
            }
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            new require_login(),
            new require_authenticated_user(),
            require_perform_goal::create()
                ->disable_throw_exception_on_missing_goal()
        ];
    }
}
