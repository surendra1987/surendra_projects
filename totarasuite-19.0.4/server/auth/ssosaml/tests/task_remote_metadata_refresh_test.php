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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\task\remote_metadata_refresh;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\task\remote_metadata_refresh
 * @group auth_ssosaml
 */
class auth_ssosaml_task_remote_metadata_refresh_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_name(): void {
        $obj = new remote_metadata_refresh();
        $this->assertSame('Metadata refresh', $obj->get_name());
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_plugin_enabled_execute(): void {
        $xml_1 = file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml');
        $xml_2 = file_get_contents(__DIR__ . '/fixtures/idp/sample_idp_2.xml');

        //Test source url
        \curl::mock_response($xml_1);
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => ["source" => metadata::SOURCE_URL, "url" => 'http://example.com/sso/metadata'],
                'logout_idp' => false
            ],
            []
        );

        // Test xml blob
        $idp2 = idp::create(
            [
                'status' => true,
                "metadata" => ["source" => metadata::SOURCE_XML, "xml" => $xml_1],
                'logout_idp' => false
            ],
            []
        );

        //When status is disabled
        \curl::mock_response($xml_1);
        $idp3 = idp::create(
            [
                'status' => false,
                "metadata" => ["source" => metadata::SOURCE_URL, "url" => 'http://example2.com/sso/metadata'],
                'logout_idp' => false
            ],
            []
        );

        \curl::mock_response($xml_2);
        $obj = new remote_metadata_refresh();
        $obj->execute();

        $idp = idp::load_by_id($idp->id);
        $this->assertTrue($idp->status);
        $this->assertSame($xml_2, $idp->metadata->remote_xml);
        $this->assertSame('http://example.com/sso/metadata', $idp->metadata->url);

        $idp2 = idp::load_by_id($idp2->id);
        $this->assertSame($xml_1, $idp2->metadata->xml);
        $this->assertSame(metadata::SOURCE_XML, $idp2->metadata->source);
        $this->assertEmpty($idp2->metadata->url);
        $this->assertEmpty($idp2->metadata->remote_xml);

        $idp3 = idp::load_by_id($idp3->id);
        $this->assertFalse($idp3->status);
        $this->assertSame($xml_1, $idp3->metadata->remote_xml);
        $this->assertSame('http://example2.com/sso/metadata', $idp3->metadata->url);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_plugin_disabled() {
        $this->ssosaml_generator->disable_plugin();

        $xml_1 = file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml');

        //Test source url
        \curl::mock_response($xml_1);
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => ["source" => metadata::SOURCE_URL, "url" => 'http://example.com/sso/metadata'],
                'logout_idp' => false
            ],
            []
        );

        $obj = new remote_metadata_refresh();
        $obj->execute();
        $idp = idp::load_by_id($idp->id);
        $this->assertSame($xml_1, $idp->metadata->remote_xml);
        $this->assertSame(metadata::SOURCE_URL, $idp->metadata->source);
    }
}
