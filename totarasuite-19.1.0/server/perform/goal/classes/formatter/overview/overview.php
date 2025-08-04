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

use core_my\models\perform_overview\state;
use core\webapi\formatter\formatter;

/**
 * Formats a perform_goal\model\overview\overview object.
 */
class overview extends formatter {
    public const ITEMS = 'goals';
    public const DUE = 'due_soon';
    public const STATE_COUNTS = 'state_counts';
    public const TOTAL = 'total';

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        return [
            self::ITEMS => null,
            self::DUE => null,
            self::STATE_COUNTS => null,
            self::TOTAL => null
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case self::DUE:
                return $this->object->get_due_soon();

            case self::ITEMS:
                return state::all()->reduce(
                    function (array $acc, state $state): array {
                        $key = strtolower($state->name);
                        $acc[$key] = $this->object->get_items_by_state($state);
                        return $acc;
                    },
                    []
                );

            case self::STATE_COUNTS:
                return state::all()->reduce(
                    function (array $acc, state $state): array {
                        $key = strtolower($state->name);
                        $acc[$key] = $this->object->get_count_by_state($state);
                        return $acc;
                    },
                    []
                );

            case self::TOTAL:
                return $this->object->get_total();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}
