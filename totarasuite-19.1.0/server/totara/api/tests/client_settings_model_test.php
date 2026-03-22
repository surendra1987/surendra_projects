<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author Scott Davies <scott.davies@totaralearning.com>
 * @package totara_api
 */

use core_phpunit\testcase;
use totara_api\exception\update_client_settings_exception;
use totara_api\model\client_settings as client_settings_model;
use totara_api\entity\client as entity_client;
use totara_api\entity\client_settings as entity_client_settings;
use totara_api\testing\generator;

/**
 * Unit tests for the totara_api\model\client_settings model class.
 * @group totara_api
 */
class totara_api_client_settings_model_test extends testcase {
    /** @var generator - client_settings */
    protected $generator;

    /**
     * Get data generator
     * @return generator
     */
    public static function get_data_generator(): generator {
        return generator::instance();
    }

    /**
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->generator = self::get_data_generator();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        parent::tearDown();
    }

    /**
     * Data provider for correct ip addresses.
     *
     * @return array
     */
    public static function correct_ip_address_list_data_provider(): array {
        return [
            'shortest valid ip' => ["0"],
            'valid ip' => ["127.0.0.1"],
            'multiple valid ips' => ["127.0.0.1\n127.0.100.300\n400.746.22.100"],
            'valid CIDR notation' => ["203.0.113.128/29"],
            'multiple valid CIDR notation' => ["203.0.113.128/29\n300.455.3.222/10\n111.30.200.20/10"],
            'valid ip-range formatted ip' => ["231.3.56.0-900", '231.3.56.0-900'],
            'multiple valid ip-range formatted addresses' => ["231.3.56.0-900\n31.300.22.200-300\n500.22.100.200-900", "231.3.56.0-900\n31.300.22.200-300\n500.22.100.200-900"],
            'valid ips in a range of different ip formats' => ["231.3.56.10-20\n231.300.56.220\n291.5.50.60/20\n291.5.60.400-900\n100\n100.200.300.400", "231.3.56.10-20\n231.300.56.220\n291.5.50.60/20\n291.5.60.400-900\n100\n100.200.300.400"],
        ];
    }

    /**
     * Data provider for incorrect ip address tests.
     *
     * @return array
     */
    public static function incorrect_ip_address_list_data_provider(): array {
        return [
            'invalid special characters as an ip' => ["1.*.@.100", '1.*.@.100'],
            'word is invalid as an ip' => ["test", 'test'],
            'incompleted invalid IP' => ["111.111.", '111.111.'],
            'single invalid IP' => ["1111.111.111.111", '1111.111.111.111'],
            'list of ips, with one invalid' => ["0.0.0.0\n200.300.4000.400", '200.300.4000.400'],
            'list of ips, with two invalid' => ["255.255.255.255\n200.400.600.900\n10.20.3000.40\n200000","10.20.3000.40,200000"],
            'list of ips, with three invalid' => ["255.255.255.255\n200.400.600.900\n10.20.3000.40\n200000\n100.100.100.100.100","10.20.3000.40,200000,100.100.100.100.100"],
            'invalid CIDR format ip' => ["203.0.113.128/290", '203.0.113.128/290'],
            'invalid ip range formatted ip' => ["231.3.56.0-9000", '231.3.56.0-9000'],
            'invalid ip in a range of different ip formats' => ["231.3.56.10-20\n231.300.56.220\n291.5.60/20\n291.5.60.400-900\n100\n100.200.300.400.500", "100.200.300.400.500"],
        ];
    }

    /**
     * @param $ips
     * @return void
     * @dataProvider correct_ip_address_list_data_provider
     */
    public function test_validate_allowed_ip_list__with_correct_ip_list($ips) {
        $this->callInternalMethod(client_settings_model::class,'validate_allowed_ip_list', [$ips]);
        //assert this test passes when an exception is not thrown
        $this->assertTrue(true);
    }

    /**
     * @param $ips
     * @param $failed_ips
     * @return void
      * @dataProvider incorrect_ip_address_list_data_provider
     */
    public function test_validate_allowed_ip_list__with_incorrect_ip_list($ips, $failed_ips) {
        $this->expectException(update_client_settings_exception::class);
        $this->expectExceptionMessage("Invalid IP address: $failed_ips");
        $this->callInternalMethod(client_settings_model::class,'validate_allowed_ip_list', [$ips]);
    }

    /**
     * @param $ips
     * @return void
     * @dataProvider correct_ip_address_list_data_provider
     */
    public function test_put_method__with_correct_ip_list($ips) {

        $entity_client = new entity_client();
        $entity_client->name = 'something else';
        $entity_client->status = 0;
        $entity_client->save();

        $client_id = $entity_client->id;
        $args = ['client_id' => $client_id,
            'allowed_ip_list' => $ips
        ];

        $this->callInternalMethod(client_settings_model::class,'validate_allowed_ip_list', [$ips]);
        client_settings_model::put($args);
        //assert this test passes when an exception is not thrown
        $this->assertTrue(true);

    }

