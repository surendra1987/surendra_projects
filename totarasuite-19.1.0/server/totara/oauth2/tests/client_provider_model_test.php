<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package totara_oauth2
 */

use core_phpunit\testcase;
use totara_oauth2\entity\access_token;
use totara_oauth2\entity\client_provider as client_provider_entity;
use totara_oauth2\event\rotated_secret_event;
use totara_oauth2\model\client_provider;

/**
 * @group totara_oauth2
 */
class totara_oauth2_client_provider_model_test extends testcase {

    public function test_generate_unique_value(): void {
        $class = new ReflectionClass(client_provider::class);
        $method = $class->getMethod("generate_unique_value");
        $method->setAccessible(true);

        $value = $method->invoke($class,'client_id');
        self::assertNotNull($value);
        self::assertNotNull(16, strlen($value));
    }

    public function test_create(): void {
        $model = client_provider::create(
            'test',
            'xapi:write',
            FORMAT_PLAIN,
            '123',
            1,
            true,
            null,
            'totara_oauth2'
        );

        self::assertNotNull($model);
        self::assertEquals('test', $model->name);
        self::assertEquals('xapi:write', $model->scope);
        self::assertEquals(FORMAT_PLAIN, $model->description_format);
        self::assertEquals('123', $model->description);
        self::assertEquals(1, $model->internal);
        self::assertEquals(true, $model->status);
        self::assertEquals('totara_oauth2', $model->component);
    }

    /**
     * @return void
     */
    public function test_create_non_internal_by_default(): void {
        $model = client_provider::create('test', 'xapi:write', FORMAT_PLAIN);

        self::assertNotNull($model);
        self::assertEquals(0, $model->internal);
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_rotate_secret(): void {
        /** @var \totara_oauth2\testing\generator $api_generator */
        $api_generator = $this->getDataGenerator()->get_plugin_generator('totara_oauth2');
        self::setAdminUser();
        $user_id = get_admin()->id;

        $id = client_provider::create(
            'test',
            'xapi:write',
            FORMAT_PLAIN,
            '123',
            1,
            true,
            null,
            'totara_oauth2'
        )->id;
        $entity = new client_provider_entity($id);
        $entity->client_secret_updated_at = strtotime("10 September 2000");
        $entity->save();
        $model = client_provider::load_by_entity($entity);
        self::assertNotNull($model);

        // Create 3 access tokens
        $api_generator->create_access_token($model->client_id);
        $api_generator->create_access_token($model->client_id);
        $api_generator->create_access_token($model->client_id);
        self::assertEquals(3, access_token::repository()->count());

        $old_secret = $model->client_secret;
        $old_time = $model->client_secret_updated_at;
        $sink = $this->redirectEvents();

        $result = $model->rotate_secret();

        $events = $sink->get_events();
        $sink->close();
        $new_time = $model->client_secret_updated_at;

        self::assertIsString($result->client_secret);
        self::assertNotEquals($old_secret, $result->client_secret);
        self::assertNotEquals($old_time, $new_time);

        // Check that we get the expected number of events.
        $rotate_events = array_filter($events, function ($event) {
            return $event instanceof rotated_secret_event;
        });
        $this->assertCount(1, $rotate_events);
        /** @var rotated_secret_event $rotate_event */
        $rotate_event = array_pop($rotate_events);
        self::assertEquals(
            "The user $user_id rotated the client secret for oauth2 client provider $model->id with 3 access tokens",
            $rotate_event->get_description()
        );
    }
}
