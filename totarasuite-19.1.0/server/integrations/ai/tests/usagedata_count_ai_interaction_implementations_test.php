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

use core_ai\usagedata\count_ai_interaction_implementations;
use core_tag\ai\interaction\suggest_tags;
use core_phpunit\testcase;

/**
 * Tests interactions usage data.
 *
 * @group core_ai
 * @group core_ai_usagedata
 */
class core_ai_usagedata_count_ai_interaction_implementations_test extends testcase {
    public function test_export(): void {
        [
            'implementation_count' => $count,
            'implementation_names' => $names
        ] = (new count_ai_interaction_implementations())->export();

        // There is at least one interaction that Totara provides out of the box
        // - core_tag\ai\interaction\suggest_tags.
        self::assertTrue($count > 0, 'wrong interaction implementation count');
        self::assertStringContainsString(
            suggest_tags::get_name(),
            $names,
            'missing interaction implementation name'
        );
    }
}