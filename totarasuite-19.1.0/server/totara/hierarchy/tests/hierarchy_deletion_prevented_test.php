<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package totara_hierarchy
 */

use core_phpunit\testcase;
use totara_hierarchy\exception\hierarchy_deletion_prevented;

class totara_hierarchy_hierarchy_deletion_prevented_test extends testcase {

    public function test_creating_exception_without_reasons_fail(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Reasons for preventing hierarchy deletion can not be empty");

        new hierarchy_deletion_prevented([]);
    }

    public function test_get_reasons(): void {
        $reasons = ["One", "Two"];
        $ex = new hierarchy_deletion_prevented($reasons);

        $this->assertEqualsCanonicalizing($reasons, $ex->get_reasons());
    }
}
