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
 * @author David Curry <david.curry@totara.com>
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
use totara_program\program as program;
use totara_program\entity\program_courseset as program_courseset_entity;
use totara_program\event\program_contentupdated;
use totara_program\model\program_courseset as program_courseset_model;

/**
 * Graphql resolver to update the nextset operator for a given courseset
 */
class update_courseset_nextset_operator extends mutation_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return boolean
     */
    public static function resolve(array $args, execution_context $ec) {
        $input = $args['input'];

        // Make sure we have a program.
        if (empty($input['program_id'])) {
            throw new invalid_parameter_exception('Missing program id');
        }
        $program_id = $input['program_id'];

        // Make sure it's a valid program.
        if (!new program($program_id)) {
            throw new coding_exception("Invalid program.");
        }

        // Make sure we have a courseset.
        if (empty($input['courseset_id'])) {
            throw new invalid_parameter_exception('Missing courseset id');
        }
        $courseset_id = $input['courseset_id'];

        // Make sure it is a valid courseset.
        $row = program_courseset_entity::repository()->where('id', $courseset_id);
        if (!program_courseset_entity::repository()->where_exists($row->get_builder())) {
            throw new coding_exception("Course set does not exist.");
        }

        // Make sure we have a next set operator.
        if (empty($input['nextset_operator'])) {
            throw new invalid_parameter_exception('Missing nextset operator');
        }
        $nextset_operator = $input['nextset_operator'];

        /** @var program_courseset_entity $programe_courseset */
        $courseset_entity = new program_courseset_entity($courseset_id);

        // Make sure the courseset belongs to the given program.
        if ($courseset_entity->programid != $program_id) {
            throw new coding_exception("Invalid course set. The course set does not belong to the given program.");
        }

        // We've loaded all our data and done all the checks, do the update.
        $courseset_model = program_courseset_model::from_entity($courseset_entity);
        $courseset_model->update_nextset_operator($nextset_operator);

        // Get all course sets in the program
        $course_set_ids = program_courseset_entity::repository()
            ->select('id')
            ->where('programid', $program_id)
            ->get()
            ->to_array();

        // Trigger the content updated event.
        $dataevent = array('id' => $program_id, 'other' => array('coursesets' => $course_set_ids));
        program_contentupdated::create_from_data($dataevent)->trigger();

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
