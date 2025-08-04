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

namespace perform_goal\formatter;

use core\webapi\formatter\formatter;
use core\webapi\formatter\field\date_field_formatter;

/**
 * Provides formatting for fields for a perform_goal_perform_status_change model.
 */
class perform_status_change extends formatter {

    use float_formatter_trait;

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {

        return [
            'id' => null,
            'goal_id' => null,
            'subject_user_id' => null,
            'status' => null,
            'current_value' => 'format_float',
            'activity_id' => null,
            'subject_instance_id' => null,
            'status_changer_user' => null,
            'status_changer_relationship_id' => null,
            'created_at' => date_field_formatter::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}
