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

use auth_ssosaml\entity\saml_log_entry;
use auth_ssosaml\exception\duplicate_idp_entity_id_exception;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\metadata;
use core\orm\query\exceptions\record_not_found_exception;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\idp
 * @group auth_ssosaml
 */
class auth_ssosaml_model_idp_test extends base_saml_testcase {
    /**
     * @return void
     * @throws coding_exception
     */
    public function test_create_idp(): void {
        $idp = idp::create(['status' => false], []);
        $certificates = $idp->certificates;
        $this->assertEquals(metadata::SOURCE_NONE, $idp->metadata->source);
        $this->assertFalse($idp->status);
        $this->assertNotEmpty($certificates);
        $this->assertNotEmpty($certificates['certificate']);
        $this->assertNotEmpty($certificates['private_key']);

        $url = "http://example.com/sso/metadata";
        curl::mock_response(file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'));
        $idp2 = idp::create(
            [
                'status' => true,
                "metadata" => [
                    "source" => metadata::SOURCE_URL,
                    "url" => $url,
                    "xml" => ""
                ],
                'logout_idp' => false
            ],
            []
        );
        $this->assertEquals($url, $idp2->metadata->url);
        $this->assertEmpty($idp2->metadata->xml);
        $this->assertTrue($idp2->status);
        $this->assertFalse($idp2->logout_idp);
        $this->assertNull($idp2->logout_url);
    }

    /**
     * @return void
     */
    public function test_update_idp(): void {
        $idp = idp::create(['status' => false], []);

        curl::mock_response(file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'));
        $url = "http://example.com/sso/metadata";
        $idp->update(['status' => true, "metadata" => ["source" => metadata::SOURCE_URL, "url" => $url]], []);

        $idp = idp::load_by_id($idp->id);
        $certificates = $idp->certificates;
        $this->assertEquals($url, $idp->metadata->url);
        $this->assertEmpty($idp->metadata->xml);
        $this->assertTrue($idp->status);
        $this->assertNotEmpty($certificates);
        $this->assertNotEmpty($certificates['certificate']);
        $this->assertNotEmpty($certificates['private_key']);
    }

    /**
     * @return void
     */
    public function test_creating_idps_with_duplicate_entity_id(): void {
        curl::mock_response(file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'));
        $url = "http://example.com/sso/metadata";

        // First IdP
        $idp_1 = idp::create(['status' => false], []);
        $idp_1->update(['status' => true, "metadata" => ["source" => metadata::SOURCE_URL, "url" => $url]], []);

        // Updating the first IdP with same entity_id works
        $idp_1->update(['status' => true, "metadata" => ["source" => metadata::SOURCE_URL, "url" => $url]], []);

        // Second IdP
        curl::mock_response(file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'));
        $url = "http://example.com/sso/metadata";
        $idp_2 = idp::create(['status' => false], []);

        // Updating the second IdP with the first IdP's entity_id fails
        $this->expectException(duplicate_idp_entity_id_exception::class);
        $this->expectExceptionMessage('There is already an IdP associated with this entity ID');
        $idp_2->update(['status' => true, "metadata" => ["source" => metadata::SOURCE_URL, "url" => $url]], []);
    }

    /**
     * @return void
     */
    public function test_delete_idp(): void {
        global $DB;
        $idp = idp::create(['status' => false], []);
        $idp->delete();

        $exists = $DB->record_exists(auth_ssosaml\entity\idp::TABLE, ['id' => $idp->id]);
        $this->assertFalse($exists);
    }

    /**
     * @return void
     */
    public function test_get_by_issuer(): void {
        $url = "http://example.com/sso/metadata";
        curl::mock_response(file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'));
        $idp = idp::create(['status' => true], []);
        $idp->update(
            [
                'status' => true,
                "metadata" => [
                    "source" => metadata::SOURCE_URL,
                    "url" => $url,
                    "xml" => "",
                ],
                'logout_idp' => false,
            ],
            []
        );

        $idp_by_issuer = idp::get_by_issuer('http://example.com/metadata');
        $this->assertEquals($idp_by_issuer->id, $idp->id);

        // disable IdP and retry
        $idp->update(['status' => false], []);
        idp::get_by_issuer('http://example.com/metadata', false);

        $this->expectException(record_not_found_exception::class);
        $this->expectExceptionMessage('Can not find data record in database');
        idp::get_by_issuer('http://example.com/metadata');
    }

    /**
     * Test that a mapping entry for the user identifier is added automatically
     *
     * @return void
     */
    public function test_user_identifier_mapping(): void {
        $idp = idp::create([
            'status' => false,
            'totara_user_id_field' => 'email',
            'idp_user_id_field' => '/email',
        ], []);

        $this->assertEquals(
            [['internal' => 'email', 'external' => '/email', 'update' => 'CREATE']],
            $idp->field_mapping_config['field_maps']
        );

        // updates to field should be ignored, as it is controlled by the user identifier field
        $idp = $idp->update([
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'email', 'external' => '/email2', 'update' => 'CREATE'],
                    ['internal' => 'username', 'external' => '/username', 'update' => 'CREATE'],
                ]
            ]
        ], []);

        $this->assertEquals(
            [
                ['internal' => 'email', 'external' => '/email', 'update' => 'CREATE'],
                ['internal' => 'username', 'external' => '/username', 'update' => 'CREATE'],
            ],
            $idp->field_mapping_config['field_maps']
        );

        $idp = $idp->update([
            'totara_user_id_field' => 'idnumber',
            'idp_user_id_field' => '/idnumber',
        ], []);

        $this->assertEquals(
            [
                ['internal' => 'idnumber', 'external' => '/idnumber', 'update' => 'CREATE'],
                ['internal' => 'email', 'external' => '/email', 'update' => 'CREATE'],
                ['internal' => 'username', 'external' => '/username', 'update' => 'CREATE'],
            ],
            $idp->field_mapping_config['field_maps']
        );

        $idp = $idp->update([
            'totara_user_id_field' => 'idnumber',
            'idp_user_id_field' => '/idnumber',
            'field_mapping_config' => [
                'field_maps' => [
                    ['internal' => 'email', 'external' => '/email2', 'update' => 'CREATE'],
                ]
            ]
        ], []);

        $this->assertEquals(
            [
                ['internal' => 'idnumber', 'external' => '/idnumber', 'update' => 'CREATE'],
                ['internal' => 'email', 'external' => '/email2', 'update' => 'CREATE'],
            ],
            $idp->field_mapping_config['field_maps']
        );
    }

    /**
     * Assert that URLs are validated on create.
     *
     * @return void
     */
    public function test_create_with_urls(): void {
        $good_url = idp::create(['logout_url' => 'https://example.com'], []);
        $bad_url = idp::create(['logout_url' => '-1'], []);

        $this->assertEquals('https://example.com', $good_url->logout_url);
        $this->assertNull($bad_url->logout_url);
    }

    /**
     * Assert that URLs are validated on update.
     *
     * @return void
     */
    public function test_update_with_urls(): void {
        $idp = $this->ssosaml_generator->create_idp();

        $idp->update(['logout_url' => 'https://example.com'], []);
        $this->assertEquals('https://example.com', $idp->logout_url);

        $idp->update(['logout_url' => '-1'], []);
        $this->assertNull($idp->logout_url);
    }

    /**
     * Assert that disabling debugging will disable logs.
     *
     * @return void
     */
    public function test_debug_clears_logs(): void {
        $idp = $this->ssosaml_generator->create_idp(['debug' => true]);
        $logger = $this->ssosaml_generator->create_db_logger($idp);
        $logger->log_request('test', \auth_ssosaml\provider\logging\contract::TYPE_LOGOUT, 'abcd');
        $count = saml_log_entry::repository()->where('idp_id', $idp->id)->count();
        $this->assertSame(1, $count);

        // Now disable debugging
        $idp->update(['debug' => false], []);

        $count = saml_log_entry::repository()->where('idp_id', $idp->id)->count();
        $this->assertSame(0, $count);
    }

    /**
     * Assert the enums for auto-link users are processed.
     * @return void
     */
    public function test_autolink_users_format(): void {
        $idp = $this->ssosaml_generator->create_idp();
        $prop = new ReflectionProperty(idp::class, 'entity');
        $prop->setAccessible(true);
        /** @var \auth_ssosaml\entity\idp $entity */
        $entity = $prop->getValue($idp);

        $test_cases = [
            idp::AUTOLINK_WITH_CONFIRMATION => 'LINK_WITH_CONFIRMATION',
            idp::AUTOLINK_NO_CONFIRMATION => 'LINK_NO_CONFIRMATION',
            999999 => 'NO_LINK',
        ];

        foreach ($test_cases as $key => $expected) {
            $entity->autolink_users = $key;
            $this->assertSame($expected, $idp->autolink_users_enum);
        }
    }
}
