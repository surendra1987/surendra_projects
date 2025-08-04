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

namespace core\cipher\task;

use cache_loader;
use core\cipher\key\manager;
use core\orm\entity\encrypted_model;
use core\orm\query\builder;

/**
 * This class handles finding & updating models that are using outdated encryption keys.
 * Access via the ::instance method as it contains & caches model definitions.
 */
class rollover_encryption_manager {
    private static ?rollover_encryption_manager $instance = null;

    protected cache_loader $cache;
    protected ?array $model_definitions = null;

    /**
     * Private constructor, use ::instance() method instead.
     *
     * @param cache_loader $cache
     */
    private function __construct(cache_loader $cache) {
        $this->cache = $cache;
    }

    /**
     * @return self
     */
    public static function instance(): self {
        if (self::$instance === null) {
            $cache = \cache::make('core', 'core_cipher_encrypted_models');
            self::$instance = new self($cache);
        }

        return self::$instance;
    }

    /**
     * Returns the list of models that support encryption and have defined entities.
     *
     * @return array
     */
    public function get_encrypted_models(): array {
        $this->load_model_definitions();
        return array_keys($this->model_definitions);
    }

    /**
     * Return the information of a single instance.
     *
     * @param string $model_class
     * @return array|null
     */
    public function get_model_definition(string $model_class): ?array {
        $this->load_model_definitions();
        return $this->model_definitions[$model_class] ?? null;
    }

    /**
     * @param string $model_class
     * @return bool
     */
    public function is_encrypted_model(string $model_class): bool {
        $this->load_model_definitions();
        return isset($this->model_definitions[$model_class]);
    }

    /**
     * Count the number of out of date records.
     *
     * @param string $model_class
     * @return int
     */
    public function count_outdated_records(string $model_class): int {
        // Search for records that do not match this key, those are
        // most likely out of date and need updating.
        $search = $this->build_search_query($model_class);
        $search->select(['id']);
        return $search->count();
    }

    /**
     * For the specific model, find & update the out of date records.
     *
     * @param string $model_class
     * @return int
     */
    public function update_outdated_records(string $model_class): int {
        [$table, $encrypted_attributes] = $this->get_model_definition($model_class);

        $cipher_manager = new \core\cipher\manager();
        $updated_count = 0;

        $search = $this->build_search_query($model_class);
        $records = $search->get_lazy();
        foreach ($records as $record) {
            $count = 0;
            foreach ($encrypted_attributes as $attribute) {
                // Let's make sure this attribute is actually encrypted
                $value = $record->$attribute ?? null;
                if (!$value || !$cipher_manager->is_encrypted_value($value)) {
                    continue;
                }

                $clear_value = $cipher_manager->decrypt($record->$attribute, $record->id, $model_class);
                if ($clear_value === false) {
                    // Failed to decrypt, we cannot re-encrypt, move along (leave the whole record alone)
                    continue;
                }
                $record->$attribute = $cipher_manager->encrypt($clear_value, $record->id, $model_class);
                $count++;
            }
            if ($count > 0) {
                builder::get_db()->update_record($table, $record);
                $updated_count++;
            }
        }

        return $updated_count;
    }

    /**
     * Loads the model definitions.
     *
     * @return void
     */
    protected function load_model_definitions(): void {
        if (is_array($this->model_definitions)) {
            return;
        }

        // Try the cache
        $result = $this->cache->get('list');
        if (is_array($result)) {
            $this->model_definitions = $result;
            return;
        }

        // Load the list then
        $models = \core_component::get_namespace_classes('model', encrypted_model::class);

        // Filter down to only those that have the encrypted entities set (they may have defined but not specified)
        $encrypted_models = [];
        foreach ($models as $model_class) {
            $rc = new \ReflectionClass($model_class);
            $properties = $rc->getDefaultProperties();
            if (!empty($properties['encrypted_attribute_list'])) {
                // Figure out the related entity table as well
                $method = $rc->getMethod('get_entity_class');
                $method->setAccessible(true);
                $entity_class = $method->invoke(null);
                $encrypted_models[$model_class] = [
                    $entity_class::TABLE,
                    $properties['encrypted_attribute_list'],
                ];
            }
        }
        unset($models);
        $this->cache->set('list', $encrypted_models);
        $this->model_definitions = $encrypted_models;
    }

    /**
     * Build up the base query used to find out of date records.
     *
     * @param string $model_class
     * @return builder
     */
    private function build_search_query(string $model_class): builder {
        $definition = $this->get_model_definition($model_class);
        if (empty($definition)) {
            throw new \coding_exception('No encryption definition found for ' . $model_class);
        }
        [$table, $encrypted_attribute_list] = $definition;

        $latest_key = manager::instance()->get_key();
        $latest_key_id = "$latest_key[0]::"; // Makes sure we're checking against the boundary so key 1 and 11 don't match
        unset($latest_key);

        // Search for records that do not match this key, those are
        // most likely out of date and need updating.
        $search = builder::table($table)->select(['id', ...$encrypted_attribute_list]);
        foreach ($encrypted_attribute_list as $attribute) {
            $search->or_where(function (builder $builder) use ($latest_key_id, $attribute) {
                $builder->where_not_null($attribute)->where($attribute, '!like_starts_with', $latest_key_id);
            });
        }

        return $search;
    }
}