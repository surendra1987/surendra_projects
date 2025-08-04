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

namespace mod_perform\models\activity\settings\controls;

use core\format;
use mod_perform\data_providers\activity\activity_type as activity_type_provider;
use mod_perform\formatter\activity\activity;
use mod_perform\formatter\activity\activity_type;
use mod_perform\models\activity\activity as activity_model;
use mod_perform\models\activity\activity_type as activity_type_model;

/**
 * Class basic control
 *
 * @package mod_perform\models\activity\settings\control
 */
class basic extends control {

    /**
     * @inheritDoc
     */
    public function get(): array {
        $context = $this->activity->get_context();
        $activity_formatter = new activity($this->activity, $context);
        $activity_type_formatter = new activity_type($this->activity->get_type(), $context);

        return [
            "name" => [
                "display_value" => $activity_formatter->format('name', format::FORMAT_PLAIN),
                "edit_value" => $activity_formatter->format('name', format::FORMAT_RAW),
                "max_length" => activity_model::NAME_MAX_LENGTH,
            ],
            "description" => [
                "display_value" => $activity_formatter->format('description', format::FORMAT_PLAIN),
                "edit_value" => $activity_formatter->format('description', format::FORMAT_RAW),
            ],
            "type" => [
                "id" => $activity_type_formatter->format('id'),
                "display_name" => $activity_type_formatter->format('display_name', format::FORMAT_PLAIN),
            ],
            "state_details" => [
                "name" => $this->activity->get_state_details()::get_name(),
                "display_name" => $this->activity->get_state_details()::get_display_name(),
            ],
            "types" => $this->get_activity_types(),
        ];
    }

    /**
     * Get all activity types and transform them into the array structure we need.
     *
     * @return array
     */
    private function get_activity_types(): array {
        return (new activity_type_provider())->get()->map(
            fn (activity_type_model $activity_type): array => [
                'id' => $activity_type->id,
                'label' => $activity_type->display_name
            ]
        )->to_array();
    }
}
