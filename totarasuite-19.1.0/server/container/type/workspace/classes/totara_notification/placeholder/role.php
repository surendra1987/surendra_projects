<?php
/**
 * This file is part of Totara Engage
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

namespace container_workspace\totara_notification\placeholder;

use coding_exception;
use core\entity\role as role_instance;
use totara_notification\placeholder\abstraction\placeholder_instance_cache;
use totara_notification\placeholder\abstraction\single_emptiable_placeholder;
use totara_notification\placeholder\option;

final class role extends single_emptiable_placeholder {
    use placeholder_instance_cache;

    /**
     * @var role_instance|null
     */
    protected $role;

    /**
     * @param role_instance|null $role
     */
    public function __construct(?role_instance $role) {
        $this->role = $role;
    }

    /**
     * Create role instance from given role id
     *
     * @param int $role_id
     * @return self
     * @throws coding_exception
     */
    public static function from_id(int $role_id): self {
        $instance = self::get_cached_instance($role_id);
        if (!$instance) {
            try {
                $role = new role_instance($role_id);
            } catch (\dml_missing_record_exception $ex) {
                $role = null;
            }
            $instance = new static($role);
            self::add_instance_to_cache($role_id, $instance);
        }

        return $instance;
    }

    /**
     * @return option[]
     */
    public static function get_options(): array {
        return [
            option::create(
                'full_name',
                get_string('placeholder_workspace_role_full_name', 'container_workspace')
            ),
        ];
    }

    public function do_get(string $key): string {
        if ($this->role === null) {
            throw new coding_exception("The workspace role is empty");
        }

        if ($key === 'full_name') {
            return role_get_name($this->role->to_record());
        }

        throw new coding_exception("Invalid key '{$key}'");
    }

    /**
     * @inheritDoc
     */
    protected function is_available(string $key): bool {
        return $this->role !== null;
    }
}
