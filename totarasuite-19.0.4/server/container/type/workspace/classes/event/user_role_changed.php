<?php
/**
 * This file is part of Totara Engage
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

namespace container_workspace\event;

use core\entity\user;
use core\event\base;
use container_workspace\workspace;

class user_role_changed extends base {

    /**
     * @inheritDoc
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'role_assignments';
    }

    /**
     * Create a data for a new event
     *
     * @param workspace $workspace
     * @param user $member
     * @param user $actor
     * @param array $other
     * @return self
     * @throws \coding_exception
     */
    public static function create_from_data(
        workspace $workspace,
        user $member,
        user $actor,
        array $other
    ): self {
        $data = [
            'objectid' => $workspace->get_id(),
            'relateduserid' => $member->id,
            'userid' => $actor->id,
            'other' => $other,
            'context' => $workspace->get_context(),
        ];

        return static::create($data);
    }

    /**
     * @inheritDoc
     */
    public static function get_name() {
        return get_string('event_member_role_changed', 'container_workspace');
    }

    /**
     * @inheritDoc
     */
    public function get_description() {
        return sprintf(
            "The '%s' role is assigned for the user with id '%d' of workspace id '%d' by the user with id '%d'",
            $this->other['role_name'],
            $this->relateduserid,
            $this->objectid,
            $this->userid
        );
    }

    /**
     * @inheritDoc
     */
    public function get_url() {
        return workspace::create_url($this->objectid, 'members');
    }
}
