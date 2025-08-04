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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\entity\saml_log_entry;
use auth_ssosaml\model\saml_log_entry as model;
use auth_ssosaml\provider\logging\contract;
use auth_ssosaml\webapi\formatter\saml_log_entry_formatter;
use core\pagination\offset_cursor;
use GraphQL\Executor\ExecutionResult;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @covers \auth_ssosaml\webapi\resolver\query\saml_log
 * @covers \auth_ssosaml\webapi\resolver\type\saml_log_entry
 * @covers \auth_ssosaml\webapi\formatter\saml_log_entry_formatter
 * @group auth_ssosaml
 */
class auth_ssosaml_webapi_resolver_query_saml_log_test extends base_saml_testcase {
    use webapi_phpunit_helper;

    private const QUERY = 'auth_ssosaml_saml_log';

    /**
     * Assert we can query for logs
     *
     * @return void
     */
    public function test_query(): void {
        $this->setAdminUser();

        $idp1 = $this->ssosaml_generator->create_idp();
        $logger1 = $this->ssosaml_generator->create_db_logger($idp1);
        $idp2 = $this->ssosaml_generator->create_idp();
        $logger2 = $this->ssosaml_generator->create_db_logger($idp2);

        $args = ['input' => ['idp_id' => $idp1->id]];
        $result = $this->execute_graphql_operation(self::QUERY, $args);
        $this->assert_query_successful($result);
        $this->assertEmpty($result->data['entries']['items']);
        $this->assertEmpty($result->data['entries']['total']);

        // Create a log for idp 1 & 2
        $logger1->log_request('abcd', $logger1::TYPE_LOGIN, 'regular');
        $logger2->log_request('efga', $logger2::TYPE_IDP_LOGIN, '99 red balloons');

        // Create another log for 1 that has another session id
        $log_id = $logger1->log_request('a1264854', $logger1::TYPE_LOGIN, 'different session');
        saml_log_entry::repository()->where('id', $log_id)->update(['session_id' => 'abcd']);

        // Query again
        $result = $this->execute_graphql_operation(self::QUERY, $args);
        $this->assert_query_successful($result);
        $this->assertEquals(2, $result->data['entries']['total']);
        $this->assertCount(2, $result->data['entries']['items']);

        $log_1 = $result->data['entries']['items'][1];
        $this->assertEquals('regular', $log_1['content_request']);
        $log_2 = $result->data['entries']['items'][0];
        $this->assertEquals('different session', $log_2['content_request']);

        // Pagination check
        $cursor = offset_cursor::create(['page' => 2, 'limit' => 1]);
        $args = [
            'input' => [
                'idp_id' => $idp1->id,
                'pagination' => [
                    'cursor' => $cursor->encode(),
                ]
            ]
        ];
        $result = $this->execute_graphql_operation(self::QUERY, $args);
        $this->assert_query_successful($result);
        $this->assertEquals(2, $result->data['entries']['total']);
        $this->assertCount(1, $result->data['entries']['items']);

        $log_1 = $result->data['entries']['items'][0];
        $this->assertEquals('regular', $log_1['content_request']);

        // Limit to sessions
        $args = ['input' => ['idp_id' => $idp1->id, 'active_session' => true]];
        $result = $this->execute_graphql_operation(self::QUERY, $args);
        $this->assert_query_successful($result);
        $this->assertEquals(1, $result->data['entries']['total']);
        $this->assertCount(1, $result->data['entries']['items']);

        $log_1 = $result->data['entries']['items'][0];
        $this->assertEquals('regular', $log_1['content_request']);
    }

    /**
     * @param ExecutionResult $result
     * @return void
     */
    private function assert_query_successful(ExecutionResult $result): void {
        $this->assertIsArray($result->data);
        $this->assertArrayHasKey('entries', $result->data);
        $this->assertArrayHasKey('items', $result->data['entries']);
        $this->assertArrayHasKey('total', $result->data['entries']);
    }

    /**
     * @return void
     */
    public function test_query_requires_site_config_capability() {
        $this->expectException(required_capability_exception::class);
        $this->resolve_graphql_query(self::QUERY);
    }

    /**
     * Assert the custom transformations in the formatter are handled.
     *
     * @return void
     */
    public function test_formatter(): void {
        $idp1 = $this->ssosaml_generator->create_idp();
        $logger1 = $this->ssosaml_generator->create_db_logger($idp1);
        $id = $logger1->log_request('abcd', $logger1::TYPE_LOGIN, 'regular');
        $source = model::load_by_id($id);

        // Override accessing the entity so we can mock values in
        $property = new ReflectionProperty(model::class, 'entity');
        $property->setAccessible(true);
        /** @var saml_log_entry $entity */
        $entity = $property->getValue($source);

        $formatter = new saml_log_entry_formatter($source, $source->get_context());

        $tests = [
            'type' => [
                contract::TYPE_LOGIN => get_string('log_login', 'auth_ssosaml'),
                contract::TYPE_IDP_LOGIN => get_string('log_idp_login', 'auth_ssosaml'),
                contract::TYPE_LOGOUT => get_string('log_logout', 'auth_ssosaml'),
                contract::TYPE_IDP_LOGOUT => get_string('log_idp_logout', 'auth_ssosaml'),
                'idk' => 'idk',
            ],
            'status' => [
                contract::STATUS_SUCCESS => 'SUCCESS',
                contract::STATUS_ERROR => 'ERROR',
                contract::STATUS_INCOMPLETE => 'INCOMPLETE',
            ],
        ];

        foreach ($tests as $property => $test_cases) {
            foreach ($test_cases as $property_value => $expected) {
                $entity->$property = $property_value;
                $result = $formatter->format($property);
                $this->assertSame($expected, $result);
            }
        }
    }
}
