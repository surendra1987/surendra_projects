<?php
/*
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\configuration;

use admin_settingpage;

class config_collection implements \JsonSerializable {
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

    public function jsonSerialize(): array {
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

    public function has_options(): bool {
        return !empty($this->options);
    }

    public function add_to_settings_page(admin_settingpage $page): void {
        foreach ($this->options as $option) {
            $page->add($option->get_admin_setting());
        }
    }

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
