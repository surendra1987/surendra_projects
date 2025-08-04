<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author  Kelsey Scheurich <kelsey.scheurich@totara.com>
 * @package totara_api
 */

use core\orm\query\builder;
use core_phpunit\testcase;
use totara_api\exception\require_manage_capability_exception;
use totara_api\model\client;
use totara_oauth2\entity\access_token;
use totara_oauth2\entity\client_provider;
use totara_oauth2\testing\generator;
use totara_webapi\phpunit\webapi_phpunit_helper;

class totara_api_webapi_resolver_mutation_rotate_client_secret_test extends testcase {
    use webapi_phpunit_helper;

    private const MUTATION = 'totara_api_rotate_client_secret';

    public function test_rotate_client_secret_as_admin(): void {
        static::setAdminUser();

        $generator = generator::instance();

        $user = $this->getDataGenerator()->create_user();
        $client = client::create(
            '123',
            $user->id,
            '',
            null,
            1,
            ['create_client_provider' => true]
        );
        /** @var \totara_oauth2\model\client_provider $provider */
        $provider = $client->get_oauth2_client_providers()->first();

        $old_secret = $provider->client_secret;
        $old_time = $provider->client_secret_updated_at;

        $generator->create_access_token_from_client_provider(client_provider::repository()->find_or_fail($provider->id));

        $db = builder::get_db();
        static::assertTrue($db->record_exists(client_provider::TABLE, [
            'id' => $provider->id,
            'client_secret' => $old_secret,
            'client_secret_updated_at' => $old_time
        ]));

        static::assertTrue($db->record_exists(access_token::TABLE, ['client_provider_id' => $provider->id]));

        $id = $provider->id;
        $this->resolve_graphql_mutation(static::MUTATION, ['id' => $client->id]);
        // assert that the result is an object and has the correct ID or other attributes
        static::assertTrue($db->record_exists(client_provider::TABLE, ['id' => $id]));
        static::assertFalse($db->record_exists(client_provider::TABLE, [
            'id' => $id,
            'client_secret' => $old_secret,
            'client_secret_updated_at' => $old_time
        ]));
        static::assertFalse($db->record_exists(access_token::TABLE, ['client_provider_id' => $provider->id]));
    }

    public function test_rotate_client_provider_secret_as_system_user(): void {
        $user = static::getDataGenerator()->create_user();
        static::setUser($user);

        $generator = generator::instance();
        $provider = $generator->create_client_provider("client_id_one");

        static::expectException(require_manage_capability_exception::class);
        $this->resolve_graphql_mutation(self::MUTATION, ['id' => $provider->id]);
    }

}
