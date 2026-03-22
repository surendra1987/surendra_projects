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
use totara_webhook\entity\totara_webhook_event_subscription as totara_webhook_event_subscription_entity;
use totara_webhook\data_provider\totara_webhook_event_subscription as totara_webhook_event_subscription_provider;
use totara_webhook\model\totara_webhook_event_subscription;
use totara_webhook\testing\totara_webhook_event_subscription_generator;
use totara_webhook\testing\generator as totara_webhook_generator;

defined('MOODLE_INTERNAL') || die();

/**
 * @group totara_webhook
 */
class totara_webhook_totara_webhook_event_subscription_model_test extends testcase {

    public function test_create_model() {
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook();
        $model = totara_webhook_event_subscription::create(
            \core\event\user_profile_viewed::class,
            $webhook->id
        );
        $this->assertInstanceOf(totara_webhook_event_subscription::class, $model);

        $this->assertEquals(\core\event\user_profile_viewed::class, $model->event);
        $this->assertEquals($webhook->id, $model->webhook_id);

        $entity = new totara_webhook_event_subscription_entity();
        $entity->event = 'abc';
        $entity->webhook_id = $webhook->id;
        $entity->save();
        $model2 = totara_webhook_event_subscription::load_by_entity($entity);

        $this->assertInstanceOf(totara_webhook_event_subscription::class, $model2);

        $this->assertEquals('abc', $model2->event);
        $this->assertEquals($webhook->id, $model2->webhook_id);
        $this->assertSame($webhook->id, $model2->get_webhook()->id);
    }

    public function test_delete_model() {
        $generator = totara_webhook_event_subscription_generator::instance();
        $webhook_generator = totara_webhook_generator::instance();
        $webhook_alpha = $webhook_generator->create_totara_webhook();
        $model = $generator->create_totara_webhook_event_subscription(['webhook_id' => $webhook_alpha->id]);
        $model2 = $generator->create_totara_webhook_event_subscription(['webhook_id' => $webhook_alpha->id]);

        $provider = totara_webhook_event_subscription_provider::create();
        self::assertCount(2, $provider->fetch());

        $deleted_id = $model->id;
        $model->delete();
        // One model should have been removed, the other should still be there.
        $new_model2 = totara_webhook_event_subscription::load_by_id($model2->id);

        self::assertEquals($model2->id, $new_model2->id);

        // An exception should be thrown because the model was deleted.
        self::expectException(\dml_exception::class);
        totara_webhook_event_subscription::load_by_id($deleted_id);
    }

    public function test_update_model() {
        $generator = totara_webhook_event_subscription_generator::instance();
        $webhook_generator = totara_webhook_generator::instance();
        $webhook_alpha = $webhook_generator->create_totara_webhook();
        $webhook_beta = $webhook_generator->create_totara_webhook();
        $model = $generator->create_totara_webhook_event_subscription(['webhook_id' => $webhook_alpha->id]);
        $model2 = $generator->create_totara_webhook_event_subscription(['webhook_id' => $webhook_alpha->id]);

        $model->update(
            'def',
            $webhook_beta->id
        );

        // Updated fields have changed in the existing object.
        self::assertEquals('def', $model->event);
        self::assertEquals($webhook_beta->id, $model->webhook_id);

        // An unmodified object should not have changed.
        self::assertEquals('abc', $model2->event);
        self::assertEquals($webhook_alpha->id, $model2->webhook_id);

        $reloaded_model = totara_webhook_event_subscription::load_by_id($model->id);
        // Changes have been persisted to the database.
        self::assertEquals('def', $reloaded_model->event);
        self::assertEquals($webhook_beta->id, $reloaded_model->webhook_id);
    }

    public function test_model_required_field_webhook_id_behaviour() {
        // Required field webhook_id should throw an exception if not provided.
        self::expectException(\TypeError::class);
        totara_webhook_event_subscription::create(
                    'abc',
                    null
                );
    }

    public function test_get_webhook_for_event_subscription(): void {
        $webhook_generator = totara_webhook_generator::instance();
        $webhook = $webhook_generator->create_totara_webhook();
        $event_sub_gen = totara_webhook_event_subscription_generator::instance();
        $event_subscription = $event_sub_gen->create_totara_webhook_event_subscription(['webhook_id' => $webhook->id, 'event' => \core\event\badge_created::class]);
        /** @var \core\orm\entity\relations\belongs_to $owner */
        $owner = $event_subscription->get_entity_copy()->webhook();
        $matched_webhook = $owner->get()->first();
        $this->assertSame($webhook->id, $matched_webhook->id);
    }

    public function test_events(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $sink = $this->redirectEvents();
        $webhook = \totara_webhook\model\totara_webhook::create(
            'abc',
            'https://example.com/webhook'
        );
        $sink->clear();

        $event_subscription = totara_webhook_event_subscription::create(
            \core\event\user_created::class,
            $webhook->get_id()
        );
        $event_sub_id = $event_subscription->get_id();
        $found_events = $sink->get_events();
        $this->assertCount(1, $found_events);
        $event = $found_events[0];
        $expected_event_desc = get_string(
            'event_totara_webhook_event_subscription_created_description',
            'totara_webhook',
            [
                'id' => $event_sub_id,
                'userid' => $user->id,
            ]
        );
        $this->assertSame($event->get_description(), $expected_event_desc);

        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user2);
        $sink->clear();
        $event_subscription->update(\core\event\badge_created::class, $event_subscription->webhook_id);
        $found_events = $sink->get_events();
        $this->assertCount(1, $found_events);
        $event = $found_events[0];
        $expected_event_desc = get_string(
            'event_totara_webhook_event_subscription_updated_description',
            'totara_webhook',
            [
                'id' => $event_sub_id,
                'userid' => $user2->id,
            ]
        );
        $this->assertSame($event->get_description(), $expected_event_desc);

        $user3 = $this->getDataGenerator()->create_user();
        $this->setUser($user3);
        $sink->clear();
        $event_subscription->delete();
        $found_events = $sink->get_events();
        $this->assertCount(1, $found_events);
        $event = $found_events[0];
        $expected_event_desc = get_string(
            'event_totara_webhook_event_subscription_deleted_description',
            'totara_webhook',
            [
                'id' => $event_sub_id,
                'userid' => $user3->id,
            ]
        );
        $this->assertSame($event->get_description(), $expected_event_desc);

        $sink->close();
    }

}