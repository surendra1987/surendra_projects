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

use core\event\user_created;
use core\event\user_profile_viewed;
use core_phpunit\testcase;
use totara_webhook\entity\totara_webhook_dlq_item as totara_webhook_dlq_item_entity;
use totara_webhook\data_provider\totara_webhook_dlq_item as totara_webhook_dlq_item_provider;
use totara_webhook\event\totara_webhook_dlq_item_created;
use totara_webhook\event\totara_webhook_dlq_item_deleted;
use totara_webhook\event\totara_webhook_dlq_item_updated;
use totara_webhook\handler\default_handler\entity\totara_webhook_payload_queue;
use totara_webhook\model\totara_webhook_dlq_item;
use totara_webhook\testing\totara_webhook_dlq_item_generator;
use totara_webhook\testing\generator as totara_webhook_generator;
use totara_webhook\totara_webhook_payload;

/**
 * @group totara_webhook
 */
class totara_webhook_totara_webhook_dlq_item_model_test extends testcase {

    /**
     * helper for setting up data for testing
     *
     * @return array
     */
    protected function create_webhook_and_payload(): array {
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook(
            [
                'name' => 'abc',
                'endpoint' => 'https://example.com/webhook',
                'events' => [
                    user_profile_viewed::class,
                ]
            ]
        );
        $payload = $webhook_generator->create_totara_webhook_payload(
            [
                'attempt' => 1,
                'body' => ['testing' => true],
                'event' => user_profile_viewed::class,
                'webhook_id' => $webhook->id
            ]
        );
        return [$webhook, $payload];
    }

    public function test_create_model(): void {
        [$webhook, $payload] = $this->create_webhook_and_payload();
        [$webhook2, $payload2] = $this->create_webhook_and_payload();
        $model = totara_webhook_dlq_item::create_from_totara_webhook_payload(
            $payload
        );
        $this->assertInstanceOf(totara_webhook_dlq_item::class, $model);

        $this->assertEquals($webhook->id, $model->webhook_id);

        $entity = new totara_webhook_dlq_item_entity();
        $entity->webhook_id = $webhook2->id;
        $entity->body = json_encode($payload2->get_body());
        $entity->event = user_profile_viewed::class;
        $entity->time_created = time();
        $entity->attempt = 10;
        $entity->save();
        $model2 = totara_webhook_dlq_item::load_by_entity($entity);

        $this->assertInstanceOf(totara_webhook_dlq_item::class, $model2);

        $this->assertEquals($webhook2->id, $model2->webhook_id);
    }

    public function test_delete_model() {
        [$webhook, $payload] = $this->create_webhook_and_payload();
        [$webhook2, $payload2] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);
        $model2 = $generator->create_totara_webhook_dlq_item_from_payload($payload2);

        $this->assertSame($webhook->id, $model->webhook_id);
        $this->assertSame($webhook2->id, $model2->webhook_id);

        $provider = totara_webhook_dlq_item_provider::create();
        $this->assertCount(2, $provider->fetch());

        $deleted_id = $model->id;
        $model->delete();
        // One model should have been removed, the other should still be there.
        $new_model2 = totara_webhook_dlq_item::load_by_id($model2->id);

        $this->assertEquals($model2->id, $new_model2->id);

