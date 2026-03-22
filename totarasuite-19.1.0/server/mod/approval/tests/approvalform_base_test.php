<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Angela Kuznetsova <angela.kuznetsova@totaralearning.com>
 * @package mod_approval
 */

use core_phpunit\testcase;
use mod_approval\model\form\approvalform_base;

/**
 * @group approval_workflow
 * @coversDefaultClass mod_approval\model\form\approvalform_base
 */
class mod_approval_approvalform_base_test extends testcase {

    /**
     * @covers ::from_plugin_name
     */
    public function test_from_plugin_name(): void {
        $plugin = approvalform_base::from_plugin_name('simple');
        $this->assertInstanceOf(approvalform_simple\simple::class, $plugin);
    }

    /**
     * @covers ::enables_component
     */
    public function test_enables_component(): void {
        $this->assertFalse(approvalform_base::enables_component('core_enrol'));
    }
}
