<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package totara_hierarahy
 */

namespace totara_hierarchy\hook;

use totara_core\hook\base;

/**
 * This hook checks if a hierarchy item can be deleted
 */
class pre_delete_item_check extends base {

    /**
     * @param int $id
     */
    protected int $id;

    /**
     * @param string $prefix
     */
    protected string $prefix;

    /**
     * Reasons the hierarchy item can not be deleted.
     * @var array|string[]
     */
    protected array $reasons = [];

    /**
     * @param int $id
     * @param string $prefix
     */
    public function __construct(int $id, string $prefix) {
        $this->id = $id;
        $this->prefix = $prefix;
    }

    /**
     * @return int $id
     */
    public function get_id(): int {
        return $this->id;
    }

    /**
     * @return string $prefix
     */
    public function get_prefix(): string {
        return $this->prefix;
    }

    /**
     * Get reasons
     * @return array|string[]
     */
    public function get_reasons(): array {
        return $this->reasons;
    }

    /**
     * Add reason
     * @param string $reason
     * @return $this
     */
    public function add_reason(string $reason): self {
        $this->reasons[] = $reason;
        return $this;
    }
}
