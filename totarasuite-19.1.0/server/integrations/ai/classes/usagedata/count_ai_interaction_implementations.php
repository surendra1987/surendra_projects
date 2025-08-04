<?php
/*
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package core_ai
 */

namespace core_ai\usagedata;

use core_ai\subsystem;
use tool_usagedata\export;

/**
 * Analytics for AI interactions.
 */
class count_ai_interaction_implementations implements export {
    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('usagedata_count_ai_interaction_implementations', 'ai');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     *
     * Result looks like this:
     * [
     *    // Total interaction implementation count.
     *    'implementation_count' => 11,
     *
     *    // Comma separated list of interaction implementation names.
     *    'implementation_names' => 'AAA,BBB,...,ZZZ'
     * ]
     */
    public function export(): array {
        $names = array_map(
            fn(string $class): string => $class::get_name(),
            subsystem::get_interactions()
        );

        return [
            'implementation_count' => count($names),
            'implementation_names' => implode(',', $names)
        ];
    }
}