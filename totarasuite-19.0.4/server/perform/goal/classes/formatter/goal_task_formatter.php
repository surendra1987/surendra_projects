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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\formatter;

use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\formatter;
use core\webapi\formatter\field\date_field_formatter;

/**
 * Provides formatting for fields for a perform_goal_task model (e.g. for UI
 * views).
 */
class goal_task_formatter extends formatter {
    public const ID = 'id';
    public const COMPLETION_DATE = 'completed_at';
    public const CREATION_DATE = 'created_at';
    public const DESC = 'description';
    public const GOAL_ID = 'goal_id';
    public const RESOURCE_EXISTS = 'resource_exists';
    public const RESOURCE_CAN_VIEW = 'resource_can_view';
    public const RESOURCE = 'resource';
    public const UPDATED_DATE = 'updated_at';

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        return [
            self::ID => null,
            self::GOAL_ID => null,
            self::DESC => string_field_formatter::class,
            self::COMPLETION_DATE => date_field_formatter::class,
            self::CREATION_DATE => date_field_formatter::class,
            self::RESOURCE => null,
            self::RESOURCE_EXISTS => null,
            self::RESOURCE_CAN_VIEW => null,
            self::UPDATED_DATE => date_field_formatter::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case self::RESOURCE_EXISTS:
                return $this->object->get_resource_exists();
            case self::RESOURCE_CAN_VIEW:
                return $this->object->get_resource_can_view();
            default:
                return parent::get_field($field);
        }
    }
}
