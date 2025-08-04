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

namespace core\cipher\testing;

use cache_loader;
use core\cipher\task\rollover_encryption_manager;
use core_cipher\fixtures\model\cipher_mock_entity;
use core_cipher\fixtures\model\cipher_mock_model;
use ReflectionClass;
use ReflectionProperty;
use xmldb_table;

/**
 * Helper methods to test ciphers against dummy models.
 */
trait mock_cipher_models {
    /**
     * @var string Used for testing the models
     */
    protected $table_name = 'test__ciphers';

    /**
     * Remove the created table after test
     */
    protected function drop_table(): void {
        global $DB;
        if ($DB->get_manager()->table_exists($this->table_name)) {
            $DB->get_manager()->drop_table(new xmldb_table($this->table_name));
        }
    }

    /**
     * Create the dummy table.
     */
    protected function create_table(): void {
        global $DB, $CFG;

        require_once($CFG->libdir . '/tests/fixtures/cipher_mock_entity.php');
        require_once($CFG->libdir . '/tests/fixtures/cipher_mock_model.php');

        if ($DB->get_manager()->table_exists($this->table_name)) {
            return;
        }

        $table = new xmldb_table($this->table_name);

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('plain', XMLDB_TYPE_CHAR, '255');
        $table->add_field('encrypted', XMLDB_TYPE_TEXT);

        $table->add_field('created_at', XMLDB_TYPE_INTEGER, '10', null);
        $table->add_field('updated_at', XMLDB_TYPE_INTEGER, '10', null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        $DB->get_manager()->create_table($table);
    }

    /**
     * @param string|null $plain
     * @param string|null $encrypted
     * @return cipher_mock_model
     */
    protected function create_model(?string $plain = null, ?string $encrypted = null): cipher_mock_model {
        $entity = new cipher_mock_entity();
        $entity->plain = $plain ?? null;
        $entity->created_at = time();
        $entity->save();
        $entity->refresh();

        $model = new cipher_mock_model($entity);
        if ($encrypted) {
            $model->set_encrypted_attribute('encrypted', $encrypted);
        }

        return $model;
    }

    /**
     * Create a new table + return a instance of rollover_encryption_manager
     * with the test models populated already.
     *
     * @param cache_loader|null $cache_loader A mock cache loader to use
     * @return rollover_encryption_manager
     */
    protected function create_mock_setup(?cache_loader $cache_loader = null, bool $with_models = true): rollover_encryption_manager {
        $this->create_table();

        $mock_cache = $cache_loader ?? $this->createMock(cache_loader::class);

        // Create our mock manager
        $reflect = new ReflectionClass(rollover_encryption_manager::class);
        $constructor = $reflect->getConstructor();
        $constructor->setAccessible(true);
        $obj = $reflect->newInstanceWithoutConstructor();
        $constructor->invoke($obj, $mock_cache);

        // Force our mock model in
        if ($with_models) {
            $reflection = new ReflectionProperty(rollover_encryption_manager::class, 'model_definitions');
            $reflection->setAccessible(true);
            $mock_models = [
                cipher_mock_model::class => [
                    $this->table_name,
                    ['encrypted']
                ],
            ];
            $reflection->setValue($obj, $mock_models);
        }

        // Override the instance
        $reflect = new \ReflectionProperty(rollover_encryption_manager::class, 'instance');
        $reflect->setAccessible(true);
        $reflect->setValue(null, $obj);

        return $obj;
    }

    /**
     * Undo the global mock object.
     *
     * @return void
     */
    protected function reset_mock_models(): void {
        // Remove our default instance
        $reflect = new \ReflectionProperty(rollover_encryption_manager::class, 'instance');
        $reflect->setAccessible(true);
        $reflect->setValue(null, null);

        $this->drop_table();
    }
}
