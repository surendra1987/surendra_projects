<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_program
 */

namespace totara_program\webapi\resolver\mutation;

use coding_exception;
use core\entity\user;
use core\webapi\execution_context;
use core\webapi\middleware;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use totara_program\clone_program_prototype;
use totara_program\program;


class clone_program extends mutation_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return mixed|program
     */
    public static function resolve(array $args, execution_context $ec) {
        $input = $args['input'];
        $clone_sections = $input['clone_sections'];

        $possible_sections = clone_program_prototype::POSSIBLE_SECTIONS;

        // validate a clone_sections
        $clone_sections = array_intersect($possible_sections, $clone_sections);
        if (!count($clone_sections)) {
            throw new coding_exception(
                "clone_sections must include at least one of: " . join(', ', $possible_sections)
            );
        }

        // find the original program
        $origin_program = new program($input['program_id']);
        $origin_program->check_advanced_feature();

        require_capability('totara/program:cloneprogram', $origin_program->get_context(), (user::logged_in())->id);

        // Check whether the user can view the program.
        if (!$origin_program->is_viewable()) {
            throw new \invalid_parameter_exception('programid');
        }

        return (new clone_program_prototype($origin_program, $clone_sections))
            ->clone()
            ->post_clone()
            ->get_cloned_program();
    }

    /**
     * @return array|middleware[]
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
        ];
    }
}