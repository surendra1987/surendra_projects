<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package core_enrol
 */

namespace core_enrol\event;

use core\entity\enrol;
use core\event\base;

class pre_user_enrolment_bulk_deleted extends base {

    /**
     * @inheritDoc
     */
    protected function init(): void {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = enrol::TABLE;
    }

    /**
     * Retrieve the IDs of the objects relevant to this event
     *
     * @returns array
     */
    public function get_notification_event_data(): array {
        return [
            'enrol_id' => $this->get_data()['objectid'],
            'user_enrolment_ids' => $this->get_data()['other']['user_enrolment_ids'],
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_name(): string {
        return get_string('pre_user_enrolment_bulk_deleted', 'core_enrol');
    }

    /**
     * @inheritDoc
     */
    public function get_description(): string {
        return "A batch of user enrolments for enrol with id '{$this->data['objectid']}' are about to be deleted in bulk";
    }
}