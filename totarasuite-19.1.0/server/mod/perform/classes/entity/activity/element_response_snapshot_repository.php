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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_perform
 */

namespace mod_perform\entity\activity;

use core\orm\entity\repository;
use core\orm\query\builder;
use mod_perform\state\participant_instance\closed as instance_closed;
use mod_perform\state\participant_section\closed as section_closed;

/**
 * Custom repository for section_element_response object snapshot entities.
 */
class element_response_snapshot_repository extends repository {

    /**
     * Restricts the snapshot repository to just snapshots that are part of an open section_element_reponse.
     *
     * @return self
     */
    public function part_of_open_response(): self {
        /**
         * SELECT * FROM element_response_snapshot ers
         *      JOIN element_response er ON er.id = ers.response_id
         *      JOIN section_element se ON se.id = er.section_element_id
         *      JOIN section s ON s.id = se.section_id
         *      JOIN participant_section ps ON ps.section_id = s.id
         *          AND ps.participant_instance_id = er.participant_instance_id
         *      JOIN participant_instance pi ON pi.id = er.participant_instance_id
         *      WHERE ps.availability <> 10 AND pi.availability <> 10
         */
        return $this
            ->join([element_response::TABLE, 'element_response'], 'response_id', '=', 'id')
            ->join([section_element::TABLE, 'section_element'], 'element_response.section_element_id', '=', 'id')
            ->join([section::TABLE, 'section'], 'section_element.section_id', '=', 'id')
            ->join([participant_section::TABLE, 'participant_section'], function (builder $builder) {
                $builder->where_field('section_id', '=', 'section.id')
                    ->where_field('participant_instance_id', '=', 'element_response.participant_instance_id');
            })
            ->join([participant_instance::TABLE, 'participant_instance'], 'element_response.participant_instance_id', '=', 'id')
            ->where('participant_section.availability', '!=', section_closed::get_code())
            ->where('participant_instance.availability', '!=', instance_closed::get_code());
    }
}
