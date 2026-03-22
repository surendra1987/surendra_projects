<?php
/**
 * This file is part of Totara Core
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package container_workspace
 */

namespace container_workspace\member;

/**
 * Indicates the result of checking a workspace owner addition condition.
 */
final class evaluation_result {
    /**
     * Creates a failed result.
     *
     * @param string $code the failure code.
     * @param string $error the failure message. This could be a straight error
     *        message or a localized string.
     *
     * @return self the object.
     */
    public static function failed(string $code, string $error): self {
        return new self(false, $code, $error);
    }

    /**
     * Creates a passed result.`
     *
     * @return self the object.
     */
    public static function passed(): self {
        return new self(true, null, null);
    }

    /**
     * Default constructor.
     *
     * @param bool $is_fulfilled true if a lifecycle condition was fulfilled.
     * @param string|null $code the failure code if this is a failed result.
     * @param string|null $error the failure message if this is a failed result.
     */
    private function __construct(
        public bool $is_fulfilled,
        public readonly ?string $code,
        public readonly ?string $error

    ) {
        // EMPTY BLOCK.
    }
}
