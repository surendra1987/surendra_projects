<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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

/**
 * Tests covering the config/config_plugin encryption steps.
 *
 * @package core
 * @group orm
 */
class core_orm_encrypted_config_test extends \core_phpunit\testcase {

    /**
     * @return array[]
     */
    public static function model_data_provider(): array {
        return [
            [\core\entity\encrypted_config::class, \core\model\encrypted_config::class, false],
            [\core\entity\encrypted_config_plugin::class, \core\model\encrypted_config_plugin::class, true],
        ];
    }

    /**
     * Assert we can store and retrieve a value via the config/config_plugin models.
     *
     * @param string $entity_class
     * @param string $model_class
     * @param bool $plugin
     * @return void
     * @dataProvider model_data_provider
     */
    public function test_encrypted_config(string $entity_class, string $model_class, bool $plugin): void {
        global $DB;

        // Save a value
        $name = 'secret';
        $secret = 'I am a Secret';
        $plugin_name = $plugin ? 'test_plugin' : 'core';

        $initial = ['name' => $name, 'value' => ''];
        if ($plugin) {
            $initial['plugin'] = $plugin_name;
        }

        /** @var \core\entity\encrypted_config|\core\entity\encrypted_config_plugin $entity */
        $entity = new $entity_class($initial);
        $entity->save();

        /** @var \core\model\encrypted_config|\core\model\encrypted_config_plugin $model */
        $model = $model_class::load_by_entity($entity);
        $model->set_encrypted_attribute('value', $secret);

        if ($plugin) {
            $value = $DB->get_record('config_plugins', ['plugin' => $plugin_name, 'name' => $name], 'value');
        } else {
            $value = $DB->get_record('config', ['name' => $name], 'value');
        }

        $this->assertNotSame($secret, $value->value);

        // Confirm we can decrypt it
        $value = $plugin ? $model::get_config_value($plugin_name, $name) : $model::get_config_value($name);
        $this->assertEquals($secret, $value);
    }

}
