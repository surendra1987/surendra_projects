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
 * @package core_my
 */

namespace core_my\models\perform_overview;

use coding_exception;
use core\collection;

/**
 * Convenience enum to handle perform overview state.
 *
 * @property-read string $name
 * @property-read int $value
 */
final class state {
    // Allowed enums.
    private const NOT_STARTED = 1;
    private const NOT_PROGRESSED = 2;
    private const PROGRESSED = 3;
    private const ACHIEVED = 4;

    private const NAMES = [
        self::NOT_STARTED => 'NOT_STARTED',
        self::NOT_PROGRESSED => 'NOT_PROGRESSED',
        self::PROGRESSED => 'PROGRESSED',
        self::ACHIEVED => 'ACHIEVED'
    ];

    /**
     * @var string state enum name. Essentially the constant names above.
     */
    private string $name;

    /**
     * @var int state enum value. One of the values above.
     */
    private int $value;

    /**
     * Get all allowed state enums.
     *
     * @return collection<self> state enums in ascending order.
     */
    public static function all(): collection {
        return collection::new(
            [
                self::not_started(),
                self::not_progressed(),
                self::progressed(),
                self::achieved()
            ]
        );
    }

    /**
     * Get all state enum names.
     *
     * @return collection<string> state enum names in ascending order.
     */
    public static function names(): collection {
        return self::all()->map(fn (self $enum): string => $enum->name);
    }

    /**
     * Get all state enum values.
     *
     * @return collection<int> state enum values in ascending order.
     */
    public static function values(): collection {
        return self::all()->map(fn (self $enum): int => $enum->value);
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
        $uppercase = strtoupper(trim($name));
        $value = array_search($uppercase, self::NAMES);

        if ($value === false) {
            throw new coding_exception(
                sprintf("Invalid %s enum name: '%s'", self::class, $name)
            );
        }

        return new self($value, $uppercase);
    }

    /**
     * Returns the enum with the given value.
     *
     * @param int $value value to look up.
     *
     * @return self the relevant enum.
     *
     * @throws coding_exception if the value is invalid
     */
    public static function from_value(int $value): self {
        $name = self::NAMES[$value] ?? null;
        if (!$name) {
            throw new coding_exception(
                sprintf("Invalid %s enum value: '%s'", self::class, $value)
            );
        }

        return new self($value, $name);
    }

    /**
     * Returns the "not started" enum.
     *
     * @return self the enum.
     */
    public static function not_started(): self {
        return self::from_value(self::NOT_STARTED);
    }

    /**
     * Returns the "not progressed" enum.
     *
     * @return self the enum.
     */
    public static function not_progressed(): self {
        return self::from_value(self::NOT_PROGRESSED);
    }

    /**
     * Returns the "progressed" enum.
     *
     * @return self the enum.
     */
    public static function progressed(): self {
        return self::from_value(self::PROGRESSED);
    }

    /**
     * Returns the "achieved" enum.
     *
     * @return self the enum.
     */
    public static function achieved(): self {
        return self::from_value(self::ACHIEVED);
    }

    /**
     * Default constructor.
     *
     * @param int $value enum value.
     */
    private function __construct(int $value, string $name) {
        $this->value = $value;
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
        $fields = ['name', 'value'];
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
     *     case state::not_started(): ...
     *     case state::not_progressed(): ...
     *     ...
     *   }
     * or
     *   switch (true) {
     *     case state::not_started() === $state: ...
     *     case state::not_progressed() === $state: ...
     *     ...
     *   }
     *
     * @param self $status status to compare against.
     *
     * @return bool true if the given status is equal to this one.
     */
    public function is_equal_to(self $status): bool {
        return $status->value === $this->value;
    }
}
