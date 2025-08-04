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
 * @package auth_ssosaml
 */

use auth_ssosaml\model\idp;
use auth_ssosaml\provider\logging\contract;
use auth_ssosaml\provider\logging\db_logger;
use auth_ssosaml\provider\logging\factory;
use auth_ssosaml\provider\logging\null_logger;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\logging\factory
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_logging_factory_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_logger(): void {
        $idp = idp::create(['status' => true], []);

        $logger = factory::get_logger($idp);
        $this->assertInstanceOf(contract::class, $logger);
        $this->assertInstanceOf(null_logger::class, $logger);

        $idp->update(['debug' => true], []);
        $logger = factory::get_logger($idp);
        $this->assertInstanceOf(contract::class, $logger);
        $this->assertInstanceOf(db_logger::class, $logger);
    }
}
