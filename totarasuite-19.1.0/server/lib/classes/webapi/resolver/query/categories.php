<?php
/*
 * This file is part of Totara Learn
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
 * @author Gihan Hewaralalage <gihan.hewaralalage@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\reopen_session_for_writing;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use coursecat;

class categories extends query_resolver {

    /**
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        if (!$ec->has_relevant_context()) {
            $ec->set_relevant_context(\context_user::instance($USER->id));
        }

        $category_list = [];

        // Note that this function only fetch all the categories that are available to be maintained
        // by the user only, no system categories in this list.
        $category_string_list = coursecat::make_categories_list();
        foreach ($category_string_list as $key => $value) {
            // Note: This takes care of visibility checks as long as the 3rd parameter is false.
            $category = coursecat::get($key, MUST_EXIST, false);
            $category_list[] = $category;
        }

        return $category_list;
    }

    public static function get_middleware(): array {
        return [
            reopen_session_for_writing::class,
            require_login::class
        ];
    }
}
