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

namespace mod_facetoface\webapi\reference;

use core\entity\user;
use core\exception\unresolved_record_reference;
use core\webapi\reference\base_record_reference;
use dml_exception;
use mod_facetoface\seminar;
use moodle_exception;
use stdClass;

/**
 * Seminar record reference. Used to find one seminar record by provided parameters
 */
class seminar_record_reference extends base_record_reference {

    /**
     * @inheritDoc
     */
    protected array $refine_columns = ['id', 'shortname'];

    /**
     * @inheritDoc
     */
    protected function get_table_name(): string {
        return 'facetoface';
    }

    /**
     * @inheritDoc
     */
    protected function get_entity_name(): string {
        return 'Seminar';
    }

    /**
     * @param array $ref_columns
     * @param user|null $actor
     * @return stdClass
     * @throws unresolved_record_reference
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function load_for_viewer(array $ref_columns = [], user $actor = null): stdClass {
        $actor = $actor ?? user::logged_in();
        $seminar_record = parent::load_for_viewer($ref_columns, $actor);
        $seminar = (new seminar())->map_instance($seminar_record);

        if (!has_capability('mod/facetoface:view', $seminar->get_context(), $actor->id ?? null)) {
            throw new unresolved_record_reference('Could not find seminar record reference or you do not have permissions.');
        }

        return $seminar_record;
    }
}
