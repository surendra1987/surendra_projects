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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\webapi\resolver\type;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\type_resolver;
use core_tag\model\tag as model;
use moodle_exception;

/**
 * Tag type resolver
 */
class tag extends type_resolver {

    /**
     * @param string $field
     * @param $source
     * @param array $args
     * @param execution_context $ec
     * @return void
     */
    public static function resolve(string $field, $source, array $args, execution_context $ec) {
        global $CFG;

        if (!($source instanceof model)) {
            throw new coding_exception("Expecting the instance of " . model::class);
        }

        if (empty($CFG->usetags)) {
            throw new moodle_exception('Tag feature is not enabled.');
        }

        switch ($field) {
            case 'name':
                return $source->name;
            case 'rawname':
                return $source->rawname;
            default:
                throw new moodle_exception("Field '{$field}' was not supported yet.");
        }
    }
}