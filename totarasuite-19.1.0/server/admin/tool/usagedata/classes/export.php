<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata;

interface export {

    public const TYPE_ARRAY = 1;
    public const TYPE_OBJECT = 2;
    public const NOTSET = '__notset__';
    /**
     * The user-facing summary to explain the exported data
     *
     * @return string the summary of the usagedata class
     */
    public function get_summary(): string;

    /**
     * The usagedata export type
     *
     * @return int either:
     * - self::TYPE_ARRAY (1)
     * - self::TYPE_OBJECT (2)
     */
    public function get_type(): int;

    /**
     * Defines the exported data from the export class
     *
     * @return array - Can be a list or associative array
     */
    public function export(): array;
}
