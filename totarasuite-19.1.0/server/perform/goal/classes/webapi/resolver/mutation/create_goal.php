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
use context;
use context_tenant;
use context_system;
use context_user;
use core\entity\user;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use Exception;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\goal_category;
use perform_goal\model\goal_raw_data;
use perform_goal\model\target_type\date as target_type_date;

/**
 * This resolver is for adding a totara perform_goal (introduced 2023).
 */
class create_goal extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        $params = $args['input'] ?? [];
        $current_user = user::logged_in();

        if (!isset($params['name'])) { // this seems only needed for unit tests.
            throw new coding_exception('Invalid or missing name.');
        }

        $user_id = null;
        if (isset($params['user'])) {
            $user_record_reference = new user_record_reference();
            $user_id = $user_record_reference->get_record($params['user'])->id;
        }  
        
        $owner_id = null;
        if (isset($params['owner'])) {
            $user_record_reference = new user_record_reference();
            $owner_id = $user_record_reference->get_record($params['owner'])->id;
        }

        // Optional fields.
        // Need validation for a context_id. This will do for now.
        try {
            if (isset($params['context_id'])) {
                $context = context::instance_by_id($params['context_id']);
            } else {
                if (!empty($user_id)) {
                    $context = context_user::instance($user_id);
                } else if (!empty($CFG->tenantsenabled) && isset($current_user->tenantid)) {
                    $context = context_tenant::instance($current_user->tenantid);
                } else {
                    $context = context_system::instance();
                }
            }
        } catch (Exception $exc) {
            throw new coding_exception('Invalid or missing context_id');
        }
        $id_number = $params['id_number'] ?? null;
        $description = $params['description'] ?? null;

        $target_type = $params['target_type'] ?? target_type_date::get_type();
        $current_value = $params['current_value'] ?? 0.0;
        $status = $params['status'] ?? 'not_started';

        // Use the basic system goal_category for now.
        $basic_category_entity = goal_category_entity::repository()
            ->where('id_number', '=', 'system-basic')
            ->one(true);
        $basic_category = goal_category::load_by_entity($basic_category_entity);

        if (is_null($user_id)) {
            // Only personal goals are implemented currently, throw an exception.
            throw new coding_exception('Only personal goals are implemented currently.');
        }

        $interactor = new goal_interactor($context);
        if (!$interactor->can_create_personal_goal()) {
            throw new coding_exception(
                'Sorry, but you do not currently have permissions to do that (create a goal in this context).'
            );
        }
        if ($owner_id && !$interactor->can_set_owner_id($owner_id)) {
            throw new coding_exception(
                'Sorry, but you do not currently have permissions to do that (set owner id in this context).'
            );
        }

        $goal_created = goal::create(
            $context,
            $basic_category,
            $params['name'],
            $params['start_date'],
            $target_type,
            $params['target_date'],
            $params['target_value'],
            $current_value,
            $status,
            $owner_id,
            $user_id,
            $id_number,
            $description,
        );

        return [
            'goal' => $goal_created,
            'raw' => new goal_raw_data($goal_created),
            'permissions' => goal_interactor::from_goal($goal_created)
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('perform_goals')
        ];
    }
}
