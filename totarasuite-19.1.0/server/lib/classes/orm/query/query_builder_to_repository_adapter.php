<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package core
 */

namespace core\orm\query;

use coding_exception;
use core\orm\entity\entity;
use core\orm\entity\repository;

defined('MOODLE_INTERNAL') || die();

/**
 * The `core\orm\query\query_builder_to_repository_adapter` enables the use of a query builder in contexts where only a repository is allowed.
 *
 * All entity-related functionality has been overridden and will throw `coding_exception` errors if invoked.
 *
 */
class query_builder_to_repository_adapter extends repository {

    /**
     * The `core\orm\query\query_builder_to_repository_adapter` class does not support `map_to`
     * as it relies on an entity to provide its functionality
     *
     * @throws coding_exception Calling this method will cause a coding exception to be thrown
     */
    public function map_to($record, bool $validate_attributes = false) {
        throw new coding_exception("Method `core\\orm\\query\\query_builder_to_repository_adapter::map_to()` is unavailable.");
    }

    /**
     * The `core\orm\query\query_builder_to_repository_adapter` class does not support `save_entity`
     * as it relies on an entity to provide its functionality
     *
     * @throws coding_exception Calling this method will cause a coding exception to be thrown
     */
    public function save_entity(entity $entity): entity {
        throw new coding_exception("Method `core\\orm\\query\\query_builder_to_repository_adapter::save_entity()` is unavailable.");
    }

    /**
     * The `core\orm\query\query_builder_to_repository_adapter` class does not support `create_entity`
     * as it relies on an entity to provide its functionality
     *
     * @throws coding_exception Calling this method will cause a coding exception to be thrown
     */
    public function create_entity(entity $entity): entity {
        throw new coding_exception("Method `core\\orm\\query\\query_builder_to_repository_adapter::create_entity()` is unavailable.");
    }

    /**
     * The `core\orm\query\query_builder_to_repository_adapter` class does not support `update_entity`
     * as it relies on an entity to provide its functionality
     *
     * @throws coding_exception Calling this method will cause a coding exception to be thrown
     */
    public function update_entity(entity $entity): entity {
        throw new coding_exception("Method `core\\orm\\query\\query_builder_to_repository_adapter::update_entity()` is unavailable.");
    }

    /**
     * The `core\orm\query\query_builder_to_repository_adapter` class does not support `delete_entity`
     * as it relies on an entity to provide its functionality
     *
     * @throws coding_exception Calling this method will cause a coding exception to be thrown
     */
    public function delete_entity(entity $entity): entity {
        throw new coding_exception("Method `core\\orm\\query\\query_builder_to_repository_adapter::delete_entity()` is unavailable.");
    }
}
