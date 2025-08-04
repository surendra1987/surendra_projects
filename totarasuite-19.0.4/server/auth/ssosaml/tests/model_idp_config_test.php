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

use auth_ssosaml\event\certificate_regenerated;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\config;
use auth_ssosaml\model\idp\config\nameid;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\idp\config
 * @group auth_ssosaml
 */
class auth_ssosaml_model_idp_config_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_valid_sp_config(): void {
        $create_idp = idp::create(['status' => false], []);

        $config = new config($create_idp->id, [], $create_idp->certificates);

        $this->assertNotEmpty($config->certificate);
        $this->assertNotEmpty($config->private_key);
        $this->assertNotEmpty($config->passphrase);
        $this->assertFalse($config->sign_metadata);
        $this->assertFalse($config->authnrequests_signed);
        $this->assertFalse($config->wants_assertions_signed);
        $this->assertEquals(nameid::FORMAT_UNSPECIFIED, $config->nameid_format);

        $config = new config($create_idp->id, [], $create_idp->certificates, 'abcd');
        $this->assertSame('abcd', $config->passphrase);
    }

    /**
     * @return void
     */
    public function test_invalid_config(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Cannot access the config without a certificate and private key.");
        $create_idp = idp::create(['status' => false], []);
        new config($create_idp->id, [], []);
    }

    /**
     * @return void
     */
    public function test_get_entity_id(): void {
        $create_idp = idp::create(['status' => false], []);

        // Defaults to metadata_url
        $config = new config(
            $create_idp->id,
            [],
            $create_idp->certificates
        );
        $this->assertEquals($config->default_entity_id, $config->entity_id);

        // Override entity_id
        $config = new config(
            $create_idp->id,
            [
                'entity_id' => 'a_random_string'
            ],
            $create_idp->certificates
        );
        $this->assertEquals('a_random_string', $config->entity_id);
    }

    /**
     * @return void
     */
    public function test_certificate_regeneration_event(): void {
        $create_idp = idp::create(['status' => false], []);

        $sink = $this->redirectEvents();
        $create_idp->regenerate_certificates();

        $found = false;
        foreach ($sink->get_events() as $event) {
            if ($event instanceof certificate_regenerated) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'certificate regeneration event');
    }
}
