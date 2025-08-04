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
 * @package totara_catalog
 */

namespace totara_catalog\formatter;

use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\formatter;

class item extends formatter {

    /**
     * @return array
     */
    protected function get_map(): array {
        return [
            'itemid' => null,
            'type_label' => null,
            'featured' => null,
            'title' => string_field_formatter::class,
            'logo' => null,
            'image_enabled' => null,
            'image' => null,
            'redirecturl' => null,
            'objecttype' => null,
            'hero_data_icon_enabled' => null,
            'hero_data_type' => null,
            'hero_data_text_enabled' => null,
            'hero_data_text' => null,
            'hero_data_icon' => null,
            'description_enabled' => null,
            'description' => string_field_formatter::class,
            'progress_bar_enabled' => null,
            'progress_bar' => null,
            'text_placeholders_enabled' => null,
            'text_placeholders' => null,
            'icon_placeholders_enabled' => null,
            'icon_placeholders' => null,
        ];
    }
}