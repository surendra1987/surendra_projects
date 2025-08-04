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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_approval
 */

namespace mod_approval\model\assignment\assignment_type;

use core\entity\context as context_entity;
use core\entity\tenant;
use core\orm\entity\entity;

/**
 * Context assignment type.
 */
class context extends base {

    public function __construct(int $id) {
        $this->entity = new context_entity($id);
    }

    /**
     * @inheritDoc
     */
    public static function get_label(): string {
        return get_string('model_assignment_type_context', 'mod_approval');
    }

    /**
     * @inheritDoc
     */
    public static function get_code(): int {
        return 4;
    }

    /**
     * @inheritDoc
     */
    public static function get_enum(): string {
        return 'CONTEXT';
    }

    /**
     * @inheritDoc
     */
    public static function get_sort_order(): int {
        return 40;
    }

    /**
     * @inheritDoc
     */
    public static function get_table(): string {
        return 'context';
    }

    /**
     * @inheritDoc
     */
    public static function instance(int $id): base {
        return new self($id);
    }

    /**
     * @inheritDoc
     */
    public function get_entity(): entity {
        return $this->entity;
    }

    /**
     * @inheritDoc
     */
    public function get_name(): string {
        switch ($this->entity->contextlevel) {
            case CONTEXT_SYSTEM:
                return \context_system::get_level_name();
            case CONTEXT_TENANT:
                $tenant_level = \context_tenant::get_level_name();
                $tenant = new tenant($this->entity->instanceid);
                return $tenant_level . '_' . $tenant->name;
            default:
                return 'other';
        }
    }

    /**
     * @inheritDoc
     */
    public function get_id_number(): string {
        return "context_{$this->entity->id}";
    }
}