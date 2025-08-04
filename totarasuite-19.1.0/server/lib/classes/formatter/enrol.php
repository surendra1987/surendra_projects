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

use core\model\enrol as enrol_model;
use core\orm\formatter\entity_model_formatter;
use core\webapi\formatter\field\string_field_formatter;

/**
 * Maps an enrol_model into the GraphQL enrol type.
 *
 * @property enrol_model $object
 */
class enrol extends entity_model_formatter {
    private const ENROL_STATUSES = [
        ENROL_INSTANCE_ENABLED => 'ENROL_INSTANCE_ENABLED',
        ENROL_INSTANCE_DISABLED => 'ENROL_INSTANCE_DISABLED',
        ENROL_INSTANCE_DELETED => 'ENROL_INSTANCE_DELETED',
    ];

    /**
     * {@inheritdoc}
     */
    protected function get_map(): array {
        global $CFG;
        require_once($CFG->dirroot."/lib/enrollib.php");
        return [
            'id' => null,
            'enrol' => null,
            'status' => null,
            'course' => null,
            'sortorder' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function get_field(string $field) {
        switch ($field) {
            case 'status':
                return self::ENROL_STATUSES[$this->object->status] ?? null;
            default:
                return parent::get_field($field);
        }
    }
}
