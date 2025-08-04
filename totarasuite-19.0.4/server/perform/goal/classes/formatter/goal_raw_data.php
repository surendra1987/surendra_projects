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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\formatter;

use core\webapi\formatter\formatter;
use core\webapi\formatter\field\text_field_formatter;
use totara_core\dates\date_time_setting;

/**
 * Formats goal_raw_data object data.
 */
class goal_raw_data extends formatter {
    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        $fmt_desc = fn ($desc, text_field_formatter $fmt) => $fmt
            ->set_pluginfile_url_options(
                $this->object->context,
                'perform_goal',
                'description',
                $this->object->id
            )
            ->format($desc);

        return [
            'available_statuses' => null,
            'description' => $fmt_desc,
            'start_date' => null,
            'target_date' => null
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case 'description':
                return goal_description::format($this->object->description);

            case 'start_date':
                return new date_time_setting($this->object->start_date, \core_date::get_user_timezone());

            case 'target_date':
                return new date_time_setting($this->object->target_date, \core_date::get_user_timezone());

            default:
                return parent::get_field($field);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}