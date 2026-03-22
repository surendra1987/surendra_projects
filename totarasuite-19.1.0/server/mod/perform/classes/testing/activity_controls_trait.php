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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\testing;

use mod_perform\models\activity\activity;
use mod_perform\models\activity\settings\visibility_conditions\all_responses;
use mod_perform\models\activity\settings\visibility_conditions\none;
use mod_perform\models\activity\settings\visibility_conditions\own_response;

trait activity_controls_trait {

    protected function create_activity(?activity_generator_configuration $configuration = null): activity {
        self::setAdminUser();
        $generator = self::getDataGenerator();
        /** @var \mod_perform\testing\generator $perform_generator */
        $perform_generator = $generator->get_plugin_generator('mod_perform');

        $configuration = $configuration ?? activity_generator_configuration::new();

        $activities = $perform_generator->create_full_activities($configuration);
        /** @var activity $activity */
        return $activities->first();
    }

    public static function visibility_control_valid_closure_state_data_provider(): array {
        return [
            // Always valid closure state when visibility is set to 'none'
            [none::VALUE, true, true, true],
            [none::VALUE, true, false, true],
            [none::VALUE, false, true, true],
            [none::VALUE, false, false, true],

            // Invalid closure state when visibility is set to something other than 'none' AND none of the closure settings is enabled.
            [own_response::VALUE, true, true, true],
            [own_response::VALUE, true, false, true],
            [own_response::VALUE, false, true, true],
            [own_response::VALUE, false, false, false],

            [all_responses::VALUE, true, true, true],
            [all_responses::VALUE, true, false, true],
            [all_responses::VALUE, false, true, true],
            [all_responses::VALUE, false, false, false],
        ];
    }
}
