<?php
/*
 * This file is part of Totara Learn
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
 * @package mod_perform
 */

namespace mod_perform\models;

use coding_exception;
use core\collection;

/**
 * Convenience enum to handle perform activity availability state.
 *
 * @property-read string $name
 */
final class activity_availability {
    // Allowed enums.
    private const OPEN = 'OPEN';
    private const CLOSE = 'CLOSED';

    /**
     * @var string progress enum name. Essentially the constant names above.
     */
    private string $name;

    /**
     * Get all allowed progress enums.
     *
     * @return collection<self> progress enums in ascending order.
     */
    public static function all(): collection {
        return collection::new(
            [
                self::open(),
                self::closed()
            ]
        );
    }

    /**
     * Get all progress enum names.
     *
     * @return collection<string> state enum names in ascending order.
     */
    public static function names(): collection {
        return self::all()->map(fn (self $enum): string => $enum->name);
    }

    /**
     * Returns the enum with the given name.
     *
     * @param string $name to look up.
     *
     * @return self the relevant enum.
     *
     * @throws coding_exception if the name is invalid
     */
    public static function from_name(string $name): self {
        switch (strtoupper(trim($name))) {
            case self::OPEN:
                return self::open();

            case self::CLOSE:
                return self::closed();

            default:
                throw new coding_exception(
                    sprintf("Invalid %s enum name: '%s'", self::class, $name)
                );
        }
    }

    /**
     * Returns the "open" enum.
     *
     * @return self the enum.
     */
    public static function open(): self {
        return new self(self::OPEN);
    }

    /**
     * Returns the "closed" enum.
     *
     * @return self the enum.
     */
    public static function closed(): self {
        return new self(self::CLOSE);
    }

    /**
     * Default constructor.
     *
     * @param int $value enum value.
     */
    private function __construct(string $name) {
        $this->name = $name;
    }

    /**
     * Magic attribute getter.
     *
     * @param string $field field whose value is to be returned.
     *
     * @return mixed the field value.
     *
     * @throws coding_exception if the field name is unknown.
     */
    public function __get(string $field) {
        $fields = ['name'];
        if (in_array($field, $fields)) {
            return $this->$field;
        }

        throw new coding_exception(
            'Unknown ' . self::class . " attribute: $field"
        );
    }

    /**
     * Indicates if the given status is equal to this status. Mainly for use in
     * switch() blocks because these do not work:
     *   switch ($state) {
     *     case activity_progress::complete(): ...
     *     case activity_progress::incomplete(): ...
     *     ...
     *   }
     * or
     *   switch (true) {
     *     case activity_progress::complete() === $progress: ...
     *     case activity_progress::incomplete() === $progress: ...
     *     ...
     *   }
     *
     * @param self $progress progress to compare against.
     *
     * @return bool true if the given progress is equal to this one.
     */
    public function is_equal_to(self $progress): bool {
        return $progress->name === $this->name;
    }
}
