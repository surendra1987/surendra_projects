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

namespace totara_program\event;

use coding_exception;
use core\event\base;
use core\session\manager;
use dml_exception;
use lang_string;
use totara_program\program;

class program_cloned extends base {

    /**
     * @return lang_string|string
     * @throws coding_exception
     */
    public static function get_name() {
        return get_string('eventcloned', 'totara_program');
    }

    /**
     * @param program $from
     * @param program $to
     * @return base
     * @throws coding_exception|dml_exception
     */
    public static function create_for_operation(program $from, program $to): base {
        $data = [
            'objectid' => $to->id,
            'userid' => manager::get_realuser()->id,
            'other' => [
                'cloned_from_id' => $from->id,
                'is_certif' => $from->is_certif()
            ],
            'context' => $to->get_context(),
        ];

        return static::create($data);
    }

    /**
     * @return string
     */
    public function get_description(): string {
        $details = (object)$this->other;
        $program_from_id = $details->cloned_from_id;

        $name = $details->is_certif ? 'Certification' : 'Program';
        return "{$name} #{$this->objectid} was cloned from {$name} #{$program_from_id} by user {$this->userid}";
    }

    /**
     * Initialise the event data
     *
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = 'prog';
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}