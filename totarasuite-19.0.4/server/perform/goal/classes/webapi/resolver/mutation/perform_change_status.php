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
use core\webapi\middleware\require_advanced_feature;
use mod_perform\models\activity\participant_instance;
use perform_goal\interactor\goal_interactor;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use perform_goal\model\perform_status_change as perform_status_change_model;
use perform_goal\model\goal as goal_model;

/**
 * This resolver is for adding a perform_goal_perform_status_change record, to keep a log of when a performance activity
 * linked_review goal had its status changed. (For totara perform_goals, introduced 2023).
 */
class perform_change_status extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        $params = $args['input'] ?? [];

        $participant_instance_id = $params['participant_instance_id'];
        $current_value = $params['current_value'];
        $goal_id = $params['goal_id'];
        $status = $params['status'];
        $section_element_id = $params['section_element_id'];

        $goal_model = goal_model::load_by_id($goal_id);
        $interactor = goal_interactor::from_goal($goal_model);
        if (!$interactor->can_set_progress()) {
            throw new coding_exception(
                'You do not currently have permissions to do that (update status in this context.)'
            );
        }

        $participant_instance = participant_instance::load_by_id($participant_instance_id);
        $existing_status = perform_status_change_model::get_existing_status(
            $goal_id, $participant_instance->subject_instance_id, $participant_instance->core_relationship_id
        );

        if ($existing_status) {
            return [
                'perform_status_change' => $existing_status,
                'already_exists' => true,
            ];
        }

        $new_status = perform_status_change_model::create(
            $participant_instance_id,
            $section_element_id,
            $status,
            $current_value,
            $goal_id
        );

        return ['perform_status_change' => $new_status,
            'already_exists' => false
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals'),
        ];
    }
}
