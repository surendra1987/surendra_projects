<?php
/**
 * This file is part of Totara LMS
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
 * @author Qingyang Liu <gary.liu@totara.com>
 * @package totara_mobile
 */


namespace totara_mobile\formatter;

use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\field\text_field_formatter;
use core\webapi\formatter\formatter;

/**
 * Formatter to format downloadable activity
 */
class mobile_downloadable_activity_formatter extends formatter {
    /**
     * @return array
     */
    protected function get_map(): array {
        return [
            'id' => null,
            'instanceid' => null,
            'name' => string_field_formatter::class,
            'intro' =>  function ($value, text_field_formatter $formatter) {
                return $formatter
                    ->set_pluginfile_url_options($this->context, 'mod_' . $this->object->get_modtype(), 'intro')
                    ->format($value);
            },
            'viewurl' => string_field_formatter::class,
            'introformat' => null,
            'attachments' => null,
            'completion'  => null,
            'showdescription'  => null,
            'completionenabled'  => null,
            'completionstatus'  => null,
            'downloadsize' => null,
        ];
    }
}