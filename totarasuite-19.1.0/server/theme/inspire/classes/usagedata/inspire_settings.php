<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package theme_inspire
 */

namespace theme_inspire\usagedata;

use core\theme\settings;
use tool_usagedata\export;

class inspire_settings implements export {
    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('inspire_settings_usagedata_summary', 'theme_inspire');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @inheritDoc
     */
    public function export(): array {
        $theme_config = \theme_config::load('inspire');
        $theme_settings = new settings($theme_config,  0);

        $displaynavicons = $theme_settings->get_property('brand', 'formbrand_field_displaynavicons');
        $expandnav = $theme_settings->get_property('brand', 'formbrand_field_expandnav');

        return [
            'icons_enabled' => isset($displaynavicons['value']) ? filter_var($displaynavicons['value'], FILTER_VALIDATE_BOOLEAN) : true,
            'desktop_default' => $expandnav['value'] ?? 'expanded'
        ];
    }
}