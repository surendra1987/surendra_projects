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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\model\idp;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\webapi\resolver\mutation\regenerate_idp_certificates
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_mutation_regenerate_idp_certificates_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'auth_ssosaml_regenerate_idp_certificates';

    /**
     * @return void
     */
    public function test_executing_mutation(): void {
        $this->setAdminUser();
        $idp = idp::create(['status' => false], []);
        $args = [
            'input' => [
                'id' => $idp->id,
            ],
        ];

        // Capture our first certificate
        $original_certificate = $idp->certificates;
        $this->assertNotEmpty($original_certificate);

        $this->execute_graphql_operation(self::MUTATION, $args);

        // Reload the IdP
        $idp = idp::load_by_id($idp->id);
        $new_certificates = $idp->certificates;
        $this->assertNotEmpty($new_certificates);

        // Check the certificate exists but is different
        $this->assertNotSame($new_certificates, $original_certificate);
        $this->assertNotEmpty($new_certificates);
        $this->assertArrayHasKey('certificate', $new_certificates);
        $this->assertArrayHasKey('private_key', $new_certificates);
    }
}
