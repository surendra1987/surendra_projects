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
use core\webapi\formatter\field\string_field_formatter;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\visibility_conditions\all_responses;
use mod_perform\models\activity\settings\visibility_conditions\none;
use mod_perform\models\activity\settings\visibility_conditions\visibility_option;

/**
 * Class visibility control
 *
 * @package mod_perform\models\activity\settings\control
 */
class visibility extends control {

    /**
     * @return array
     */
    private function get_visibility_condition_options(): array {
        $options = $this->activity->get_visibility_condition_options()->all();

        // Sort options by value.
        usort($options, static fn ($a, $b) => $a->get_value() <=> $b->get_value());

        $formatter = new string_field_formatter(format::FORMAT_PLAIN, $this->activity->get_context());

        return array_map(
            static fn (visibility_option $option): array => [
                'name' => $formatter->format($option->get_name()),
                'value' => $option->get_value()
            ],
            $options
        );
    }

    /**
     * @inheritDoc
     */
    public function get(): array {
        return [
            'anonymous_responses' => [
                'value' => $this->activity->anonymous_responses,
                'mutable' => !$this->activity->is_active(),
            ],
            'valid_closure_state' => $this->is_valid_closure_state(),
            'visibility_condition' => [
                'value' => $this->get_visibility_condition(),
                'anonymous_value' => all_responses::VALUE, // The visibility value to be set when anonymous_responses is enabled
                'options' => $this->get_visibility_condition_options(),
            ],
        ];
    }
}