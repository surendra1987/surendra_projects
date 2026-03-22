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

use core\webapi\execution_context;
use core\webapi\mutation_resolver;
use core\webapi\middleware\require_advanced_feature;
use mod_perform\models\activity\activity;
use mod_perform\models\activity\activity_setting;
use mod_perform\webapi\middleware\require_activity;
use mod_perform\webapi\middleware\require_manage_capability;

class update_activity_closure_settings extends mutation_resolver {

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        /** @type $activity activity */
        $activity = $args['activity'];
        $input = $args['input'];

        $close_on_completion = $input[activity_setting::CLOSE_ON_COMPLETION] ?? null;
        $close_on_section_submission = $input[activity_setting::CLOSE_ON_SECTION_SUBMISSION] ?? null;
        if (!is_null($close_on_section_submission)) {
            $close_on_completion = $close_on_section_submission
                ? true
                : $close_on_completion;
        }

        $settings = [
            activity_setting::CLOSE_ON_SECTION_SUBMISSION => $close_on_section_submission,
            activity_setting::CLOSE_ON_COMPLETION => $close_on_completion,
            activity_setting::CLOSE_ON_DUE_DATE => $input[activity_setting::CLOSE_ON_DUE_DATE] ?? null,
            activity_setting::MANUAL_CLOSE => $input[activity_setting::MANUAL_CLOSE] ?? null,
        ];

        $updates = array_filter($settings, static fn($item) => null !== $item);

        return $activity->settings->update($updates);
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
