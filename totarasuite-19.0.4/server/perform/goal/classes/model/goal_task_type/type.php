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

namespace perform_goal\model\goal_task_type;

use cache;
use coding_exception;
use core_component;
use perform_goal\model\goal_task_resource;

/**
 * Base type for goal task resource type.
 */
abstract class type {
    /**
     * @var ?int the id of the associated resource.
     */
    private ?int $resource_id;

    /**
     * Creates an instance of a type given its typecode.
     *
     * @param int $code target resource code.
     * @param ?int $resource_id associated resource id if any.
     *
     * @return self the instance.
     *
     * @throws coding_exception if the target type code does not correspond to
     *         a known type.
     */
    final public static function by_code(
        int $code, ?int $resource_id = null
    ): self {
        $cache = cache::make('perform_goal', 'goal_task_resource_type');

        $class = $cache->get($code);
        if ($class !== false) {
            return new $class($resource_id);
        }

        // It is a bad idea to force other code to adhere a specific namespace
        // convention if they want to provide task resource types but that is
        // the way get_namespace_classes() works.
        $classes = core_component::get_namespace_classes(
            'model\goal_task_type', self::class
        );

        $entries = [];
        $found = null;

        foreach ($classes as $class) {
            $type = new $class($resource_id);
            $type_code = $type->code();
            $entries[$type_code] = $class;

            if ($code === $type_code) {
                $found = $type;
            }
        }

        $cache->set_many($entries);

        if (!$found) {
            throw new coding_exception("Unknown goal task resource code: $code");
        }

        return $found;
    }

    /**
     * Default constructor.
     *
     * Note the following:
     * - This is final since self::by_code() assumes a subtype has a constructor
     *   with this signature.
     * - This is protected because if you really want to create subtypes without
     *   using self::by_code(), you can create a virtual static method in the
     *   subtype definition that calls this constructor.
     *
     * @param ?int $resource_id associated resource record id if any.
     *
     * @return self the instance.
     */
    final protected function __construct(?int $resource_id = null) {
        $this->resource_id = $resource_id;
    }

    /**
     * Returns the code for this type.
     *
     * @return int the code.
     */
    abstract public function code(): int;

    /**
     * Returns any custom data for this resource.
     *
     * @param goal_task_resource $resource the parent resource model.
     *
     * @return array<string,mixed> custom data as a set of key value pairs.
     */
    abstract public function custom_data(goal_task_resource $resource): array;

    /**
     * Returns a customized type name. By default this is just the class name;
     * override to provide something else.
     *
     * @return string the name.
     */
    public function name(): string {
        return static::class;
    }

    /**
     * Returns the actual resource associated with this type.
     *
     * @return mixed the resource or null if it was deleted.
     */
    abstract public function resource();

    /**
     * Indicates whether the associated resource (eg course) exists.
     *
     * If this returns false and resource_id is not null, it means the artifact
     * associated with this resource originally existed but has now been deleted.
     *
     * @return bool if a resource exists.
     */
    abstract public function resource_exists(): bool;

    /**
     * Returns the associated resource id.
     *
     * @return ?int the resource id or null if the resource was deleted.
     */
    final public function resource_id(): ?int {
        return $this->resource_id;
    }

    /**
     * Checks if the currently logged on user is allowed to see the associated
     * resource details.
     *
     * @return bool true if the user is allowed to see.
     */
    abstract public function authorized(): bool;
}
