<?php
/*
 * This file is part of Totara LMS
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package totara_core
 */

namespace totara_core\event;

use core\event\base;

defined('MOODLE_INTERNAL') || die();

/**
 * User unsuspended event.
 *
 * Note: this event is triggered right after
 *
 * @package totara_core
 * @author  Angela Kuznetsova <angela.kuznetsova@totara.com>
 */
class user_unsuspended extends base {
    /**
     * Create instance of event.
     *
     * @param \stdClass $user
     * @return user_unsuspended
     */
    public static function create_from_user(\stdClass $user): self {
        $data = array(
            'objectid' => $user->id,
            'context' => \context_user::instance($user->id),
            'other' => array(
                'username' => $user->username,
            )
        );
        $event = self::create($data);
        $event->add_record_snapshot('user', $user);

        return $event;
    }

    /**
     * Init method.
     *
     * @return void
     */
    protected function init(): void {
        $this->data['objecttable'] = 'user';
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('eventuserunsuspended', 'totara_core');
    }

    /**
     * Returns non-localised event description with id's for admin use only.
     *
     * @return string
     */
    public function get_description(): string {
        return 'User ' . $this->other['username'] . ' unsuspended';
    }

    /**
     * Custom validation.
     *
     * @return void
     * @throws \coding_exception
     */
    protected function validate_data(): void {
        parent::validate_data();
        if (!isset($this->other['username'])) {
            throw new \coding_exception('username must be set in $other.');
        }
    }
}