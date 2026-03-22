<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

use core_mfa\data_provider\factor as factor_provider;
use core_mfa\factor;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \core_mfa\data_provider\factor
 * @group core_mfa
 */
class core_mfa_data_provider_factor_test extends testcase {
    /**
     * @return void
     */
    public function test_get_registerable_factors(): void {
        global $USER;
        set_config('mfa_plugins', 'button,totp');
        $factors = factor_provider::get_registerable_factors($USER->id);
        $this->assertCount(1, $factors);
        $factor = $factors->first();
        $this->assertInstanceOf(factor::class, $factor);
        $this->assertSame('totp', $factor->get_name());
    }

    /**
     * @return void
     */
    public function test_get_enabled_factors(): void {
        set_config('mfa_plugins', 'button,totp');
        $factors = factor_provider::get_enabled_factors();
        $this->assertCount(2, $factors);
        $this->assertInstanceOf(factor::class, $factors['button']);
        $this->assertInstanceOf(factor::class, $factors['totp']);
    }

    /**
     * @return void
     */
    public function test_get_enabled_factor(): void {
        set_config('mfa_plugins', 'totp');
        $this->assertNull(factor_provider::get_enabled_factor('button'));
        $this->assertInstanceOf(factor::class, factor_provider::get_enabled_factor('totp'));
    }
}
