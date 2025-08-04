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

use context;
use core\webapi\formatter\formatter;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\field\text_field_formatter;

/**
 * Provides formatting for fields for a perform_goal model (e.g. for UI views).
 */
class goal extends formatter {

    use float_formatter_trait;

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        $fmt_desc = fn ($desc, text_field_formatter $fmt) => $fmt
            ->set_pluginfile_url_options(
                context::instance_by_id($this->object->context_id),
                'perform_goal',
                'description',
                $this->object->id
            )
            ->format($desc);

        return [
            'id' => null,
            'context_id' => null,
            'owner' => null,
            'user' => null,
            'name' => string_field_formatter::class,
            'id_number' => string_field_formatter::class,
            'description' => $fmt_desc,
            'assignment_type' => null,
            'plugin_name' => null,
            'start_date' => date_field_formatter::class,
            'target_type' => null,
            'target_date' => date_field_formatter::class,
            'target_value' => 'format_float',
            'current_value' => 'format_float',
            'current_value_updated_at' => date_field_formatter::class,
            'status' => null,
            'status_updated_at' => date_field_formatter::class,
            'closed_at' => date_field_formatter::class,
            'created_at' => date_field_formatter::class,
            'updated_at' => date_field_formatter::class,
            'category_id' => null,
            'comment_count' => null,
            'goal_tasks_metadata' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case 'description':
                return goal_description::format($this->object->description);

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