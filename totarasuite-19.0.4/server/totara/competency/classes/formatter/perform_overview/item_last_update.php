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

use core\webapi\formatter\formatter;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;

/**
 * Formats a totara_competency\models\perform_overview\item_last_update object.
 */
class item_last_update extends formatter {
    public const DATE = 'date';
    public const DESC = 'description';

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        return [
            self::DATE => date_field_formatter::class,
            self::DESC => string_field_formatter::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case self::DATE:
            case self::DESC:
                $getter = "get_$field";
                return $this->object->$getter();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}