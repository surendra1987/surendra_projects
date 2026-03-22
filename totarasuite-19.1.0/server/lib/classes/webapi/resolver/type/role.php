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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package core
 */

namespace core\webapi\resolver\type;

use context_system;
use core\format;
use core\model\role as role_model;
use core\webapi\execution_context;
use core\webapi\formatter\field\string_field_formatter;
use core\webapi\type_resolver;
use coding_exception;
use moodle_exception;

/**
 * Role type resolver
 */
class role extends type_resolver {
    /**
     * @param string $field
     * @param $source
     * @param array $args
     * @param execution_context $ec
     * @return void
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        if (!$source instanceof role_model) {
            throw new coding_exception('Only role objects are accepted: ' . gettype($source));
        }

        $context = $ec->has_relevant_context() ? $ec->get_relevant_context() : context_system::instance();

        switch ($field) {
            case 'name':
                return $source->name;
            case 'shortname':
                return $source->shortname;
            case 'description':
                return (new string_field_formatter(format::FORMAT_PLAIN,  $context))->format($source->description);
            case 'id':
                return $source->id;
            default:
                throw new moodle_exception("Field '{$field}' was not supported yet.");
        }
    }
}