    /**
     * @param $ips
     * @param $failed_ips
     * @return void
     * @dataProvider incorrect_ip_address_list_data_provider
     */
    public function test_put_method__with_incorrect_ip_list($ips, $failed_ips) {
        $this->expectException(update_client_settings_exception::class);
        $this->expectExceptionMessage("Invalid IP address: $failed_ips");

        $args = [
            'allowed_ip_list' => $ips
        ];
        client_settings_model::put($args);

    }

    /**
     * @return void
     * @throws Throwable
     */
    public function test_put_create_new_client_settings(): void {
        $this->setAdminUser();

        // Create an api client without a related api_client_settings.
        $entity_client = new entity_client();
        $entity_client->name = 'something else';
        $entity_client->status = 0;
        $entity_client->save();

        $client_id = $entity_client->id;
        $new_default_token_expiry_time = time();
        $client_rate_limit = 5;

        // Create a new api client_settings
        $args = ['client_id' => $client_id,
            'client_rate_limit' => $client_rate_limit,
            'default_token_expiry_time' =>$new_default_token_expiry_time
        ];
        $model_client_settings_updated = client_settings_model::put($args);

        self::assertNotNull($model_client_settings_updated);
        self::assertEquals($new_default_token_expiry_time, $model_client_settings_updated->default_token_expiry_time);
        self::assertEquals($client_rate_limit, $model_client_settings_updated->client_rate_limit);
        // new client id
        self::assertEquals($model_client_settings_updated->client_id, $model_client_settings_updated->client_id);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function test_put_update_existing_client_settings(): void {
        $this->setAdminUser();
        // This will be our existing client_settings_model that we want to update.
        $model_client_settings = $this->generator->create_client_settings_model();

        $new_default_token_expiry_time = time();
        $client_rate_limit = 6;
        $allowed_ip_list = '192.168.1.1';

        // Update an existing api client_settings. Try a model update call & check the model returned.
        $args = ['client_id' => $model_client_settings->client_id,
            'client_rate_limit' => $client_rate_limit,
            'default_token_expiry_time' =>$new_default_token_expiry_time,
            'allowed_ip_list' => $allowed_ip_list
        ];
        $model_client_settings_updated = client_settings_model::put($args);

        self::assertNotNull($model_client_settings_updated);
        self::assertEquals($new_default_token_expiry_time, $model_client_settings_updated->default_token_expiry_time);
        self::assertEquals($client_rate_limit, $model_client_settings_updated->client_rate_limit);
        self::assertEquals($allowed_ip_list, $model_client_settings_updated->allowed_ip_list);
        // but keep the same client id
        self::assertEquals($model_client_settings_updated->client_id, $model_client_settings_updated->client_id);

        // Let's check again with the result of a repo call.
        $entity_fetched = entity_client_settings::repository()
             ->find_or_fail($model_client_settings_updated->id);

        self::assertNotNull($entity_fetched);
        self::assertEquals($new_default_token_expiry_time, $entity_fetched->default_token_expiry_time);
        self::assertEquals($client_rate_limit, $entity_fetched->client_rate_limit);
        self::assertEquals($allowed_ip_list, $entity_fetched->allowed_ip_list);
        // but keep the same client id
        self::assertEquals($model_client_settings_updated->client_id, $entity_fetched->client_id);
    }

    /**
     * @return void
     */
    public function test_find_by_client_id(): void {
        $client_settings_model = $this->generator->create_client_settings_model();

        $entity_fetched = entity_client_settings::repository()->find_by_client_id($client_settings_model->client_id);

        $this->assertEquals($client_settings_model->client_id, $entity_fetched->client_id);
        $this->assertEquals($client_settings_model->id, $entity_fetched->id);
    }

    /**
     * @return void
     */
    public function test_update_settings_with_client_rate_limit(): void {
        $client_settings_model = $this->generator->create_client_settings_model(['client_rate_limit' => 10]);

        $client_rate_limit = $this->generator->create_client_rate_limit(
            [
                'client_id' => $client_settings_model->client_id,
                'current_limit' => $client_settings_model->client_rate_limit
            ]
        );
        self::assertEquals(10, $client_rate_limit->current_limit);

        $args = ['client_rate_limit' => 100];
        $client_settings_model->update($args);
        $client_rate_limit = \totara_api\model\client_rate_limit::load_by_id($client_rate_limit->id);
        self::assertEquals(100, $client_rate_limit->current_limit);
    }

}
