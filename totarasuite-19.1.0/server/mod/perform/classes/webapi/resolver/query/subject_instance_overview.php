<?php
/*
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\query;

use coding_exception;
use context_user;
use core\collection;
use core\entity\user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use core_my\perform_overview_util;
use invalid_parameter_exception;
use mod_perform\data_providers\activity\subject_instance_overview as subject_instance_overview_data_provider;
use mod_perform\models\activity\participant_source;
use mod_perform\models\activity\subject_instance_overview_item;
use mod_perform\util;

/**
 * Query: "mod_perform_subject_instance_overview"
 * Query resolver for subject instance overview
 */
class subject_instance_overview extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $args = $args['input'];
        $subject_user_id = $args['filters']['id'] ?? null;

        if (!$subject_user_id) {
            throw new invalid_parameter_exception('Missing user id');
        }

        $logged_in_user_id = user::logged_in()->id;

        // Check capabilities
        if (!perform_overview_util::can_view_activities_overview_for($subject_user_id)) {
            throw new coding_exception('No permissions to get overview data for this user');
        }

        // We don't need the user id as a filter for the data provider. It is passed to the constructor.
        $filters = [
            'period' => $args['filters']['period'] ?? null
        ];

        $sort_by = $args['sort_by'] ?? 'last_updated';

        $completed = self::get_data_by_status(
            $subject_user_id,
            $filters,
            $sort_by,
            subject_instance_overview_data_provider::STATUS_COMPLETED
        );
        $progressed = self::get_data_by_status(
            $subject_user_id,
            $filters,
            $sort_by,
            subject_instance_overview_data_provider::STATUS_PROGRESSED
        );
        $not_progressed = self::get_data_by_status(
            $subject_user_id,
            $filters,
            $sort_by,
            subject_instance_overview_data_provider::STATUS_NOT_PROGRESSED
        );
        $not_started = self::get_data_by_status(
            $subject_user_id,
            $filters,
            $sort_by,
            subject_instance_overview_data_provider::STATUS_NOT_STARTED
        );

        $results = [
            'total' => $completed->count() + $progressed->count() + $not_progressed->count() + $not_started->count(),
            'due_soon' => subject_instance_overview_item::count_due_soon([$completed, $progressed, $not_progressed, $not_started]),
            'state_counts' => [
                'completed' => $completed->count(),
                'progressed' => $progressed->count(),
                'not_progressed' => $not_progressed->count(),
                'not_started' => $not_started->count(),
            ],
            'activities' => [
                'completed' => array_slice($completed->all(), 0, 2),
                'progressed' => array_slice($progressed->all(), 0, 2),
                'not_progressed' => array_slice($not_progressed->all(), 0, 2),
                'not_started' => array_slice($not_started->all(), 0, 2),
            ]
        ];

        $ec->set_relevant_context(context_user::instance($subject_user_id));

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            new require_login()
        ];
    }

    /**
     * @param int $user_id
     * @param array $filters
     * @param string $sort_by
     * @param string $status
     * @return collection
     */
    private static function get_data_by_status(int $user_id, array $filters, string $sort_by, string $status): collection {
        $data_provider = (new subject_instance_overview_data_provider($user_id, participant_source::INTERNAL))
            ->add_filters($filters);
        $data_provider->sort_by($sort_by);
        $function_name = 'get_' . $status . '_overview_items';
        return $data_provider->$function_name();
    }
}