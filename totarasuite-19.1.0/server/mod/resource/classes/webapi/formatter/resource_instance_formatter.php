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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package mod_resource
 */

namespace mod_resource\webapi\formatter;

use core\webapi\formatter\field\string_field_formatter;
use totara_mobile\formatter\mobile_downloadable_activity_formatter;

class resource_instance_formatter extends mobile_downloadable_activity_formatter {
    /**
     * @return array
     */
    protected function get_map(): array {
        return array_merge(
            parent::get_map(),
            [
                'display_type' => string_field_formatter::class,
                'file' => null,
                'extra_info' => string_field_formatter::class,
            ]
        );
    }
}