<?php
/*
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_perform
 */

namespace mod_perform\testing;

use core\collection;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\section_element as section_element_entity;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\section_element;
use mod_perform\models\response\element_validation_error;
use mod_perform\models\response\section_element_response;

/**
 * Test double for invalid element responses
 */
class section_element_response_doubles_invalid extends section_element_response {
    public $was_saved = false;

    /**
     * @throws \coding_exception
     */
    public function __construct() {
        $participant_instance_entity = new participant_instance_entity();
        $participant_instance_entity->id = 1;
        $participant_instance = new participant_instance($participant_instance_entity);

        $section_element_entity = new section_element_entity();
        $section_element_entity->set_id_attribute(1);
        $section_element = new section_element($section_element_entity);

        parent::__construct(
            $participant_instance,
            $section_element,
            null,
            new collection()
        );
    }

    /**
     * @return section_element_response
     */
    public function save(): section_element_response {
        $this->was_saved = true;
        return $this;
    }

    /**
     * @param $is_draft_validation
     * @return bool
     */
    public function validate_response($is_draft_validation = false): bool {
        $this->validation_errors = new collection([
            new element_validation_error(1, 'one'),
            new element_validation_error(2, 'two')
        ]);
        return false;
    }
}
