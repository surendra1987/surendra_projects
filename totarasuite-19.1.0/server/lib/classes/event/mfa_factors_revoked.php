<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core
 */

namespace core\event;

use core_mfa\entity\instance as instance_entity;

/**
 * Event triggered when an admin user's factors have been revoked.
 */
class mfa_factors_revoked extends base {

    protected function init() {
        $this->data['crud'] = 'd';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = instance_entity::TABLE;
    }


    /**
     * Create instance of the event.
     *
     * @param int $user_id
     *
     * @return self
     */
    public static function create_event(int $user_id): self {
        global $USER;
        $data = [
            'userid' => $user_id,
            'objectid' => $user_id,
            'contextid' => \context_user::instance($user_id)->id,
            'other' => [
                'actor_id' => $USER->id,
            ],
        ];

        return self::create($data);
    }

    public static function get_name(): string {
        return get_string('event_instances_revoked', 'core_mfa');
    }

    public function get_description(): string {
        $by_who = empty($this->data['other']['actor_id'])
            ? 'via CLI'
            : "by user with id '{$this->data['other']['actor_id']}'";

        return "The registered MFA factors for user with id '$this->userid' has been revoked $by_who";
    }
}
