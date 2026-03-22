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
 * @package core_cipher
 */

use core\cipher\key\manager as key_manager;
use core\cipher\task\rollover_encryption_manager;
use core\cipher\testing\mock_cipher_models;
use core\cipher\testing\mock_key_manager;
use core\orm\query\builder;
use core_cipher\fixtures\model\cipher_mock_model;
use core_phpunit\testcase;

/**
 * @group core_cipher
 */
class core_cipher_rollover_encryption_manger_test extends testcase {
    use mock_cipher_models, mock_key_manager;

    /**
     * Test the model definitions are loaded from object, cache & then live lookup
     * in that order.
     *
     * @return void
     */
    public function test_load_model_definitions(): void {
        // Mock a cache loader
        $mock_cache = $this->createMock(cache_loader::class);
        $mock_cache->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn(['read from cache'], null);
        $mock_cache->expects($this->once())
            ->method('set');

        $manager = $this->create_mock_setup($mock_cache, false);

        $reflection = new ReflectionProperty(rollover_encryption_manager::class, 'model_definitions');
        $reflection->setAccessible(true);

        // Starts off with nothing
        $value = $reflection->getValue($manager);
        $this->assertNull($value);

        // Assert we read from the object first of all
        $reflection->setValue($manager, ['from_object']);
        $this->callInternalMethod($manager, 'load_model_definitions', []);
        $value = $reflection->getValue($manager);
        $this->assertSame(['from_object'], $value);

        // Assert we read from the cache if it has a value
        $reflection->setValue($manager, null);
        $this->callInternalMethod($manager, 'load_model_definitions', []);
        $value = $reflection->getValue($manager);
        $this->assertSame(['read from cache'], $value);

        // Assert we load the data fresh
        $reflection->setValue($manager, null);
        $this->callInternalMethod($manager, 'load_model_definitions', []);
        $value = $reflection->getValue($manager);
        $this->assertIsArray($value);

        // Assert the second call we see the data again from the object
        $this->callInternalMethod($manager, 'load_model_definitions', []);
        $value2 = $reflection->getValue($manager);
        $this->assertIsArray($value2);
        $this->assertSame($value, $value2);

        $this->reset_mock_models();
    }

    /**
     * Test the simple getter methods.
     *
     * @return void
     */
    public function test_encrypted_models_lookup(): void {
        $mock_cache = $this->createMock(cache_loader::class);

        $manager = $this->create_mock_setup($mock_cache, false);

        $reflection = new ReflectionProperty(rollover_encryption_manager::class, 'model_definitions');
        $reflection->setAccessible(true);
        $mock_models = [
            'class_a' => ['a', 'a'],
            'class_b' => ['b', 'b'],
        ];
        $reflection->setValue($manager, $mock_models);

        $value = $manager->get_encrypted_models();
        $this->assertIsArray($value);
        $this->assertSame(['class_a', 'class_b'], $value);

        $value = $manager->get_model_definition('class_a');
        $this->assertSame(['a', 'a'], $value);

        $value = $manager->get_model_definition('not_real');
        $this->assertNull($value);

        $value = $manager->is_encrypted_model('class_a');
        $this->assertTrue($value);
        $value = $manager->is_encrypted_model('not_real');
        $this->assertFalse($value);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('No encryption definition found for does not exist');
        $manager->count_outdated_records('does not exist');

        $this->reset_mock_models();
    }

