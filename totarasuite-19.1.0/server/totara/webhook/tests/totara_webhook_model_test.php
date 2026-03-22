<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

use core_phpunit\testcase;
use totara_webapi\fixtures\mock_webhook_auth;
use totara_webhook\auth\webhook_hmac_auth;
use totara_webhook\entity\totara_webhook as totara_webhook_entity;
use totara_webhook\data_provider\totara_webhook as totara_webhook_provider;
use totara_webhook\model\totara_webhook;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/fixtures/mock_webhook_auth.php';

/**
 * @group totara_webhook
 */
class totara_webhook_totara_webhook_model_test extends testcase {

    public function test_create_model(): void {
        $model = totara_webhook::create(
            'abc',
            'abc'
        );
        $this->assertInstanceOf(totara_webhook::class, $model);

        $this->assertEquals('abc', $model->name);
        $this->assertEquals('abc', $model->endpoint);
        $this->assertEquals(webhook_hmac_auth::class, $model->auth_class);

        $entity = new totara_webhook_entity();
        $entity->name = 'abc';
        $entity->endpoint = 'abc';
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();
        $model2 = totara_webhook::load_by_entity($entity);

        $this->assertInstanceOf(totara_webhook::class, $model2);

        $this->assertEquals('abc', $model2->name);
        $this->assertEquals('abc', $model2->endpoint);
    }

    public function test_delete_model(): void {
        $generator = totara_webhook_generator::instance();
        $model = $generator->create_totara_webhook();
        $model2 = $generator->create_totara_webhook();

        $provider = totara_webhook_provider::create();
        self::assertCount(2, $provider->fetch());

        $deleted_id = $model->id;
        $model->delete();
        // One model should have been removed, the other should still be there.
        $new_model2 = totara_webhook::load_by_id($model2->id);

        self::assertEquals($model2->id, $new_model2->id);

        // An exception should be thrown because the model was deleted.
        self::expectException(\dml_exception::class);
        totara_webhook::load_by_id($deleted_id);
    }

    public function test_update_model(): void {
        $generator = totara_webhook_generator::instance();
        $model = $generator->create_totara_webhook();
        $model2 = $generator->create_totara_webhook();

        $model->update(
            'def',
            'def',
            true,
            true,
            null,
            mock_webhook_auth::class
        );

        // Updated fields have changed in the existing object.
        self::assertEquals('def', $model->name);
        self::assertEquals('def', $model->endpoint);
        self::assertTrue($model->get_status());
        self::assertTrue($model->get_immediate());
        self::assertSame(mock_webhook_auth::class, $model->auth_class);
        self::assertSame(mock_webhook_auth::$generated_config, $model->auth_config);

        // An unmodified object should not have changed.
        self::assertEquals('abc', $model2->name);
        self::assertEquals('abc', $model2->endpoint);
        self::assertFalse($model2->get_immediate());
        self::assertSame(webhook_hmac_auth::class, $model2->auth_class);

        $reloaded_model = totara_webhook::load_by_id($model->id);
        // Changes have been persisted to the database.
        self::assertEquals('def', $reloaded_model->name);
        self::assertEquals('def', $reloaded_model->endpoint);
        self::assertTrue($reloaded_model->get_immediate());
        self::assertSame(mock_webhook_auth::class, $model->auth_class);
        self::assertSame(mock_webhook_auth::$generated_config, $model->auth_config);
    }

    public function test_model_required_field_name_behaviour(): void {
        // Required field name should throw an exception if not provided.
        self::expectException(\TypeError::class);
        totara_webhook::create(
                    null,
                    'abc'
                );
    }

    public function test_model_required_field_endpoint_behaviour(): void {
        // Required field endpoint should throw an exception if not provided.
        self::expectException(\TypeError::class);
        totara_webhook::create(
                    'abc',
                    null
                );
    }

    public function test_model_create_with_event_subscriptions(): void {
        $webhook = totara_webhook::create('test', 'https://example.com/webhook', true, false,
            [
                \core\event\user_profile_viewed::class,
                \core\event\user_created::class,
            ]
        );
        $event_subscriptions = $webhook->get_event_subscriptions()->pluck('event');
        $this->assertCount(2, $event_subscriptions);
        $this->assertTrue(in_array(\core\event\user_profile_viewed::class, $event_subscriptions));
        $this->assertTrue(in_array(\core\event\user_created::class, $event_subscriptions));
    }

