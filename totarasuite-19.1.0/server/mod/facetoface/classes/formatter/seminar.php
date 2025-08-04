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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

namespace mod_facetoface\formatter;

use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\formatter\field\text_field_formatter;
use core\webapi\formatter\formatter;
use mod_facetoface\webapi\field_mapping\seminar as seminar_field_mapping;

defined('MOODLE_INTERNAL') || die();

/**
 * Seminar formatter.
 */
class seminar extends formatter {

    /**
     * @inheritDoc
     */
    protected function get_map(): array {
        return [
            'id' => null,
            'shortname' => string_field_formatter::class,
            'name' => string_field_formatter::class,
            'description' => function ($value, text_field_formatter $formatter) {
                $component = 'mod_facetoface';
                $filearea = 'intro';

                $context = $this->context;
                return $formatter
                    ->set_text_format($this->object->introformat)
                    ->set_pluginfile_url_options($context, $component, $filearea)
                    ->format($value);
            },
            'timecreated' => date_field_formatter::class,
            'timemodified' => date_field_formatter::class,
            'course_id' => null,
            'events' => null,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function get_field(string $field) {
        // Map the GraphQL schema to the database fields
        $field = seminar_field_mapping::get_graphql_to_internal_column_mapping()[$field] ?? $field;

        return $this->object->$field;
    }

    /**
     * @inheritDoc
     */
    protected function has_field(string $field): bool {
        return array_key_exists($field, $this->get_map());
    }
}