    /**
     * Test that data inside tables can be discovered and rolled over.
     * This test uses both mock keys & mock models.
     *
     * @return void
     */
    public function test_key_rollover(): void {
        $key_manager = $this->setup_mock_key_manager();
        $rollover_manager = $this->create_mock_setup();
        $first_id = $key_manager->add_key();

        $model1 = $this->create_model('plain', 'secret');

        // Assert $model1 is encrypted with the latest key
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(0, $count);

        // We need a new key that rolls over to a new second
        $this->waitForSecond();
        $second_id = $key_manager->add_key();

        // Assert $model1 is encrypted with an old key
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(1, $count);

        // Add a new MFA element
        $model2 = $this->create_model('plain2', 'secret2');

        // Assert $model1 is outdated, but $model2 is up-to-date
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(1, $count);

        // Add a third key
        $this->waitForSecond();
        $third_id = $key_manager->add_key();

        // Add $model3 in (should remain unchanged)
        $model3 = $this->create_model('plain3', 'secret3');

        // Assert $model1 is outdated and $model2 are out of date
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(2, $count);

        // Confirm all three have different encryption keys
        $model_keys = builder::table($this->table_name)
            ->select(['id', 'encrypted'])
            ->where_in('id', [$model1->get_id(), $model2->get_id(), $model3->get_id()])
            ->get()
            ->all(true);
        $this->assertStringStartsWith($first_id . '::', $model_keys[$model1->id]->encrypted);
        $this->assertStringStartsWith($second_id . '::', $model_keys[$model2->id]->encrypted);
        $this->assertStringStartsWith($third_id . '::', $model_keys[$model3->id]->encrypted);

        // Assert only two records were updated
        $updated_count = $rollover_manager->update_outdated_records(cipher_mock_model::class);
        $this->assertSame(2, $updated_count);

        // Confirm all three have the same encryption keys
        $model_keys = builder::table($this->table_name)
            ->select(['id', 'encrypted'])
            ->where_in('id', [$model1->get_id(), $model2->get_id(), $model3->get_id()])
            ->get()
            ->all(true);
        $this->assertStringStartsWith($third_id . '::', $model_keys[$model1->id]->encrypted);
        $this->assertStringStartsWith($third_id . '::', $model_keys[$model2->id]->encrypted);
        $this->assertStringStartsWith($third_id . '::', $model_keys[$model3->id]->encrypted);

        // Assert we can read all three
        $model1 = cipher_mock_model::load_by_id($model1->id);
        $model2 = cipher_mock_model::load_by_id($model2->id);
        $model3 = cipher_mock_model::load_by_id($model3->id);

        $this->assertNotEmpty($model1->encrypted);
        $this->assertNotEmpty($model2->encrypted);
        $this->assertNotEmpty($model3->encrypted);

        // Assert count is back to zero for all three
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(0, $count);

        // Cleanup afterwards
        $this->reset_mock_key_manager();
        $this->reset_mock_models();
    }

    /**
     * Assert if a record cannot be decrypted we do not mangle the data
     *
     * @return void
     */
    public function test_key_rollover_with_corruption(): void {
        $key_manager = key_manager::instance();
        $rollover_manager = $this->create_mock_setup();
        $key_manager->add_key();

        $this->create_table();

        $model1 = $this->create_model('plain', 'foobar2');
        $model2 = $this->create_model('plain', 'foobar33');

        // Now corrupt $model2 so it can't be read
        $entity2 = $model2->get_entity();
        $entity2->encrypted = 'garbage';
        $entity2->save()->refresh();

        // We should see 1 showing up as out of date (as the corrupted record is found)
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(1, $count);

        $this->waitForSecond();
        $key_manager->add_key();

        // We should see 2 showing up as out of date (as the corrupted record is found)
        $count = $rollover_manager->count_outdated_records(cipher_mock_model::class);
        $this->assertSame(2, $count);

        // When we roll over the records, only one will succeed
        $updated_count = $rollover_manager->update_outdated_records(cipher_mock_model::class);
        $this->assertSame(1, $updated_count);

        // Confirm our corrupted record didn't change
        $entity2->refresh();
        $this->assertSame('garbage', $entity2->encrypted);

        // Roll over once more, expect to see 0 changes
        $updated_count = $rollover_manager->update_outdated_records(cipher_mock_model::class);
        $this->assertSame(0, $updated_count);

        $this->reset_mock_key_manager();
        $this->reset_mock_models();
    }
}