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

namespace perform_goal\formatter\overview;

use core\webapi\formatter\formatter;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\field\text_field_formatter;
use perform_goal\formatter\goal_description;

/**
 * Formats a perform_goal\model\overview\item object.
 */
class item extends formatter {
    public const ASSIGNMENT_DATE = 'assignment_date';
    public const ACHIEVEMENT_DATE = 'achievement_date';
    public const DESC = 'description';
    public const DUE = 'due';
    public const GOAL_ID = 'id';
    public const LAST_UPDATE = 'last_update';
    public const NAME = 'name';
    public const RAW_ID = 'raw_id';
    public const URL = 'url';
    public const USER_ID = 'user_id';

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        $fmt_desc = fn ($desc, text_field_formatter $fmt) => $fmt
            ->set_pluginfile_url_options(
                $this->object->goal->context,
                'perform_goal',
                'description',
                $this->object->id
            )
            ->format($desc);

        return [
            self::ACHIEVEMENT_DATE => date_field_formatter::class,
            self::ASSIGNMENT_DATE => date_field_formatter::class,
            self::DESC => $fmt_desc,
            self::DUE => null,
            self::GOAL_ID => null,
            self::LAST_UPDATE => null,
            self::NAME => string_field_formatter::class,
            self::RAW_ID => null,
            self::URL => null,
            self::USER_ID => null
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case self::DESC:
                return goal_description::format($this->object->description);

            case self::RAW_ID:
                return $this->object->id;

            case self::USER_ID:
                return $this->object->goal->user_id;

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