        // An exception should be thrown because the model was deleted.
        $this->expectException(\dml_exception::class);
        totara_webhook_dlq_item::load_by_id($deleted_id);
    }

    public function test_update_model() {
        [$webhook, $payload] = $this->create_webhook_and_payload();
        [$webhook2, $payload2] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);
        $model2 = $generator->create_totara_webhook_dlq_item_from_payload($payload2);

        $this->assertSame($webhook->id, $model->webhook_id);

        $model->update(
            $webhook2->id,
            20,
            user_created::class,
            'updated body',
            1000
        );

        // Updated fields have changed in the existing object.
        $this->assertEquals($webhook2->id, $model->webhook_id);

        // An unmodified object should not have changed.
        $this->assertEquals($webhook2->id, $model2->webhook_id);

        // check other fields were updated
        $this->assertEquals(20, $model->get_attempt());
        $this->assertEquals('updated body', $model->get_body());
        $this->assertEquals(1000, $model->get_time_created());

        $reloaded_model = totara_webhook_dlq_item::load_by_id($model->id);
        // Changes have been persisted to the database.
        $this->assertEquals($webhook2->id, $reloaded_model->webhook_id);
    }

    public function test_created_event_triggered(): void {
        $sink = $this->redirectEvents();
        $sink->clear();
        $this->assertSame(0, $sink->count());
        $user = get_admin();
        $this->setUser($user);
        [$webhook, $payload] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);
        $created_event_triggered = false;
        $events = $sink->get_events();
        $expected_event_message = get_string('event_totara_webhook_dlq_item_created_description', 'totara_webhook', [
                'id' => $model->id,
                'userid' => $user->id,
        ]);
        $event_message = '';
        foreach ($events as $event) {
            if ($event instanceof totara_webhook_dlq_item_created) {
                $created_event_triggered = true;
                $event_message = $event->get_description();
            }
        }
        $this->assertTrue($created_event_triggered);
        $this->assertSame($expected_event_message, $event_message);
    }

    public function test_deleted_event_triggered(): void {
        $sink = $this->redirectEvents();
        $user = get_admin();
        $this->setUser($user);
        [$webhook, $payload] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);
        $expected_event_message = get_string('event_totara_webhook_dlq_item_deleted_description', 'totara_webhook', [
            'id' => $model->id,
            'userid' => $user->id,
        ]);
        $sink->clear();
        $this->assertSame(0, $sink->count());
        $model->delete();
        $deleted_event_triggered = false;
        $events = $sink->get_events();
        $event_message = '';
        foreach ($events as $event) {
            if ($event instanceof totara_webhook_dlq_item_deleted) {
                $deleted_event_triggered = true;
                $event_message = $event->get_description();
            }
        }
        $this->assertTrue($deleted_event_triggered);
        $this->assertSame($expected_event_message, $event_message);
    }

    public function test_updated_event_triggered(): void {
        $sink = $this->redirectEvents();
        $user = get_admin();
        $this->setUser($user);
        [$webhook, $payload] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);
        $expected_event_message = get_string('event_totara_webhook_dlq_item_updated_description', 'totara_webhook', [
            'id' => $model->id,
            'userid' => $user->id,
        ]);
        $sink->clear();
        $this->assertSame(0, $sink->count());
        $model->update(null, 1);
        $updated_event_triggered = false;
        $events = $sink->get_events();
        $event_message = '';
        foreach ($events as $event) {
            if ($event instanceof totara_webhook_dlq_item_updated) {
                $updated_event_triggered = true;
                $event_message = $event->get_description();
            }
        }
        $this->assertTrue($updated_event_triggered);
        $this->assertSame($expected_event_message, $event_message);
    }

    public function test_requeue(): void {
        [$webhook, $payload] = $this->create_webhook_and_payload();
        $generator = totara_webhook_dlq_item_generator::instance();
        $model = $generator->create_totara_webhook_dlq_item_from_payload($payload);

        $standard_queued_items_count = totara_webhook_payload_queue::repository()->count();
        $this->assertSame(0, $standard_queued_items_count);
        $dlq_items_count = totara_webhook_dlq_item_entity::repository()->count();
        $this->assertSame(1, $dlq_items_count);

        $model->requeue();
        $standard_queued_items_count = totara_webhook_payload_queue::repository()->count();
        $this->assertSame(1, $standard_queued_items_count);
        $dlq_items_count = totara_webhook_dlq_item_entity::repository()->count();
        $this->assertSame(0, $dlq_items_count);
    }
}