    public function test_model_update_with_event_subscriptions(): void {
         $webhook = totara_webhook::create('test', 'https://example.com/webhook', true, false,
            [
                \core\event\user_profile_viewed::class,
                \core\event\user_created::class,
            ]
        );
        $event_subscriptions = $webhook->get_event_subscriptions()->pluck('event');
        $this->assertCount(2, $event_subscriptions);
        $webhook->update($webhook->name, $webhook->endpoint, false, false, [
            \core\event\badge_created::class
        ]);
        $event_subscriptions = $webhook->get_event_subscriptions()->pluck('event');
        $this->assertCount(1, $event_subscriptions);
        $this->assertFalse(in_array(\core\event\user_profile_viewed::class, $event_subscriptions));
        $this->assertTrue(in_array(\core\event\badge_created::class, $event_subscriptions));
    }

    public function test_events(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $sink = $this->redirectEvents();
        $sink->clear();
        $model = totara_webhook::create(
            'abc',
            'abc'
        );
        $webhook_id = $model->id;
        $found_events = $sink->get_events();
        $this->assertCount(1, $found_events);
        $event = $found_events[0];
        $expected_event = get_string('event_totara_webhook_created_description', 'totara_webhook', [
            'id' => $webhook_id,
            'userid' => $user->id,
        ]);
        $this->assertSame($expected_event, $event->get_description());

        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        $sink->clear();
        $model->update('test', 'https://example.com/webhook', false, false,[
            \core\event\badge_created::class
        ]);
        $found_events = $sink->get_events();
        $this->assertCount(1, $found_events);
        $event = $found_events[0];
        $expected_event = get_string('event_totara_webhook_updated_description', 'totara_webhook', [
            'id' => $webhook_id,
            'userid' => $user2->id,
        ]);
        $this->assertSame($expected_event, $event->get_description());

        $user3 = $this->getDataGenerator()->create_user();
        $this->setUser($user3);
        $sink->clear();
        $model->delete();
        $found_events = $sink->get_events();
        $this->assertCount(1, $found_events);
        $event = $found_events[0];
        $expected_event = get_string('event_totara_webhook_deleted_description', 'totara_webhook', [
            'id' => $webhook_id,
            'userid' => $user3->id,
        ]);
        $this->assertSame($expected_event, $event->get_description());

        $sink->close();
    }

    public function test_model_get_immediately(): void {
        $model = totara_webhook::create(
            'abc',
            'abc'
        );
        $this->assertInstanceOf(totara_webhook::class, $model);

        $this->assertFalse($model->get_immediate());

        $entity = new totara_webhook_entity();
        $entity->name = 'abc';
        $entity->endpoint = 'abc';
        $entity->immediate = true;
        $entity->auth_class = webhook_hmac_auth::class;
        $entity->save();

        $model2 = totara_webhook::load_by_entity($entity);

        $this->assertInstanceOf(totara_webhook::class, $model2);

        $this->assertTrue($model2->get_immediate());

    }

    public function test_enable_disable_status(): void {
        //one default webhook and one to be disabled
         $webhook1 = totara_webhook::create(
            'abc',
            'abc'
        );

         $webhook2 = totara_webhook::create(
             'abc',
             'abc'
         );


        $this->assertTrue($webhook1->get_status());
        $this->assertTrue($webhook2->get_status());

        $webhook1->update(
            null,
            null,
            false,
            null
        );

        $this->assertFalse($webhook1->get_status());
        $this->assertTrue($webhook2->get_status());
    }

    public function test_create_empty_name(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('name cannot be empty');
        $model = totara_webhook::create(
            '',
            'abc'
        );
    }

    public function test_update_empty_name(): void {
        $model = totara_webhook::create(
            'abc',
            'abc'
        );
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('name cannot be empty');
        $model->update('', 'https://example.com/webhook', true, false);
    }

    public function setUp(): void {
        parent::setUp();
    }

}
