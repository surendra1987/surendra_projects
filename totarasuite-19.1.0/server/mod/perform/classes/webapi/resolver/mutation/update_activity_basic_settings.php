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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
*/

namespace mod_perform\webapi\resolver\mutation;

use core\orm\query\builder;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\mutation_resolver;
use mod_perform\models\activity\activity;
use mod_perform\webapi\middleware\require_activity;
use mod_perform\webapi\middleware\require_manage_capability;

class update_activity_basic_settings extends mutation_resolver {

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        // The require_activity middleware loads the activity and passes it along via the args
        /** @var activity $activity */
        $activity = $args['activity'];
        $input = $args['input'];

        builder::get_db()->transaction(
            function () use ($input, $activity) {
                $type_id = $activity->is_draft() ? $input['type_id'] : null;
                $activity->set_general_info($input['name'], $input['description'] ?? null, $type_id);
                $activity->update();
            }
        );

        return [
            'id' => $activity->id,
            'name' => $activity->name,
            'description' => $activity->description,
            'type' => $activity->get_type()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_advanced_feature('performance_activities'),
            require_activity::by_activity_id('input.activity_id', true),
            require_manage_capability::class
        ];
    }
}
