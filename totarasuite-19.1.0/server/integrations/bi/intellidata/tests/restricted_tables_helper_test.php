<?php
/**
 * This file is part of Totara TXP
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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package bi_intellidata
 */


use bi_intellidata\helpers\RestrictedTablesHelper;
use core_phpunit\testcase;

/**
 * Unit tests for the restricted tables helper class.
 * @group bi_intellidata
 */
class bi_intellidata_restricted_tables_helper_test extends testcase {

    /**
     * @return void
     */
    public function test_exact_match(): void {
        $helper = new RestrictedTablesHelper();
        $this->assertTrue($helper->isRestricted('config'));
        $this->assertTrue($helper->isRestricted('enrol_paypal'));
        $this->assertFalse($helper->isRestricted('config2'));
    }

    /**
     * @return void
     */
    public function test_wildcard_match(): void {
        $helper = new RestrictedTablesHelper();
        $this->assertTrue($helper->isRestricted('my_config'));
        $this->assertTrue($helper->isRestricted('oauth2_tokens'));
        $this->assertFalse($helper->isRestricted('justaconfig'));
    }
}
