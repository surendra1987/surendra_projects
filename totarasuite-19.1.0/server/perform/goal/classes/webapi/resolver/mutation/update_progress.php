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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\resolver\mutation;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use perform_goal\model\goal;
use perform_goal\interactor\goal_interactor;
use perform_goal\webapi\middleware\require_perform_goal;

class update_progress extends mutation_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        /** @var goal $goal */
        $goal = $args['goal'];
        $progress = $args['input'];

        $interactor = goal_interactor::from_goal($goal);
        if (!$interactor->can_set_progress()) {
            throw new coding_exception(
                'You do not currently have permissions to do that (set progress on a goal in this context.)'
            );
        }
        /** @var goal $goal_updated */
        $goal_updated = $goal->update_progress(
            $progress['status'],
            $progress['current_value']
        );
        return [
            'current_value' => $goal_updated->current_value,
            'status' => $goal_updated->status
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
            require_perform_goal::create()
        ];
    }
}
