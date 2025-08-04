<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Gihan Hewaralalage <gihan.hewaralalage@totara.com>
 * @package totara_program
 */

namespace totara_program\webapi\resolver\mutation;

use coding_exception;
use core\webapi\execution_context;
use core\webapi\middleware;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_prog_capability;
use core\webapi\mutation_resolver;
use invalid_parameter_exception;
use ProgramException;
use totara_program\content\course_set as content;
use totara_program\entity\program as program_entity;
use totara_program\entity\program_courseset as program_courseset_entity;
use totara_program\model\program as program_model;
use totara_program\model\program_courseset as program_courseset_model;
use totara_program\program as program;

class delete_courseset extends mutation_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return bool
     * @throws ProgramException
     * @throws coding_exception
     * @throws invalid_parameter_exception
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG;
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        $input = $args['input'];
        if (empty($input['id'])) {
            throw new invalid_parameter_exception('Missing courseset id');
        }
        $courseset_id = $input['id'];

        if (empty($input['program_id'])) {
            throw new invalid_parameter_exception('Missing program id');
        }
        $program_id = $input['program_id'];

        // Is it valid program?
        if (!new program($program_id)) {
            throw new \coding_exception("Invalid program.");
        }

        // Get the course set model
        $current_course_set_entity = new program_courseset_entity($courseset_id);
        $courseset_model = program_courseset_model::from_entity($current_course_set_entity);

        // Check course set exist
        $is_courseset_exist = program_courseset_model::is_courseset_exists($courseset_id);
        if (!$is_courseset_exist) {
            throw new \coding_exception("Course set does not exist.");
        }

        // Is this course set belong to the given program
        if ($current_course_set_entity->programid != $program_id) {
            throw new \coding_exception("Invalid course set. The course set does not belong to the given program.");
        }

        // Get previous and next course set ids.
        list($previous_courseset_id, $next_courseset_id) = $courseset_model->get_previous_and_next_courseset_ids();

        // Delete current course set.
        $courseset_model->delete();

        // If the course set that has been deleted is the last course set in the program,
        // the previous set's Nextsetoperator property must be set to 0. Otherwise, it is set to THEN.
        if ($next_courseset_id === null && $previous_courseset_id) {
            $previous_courseset = new program_courseset_entity($previous_courseset_id);
            $previous_courseset->nextsetoperator = 0;
            $previous_courseset->save();
        } else if ($next_courseset_id && $previous_courseset_id) {
            $previous_courseset = new program_courseset_entity($previous_courseset_id);
            $previous_courseset->nextsetoperator = content::NEXTSETOPERATOR_THEN;
            $previous_courseset->save();
        }

        // Make sure that an array of course sets is in order in terms of each
        // set's sort order property and reset the sort order properties to ensure
        // that it begins from 1 and there are no gaps in the order.
        $program_entity = new program_entity($program_id);
        $program_model = program_model::from_entity($program_entity);
        $program_model->reset_coursesets_sortorder();

        // Trigger the content updated event.
        $program_model->trigger_content_updated_event();

        return true;
    }

    /**
     * @return array|middleware[]
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_prog_capability('program_id', 'totara/program:configurecontent')
        ];
    }
}
