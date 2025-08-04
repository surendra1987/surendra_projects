<?php
/**
 * This file is part of Totara Core
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\configuration;

use admin_settingpage;
use core_ai\data_transfer_object_base;

/**
 * A collection of options, which represent Totara admin settings.
 */
class config_collection extends data_transfer_object_base {
    /**
     * @var option[] $options
     */
    private array $options;

    /**
     * @param option[] $options
     */
    public function __construct(array $options) {
        $options_list = [];
        foreach ($options as $option) {
            if (!$option instanceof option) {
                throw new \coding_exception("Only config options are allowed");
            }
            $options_list[$option->get_admin_setting()->name] = $option;
        }
        $this->options = $options_list;
    }

    /**
     * Convert any non-secure admin settings to an array of name => value pairs.
     *
     * @return array
     */
    public function to_array(): array {
        $values = [];

        foreach ($this->options as $option) {
            if ($option->is_secure()) {
                continue;
            }
            $setting = $option->get_admin_setting();
            $values[$setting->name] = $this->get_value($setting->name);
        }

        return $values;
    }

    /**
     * Check if there are defined options.
     *
     * @return bool
     */
    public function has_options(): bool {
        return !empty($this->options);
    }

    /**
     * Add the collection of admin settings to a settings page.
     *
     * @param admin_settingpage $page
     * @return void
     */
    public function add_to_settings_page(admin_settingpage $page): void {
        foreach ($this->options as $option) {
            $page->add($option->get_admin_setting());
        }
    }

    /**
     * Get the value of a named option, using the default if the value is empty.
     *
     * Bascially get_config() but you don't need to know the component.
     *
     * @param string $setting
     * @return mixed
     */
    public function get_value(string $setting) {
        $admin_setting = $this->options[$setting]->get_admin_setting();
        $value = $admin_setting->get_setting();

        $default_setting = $admin_setting->get_defaultsetting();

        if (empty($value) && !empty($default_setting)) {
            $value = $default_setting;
        }

        return $value;
    }
}
