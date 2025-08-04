<?php
/**
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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\resolver\query;

use coding_exception;
use context_user;
use core\collection;
use core\entity\user;
use core\pagination\offset_cursor;
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
use totara_core\data_provider\provider;

/**
 * Query: "mod_perform_subject_instance_overview_by_status"
 * Query resolver for subject instance overview by status
 */
class subject_instance_overview_by_status extends query_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        $input = $args['input'];

        $subject_user_id = $input['filters']['id'] ?? null;
        self::check_permission($subject_user_id);

        $status = $input['filters']['status'] ?? '';
        self::validate_status($status);

        // We don't need the user id as a filter for the data provider. It is passed to the constructor.
        $filters = [
            'period' => $input['filters']['period'] ?? null
        ];

        $data_provider = (new subject_instance_overview_data_provider($subject_user_id, participant_source::INTERNAL))
            ->add_filters($filters);
        $sort_by = $input['sort_by'] ?? 'last_updated';
        $data_provider->sort_by($sort_by);

        if (isset($input['pagination'])) {
            $data_provider->set_pagination(self::get_offset_cursor($input));
        }

        $ec->set_relevant_context(context_user::instance($subject_user_id));

        /** @var collection|subject_instance_overview_item[] $overview_items */
        $overview_items = $data_provider->{'get_' . $status . '_overview_items'}();
        return [
            'total' => $data_provider->get_total(),
            'activities' => $overview_items->all(),
        ];
    }

    /**
     * @inheritdoc
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            new require_login()
        ];
    }

    /**
     * Check current user permission to manage the subject user
     *
     * @param $subject_user_id
     * @return void
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    private static function check_permission($subject_user_id): void {
        if (empty($subject_user_id)) {
            throw new invalid_parameter_exception('Missing user id');
        }

        if (!perform_overview_util::can_view_activities_overview_for($subject_user_id)) {
            throw new coding_exception('No permissions to get overview data for this user');
        }
    }

    /**
     * Check if the input status is in the status list.
     *
     * @param string $status
     * @return void
     * @throws invalid_parameter_exception
     */
    private static function validate_status(string $status): void {
        if (!in_array($status, [
            subject_instance_overview_data_provider::STATUS_NOT_STARTED,
            subject_instance_overview_data_provider::STATUS_COMPLETED,
            subject_instance_overview_data_provider::STATUS_PROGRESSED,
            subject_instance_overview_data_provider::STATUS_NOT_PROGRESSED,
        ])) {
            throw new invalid_parameter_exception('Invalid status');
        }
    }

    /**
     * process the pagination
     *
     * @param array|null $input
     * @return offset_cursor
     */
    private static function get_offset_cursor(?array $input = null): offset_cursor {
        return offset_cursor::create([
            'page' => $input['pagination']['page'] ?? 1,
            'limit' => $input['pagination']['limit'] ?? provider::DEFAULT_PAGE_SIZE
        ]);
    }
}