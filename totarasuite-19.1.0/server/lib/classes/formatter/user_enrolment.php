<?php
/**
 *
 * This file is part of Totara TXP
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
 * @package core_enrol
 */

namespace core\formatter;

use core\model\user_enrolment as user_enrolment_model;
use core\orm\formatter\entity_model_formatter;
use core\webapi\formatter\field\date_field_formatter;

/**
 * Maps a user_enrolment_model into the GraphQL user_enrolment type.
 *
 * @property user_enrolment_model $object
 */
class user_enrolment extends entity_model_formatter {
    private const ENROLMENT_STATUSES = [
        ENROL_USER_ACTIVE => 'ENROL_USER_ACTIVE',
        ENROL_USER_SUSPENDED => 'ENROL_USER_SUSPENDED'
    ];

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        global $CFG;
        require_once($CFG->dirroot."/lib/enrollib.php");
        return [
            'id' => null,
            'enrolment' => null,
            'timestart' => date_field_formatter::class,
            'timeend' => date_field_formatter::class,
            'timecreated' => date_field_formatter::class,
            'timemodified' => date_field_formatter::class,
            'modified_by' => null,
            'status' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case 'status':
                return self::ENROLMENT_STATUSES[$this->object->status] ?? null;
            default:
                return parent::get_field($field);
        }
    }
}
