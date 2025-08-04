<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 */

namespace totara_competency\formatter\perform_overview;

use context_system;
use core\webapi\formatter\formatter;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\field\text_field_formatter;

/**
 * Formats a totara_competency\models\perform_overview\item object.
 */
class item extends formatter {
    public const ACHIEVEMENT_ID = 'achievement_id';
    public const ACHIEVEMENT_DATE = 'achievement_date';
    public const ACHIEVEMENT_LEVEL = 'achievement_level';
    public const ASSIGNMENT_ID = 'assignment_id';
    public const ASSIGNMENT_DATE = 'assignment_date';
    public const ASSIGNMENT_TYPE = 'assignment_type';
    public const ASSIGNMENT_URL = 'url';
    public const COMPETENCY_ID = 'raw_id';
    public const COMPETENCY_DESC = 'description';
    public const COMPETENCY_NAME = 'name';
    public const LAST_UPDATE = 'last_update';
    public const UNIQUE_ID = 'id';
    public const USER_ID = 'user_id';

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        $text_area_formatter = fn ($value, text_field_formatter $formatter): string => $formatter
            ->set_pluginfile_url_options(
                context_system::instance(),
                'totara_hierarchy',
                'comp',
                $this->object->get_competency_id()
            )
            ->format($value);

        return [
            self::ACHIEVEMENT_ID => null,
            self::ACHIEVEMENT_DATE => date_field_formatter::class,
            self::ACHIEVEMENT_LEVEL => string_field_formatter::class,
            self::ASSIGNMENT_ID => null,
            self::ASSIGNMENT_DATE => date_field_formatter::class,
            self::ASSIGNMENT_TYPE => string_field_formatter::class,
            self::ASSIGNMENT_URL => null,
            self::COMPETENCY_ID => null,
            self::COMPETENCY_DESC => $text_area_formatter,
            self::COMPETENCY_NAME => string_field_formatter::class,
            self::LAST_UPDATE => null,
            self::UNIQUE_ID => null,
            self::USER_ID => null
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case self::ASSIGNMENT_TYPE:
                return $this->object->get_assignment_type();

            case self::COMPETENCY_ID:
                return $this->object->get_competency_id();

            case self::COMPETENCY_DESC:
                return $this->object->get_competency_description() ?? '';

            case self::COMPETENCY_NAME:
                return $this->object->get_competency_name();

            case self::ACHIEVEMENT_ID:
            case self::ACHIEVEMENT_LEVEL:
            case self::ACHIEVEMENT_DATE:
            case self::ASSIGNMENT_ID:
            case self::ASSIGNMENT_DATE:
            case self::ASSIGNMENT_URL:
            case self::LAST_UPDATE:
            case self::USER_ID:
                $getter = "get_$field";
                return $this->object->$getter();

            case self::UNIQUE_ID:
                return $this->object->get_achievement_id();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}