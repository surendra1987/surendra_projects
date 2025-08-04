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
 * @package core_orm
 */

namespace core\orm\entity;

use core\cipher\manager;
use stdClass;

/**
 * A model that contains encrypted attributes.
 * Attributes that should have encryption applied can be defined in $encrypted_attribute_list.
 */
abstract class encrypted_model extends model {
    /**
     * This marks attributes as holding encrypted/private values that should be opaque at the entity and storage level.
     * If it is going to be accessed directly, the attribute must also be in the entity attribute whitelist.
     *
     * @var array
     */
    protected $encrypted_attribute_list = [];

    /**
     * Set the value of an encrypted entity attribute, and saves the entity.
     * Note that the entity must already exist, because we lock the encrypted value to the record ID.
     *
     * @param string $name
     * @param $clear_value
     */
    final public function set_encrypted_attribute(string $name, $clear_value): void {
        // Encrypted entities are only available if the model is in the discoverable path
        $this->validate_encrypted_model();

        // Sanity check.
        if (!in_array($name, $this->encrypted_attribute_list)) {
            throw new \coding_exception('Attribute is not expected to be encrypted');
        }

        // The entity must already exist, because we authenticate the encrypted value to the entity ID.
        if (!$this->entity->exists()) {
            throw new \coding_exception('The entity must be saved before an encrypted attribute can be added.');
        }

        $compound_value = (new manager())->encrypt($clear_value, $this->get_id(), static::class);

        $this->entity->{$name} = $compound_value;
        $this->entity->save();
    }

    /**
     * Get the clear-text value of an encrypted entity attribute.
     *
     * @param string $name
     * @return string|false Returns false if value cannot be decrypted.
     */
    final protected function get_encrypted_attribute(string $name) {
        // Encrypted entities are only available if the model is in the discoverable path
        $this->validate_encrypted_model();

        // Sanity check.
        if (!in_array($name, $this->encrypted_attribute_list)) {
            throw new \coding_exception('Attribute is not expected to be encrypted');
        }

        // Decompose the compound entity value.
        $compound_value = $this->entity->{$name};

        // If it's a null, return false (we may store nulls from time to time)
        if ($compound_value === null) {
            return false;
        }

        $manager = new manager();
        $clear_value = $manager->decrypt($compound_value, $this->get_id(), static::class);

        // If we failed to decrypt & the value looks plain text, just return the plaintext instead.
        if ($clear_value === false && !$manager->is_encrypted_value($compound_value)) {
            return $compound_value;
        }

        return $clear_value;
    }

    /**
     * Override for the model, make sure to read the encrypted attribute.
     *
     * @param string $name
     * @return false|mixed|null
     */
    protected function get_entity_attribute(string $name) {
        if (in_array($name, $this->encrypted_attribute_list)) {
            return $this->get_encrypted_attribute($name);
        }

        return parent::get_entity_attribute($name);
    }

    /**
     * Internal validation to confirm the model is in the correct location for encryption.
     *
     * @return void
     */
    private function validate_encrypted_model(): void {
        // Namespace should include model in the path
        $expected = '\\model\\';
        if (strpos(static::class, $expected) !== false) {
            return;
        }

        // For unit tests some sample classes are accepted. Do not add extra classes here.
        if (defined('PHPUNIT_TEST') && PHPUNIT_TEST) {
            if (strpos(static::class, 'sample_model') === 0) {
                return;
            }
        }

        throw new \coding_exception(sprintf('encrypted model %s must be in the namespace component_name\\model\\model_name', static::class));
    }

    /**
     * @inheritDoc
     */
    public function to_stdClass(): stdClass {
        $object = parent::to_stdClass();
        // Encrypted values.
        foreach ($this->encrypted_attribute_list as $key) {
            $object->{$key} = $this->get_encrypted_attribute($key);
        }
        return $object;
    }
}