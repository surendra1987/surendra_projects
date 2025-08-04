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
 * @package core
 */

namespace core_cipher\fixtures\model;

use core\orm\entity\encrypted_model;
use core\orm\entity\entity;

/**
 * Mock class for testing ciphers
 *
 * @property-read int $id
 * @property-read string $plain
 * @property-read string|false|null $encrypted
 */
class cipher_mock_model extends encrypted_model {
    protected $encrypted_attribute_list = ['encrypted'];
    protected $entity_attribute_whitelist = ['plain', 'encrypted', 'id'];

    protected static function get_entity_class(): string {
        return cipher_mock_entity::class;
    }

    /**
     * @return cipher_mock_entity|entity
     */
    public function get_entity(): cipher_mock_entity {
        return $this->entity;
    }
}
