<?php
/*
 * This file is part of Totara Talent Experience Platform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core
 */

namespace core\model;

use core\orm\entity\encrypted_model;

/**
 * Model class config plugin
 *
 * @property-read int $id
 * @property-read string $plugin
 * @property-read string $name
 * @property-read string $value
 * @package core\model
 */
class encrypted_config_plugin extends encrypted_model {

    /**
     * Fields that are directly accessed from the entity.
     *
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'plugin',
        'name',
        'value',
    ];

    /**
     * Fields that are stored encrypted.
     *
     * @var string[]
     */
    protected $encrypted_attribute_list = [
        'value',
    ];

    protected static function get_entity_class(): string {
        return \core\entity\encrypted_config_plugin::class;
    }

    /**
     * Read the config value - global helper method, like get_config($plugin)
     *
     * @param string $plugin
     * @param string $name
     * @return string|null
     */
    public static function get_config_value(string $plugin, string $name) {
        $entity = \core\entity\encrypted_config_plugin::repository()
            ->where('plugin', $plugin)
            ->where('name', $name)
            ->get()
            ->first();

        if (!$entity) {
            return null;
        }

        $model = static::load_by_entity($entity);
        return $model->value;
    }
}
