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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\data_provider\idp;
use auth_ssosaml\model\idp as idp_model;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\data_provider\idp
 * @group auth_ssosaml
 */
class auth_ssosaml_data_provider_idp_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_all(): void {
        // On installation no IdPs are present.
        $all_idps = idp::get_all();
        $this->assertCount(0, $all_idps);

        idp_model::create(['status' => false], []);
        idp_model::create(['status' => true], []);
        $all_idps = idp::get_all();
        $this->assertCount(2, $all_idps);
        foreach ($all_idps as $idp) {
            $this->assertInstanceOf(idp_model::class, $idp);
        }
    }

    /**
     * @return void
     */
    public function test_get_enabled(): void {
        idp_model::create(['status' => false], []);
        idp_model::create(['status' => true], []);
        $all_idps = idp::get_enabled();
        $idp_status = [];
        foreach ($all_idps as $idp) {
            $idp_status[] = $idp->status;
        }
        $this->assertCount(1, $all_idps);
        $this->assertContains(true, $idp_status);
    }
}
