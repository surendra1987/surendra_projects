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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core
 */

namespace core\event;

/**
 * Log an event whenever we queue up an encryption job.
 */
class cipher_encryption_queued extends base {
    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('cipher_key_rollover_queued', 'core');
    }

    /**
     * @return cipher_key_added|base
     */
    public static function create_anonymous(): self {
        return self::create([
            'context' => \context_system::instance(),
            'userid' => self::USER_OTHER
        ]);
    }

    /**
     * Returns description of what happened.
     *
     * @return string
     */
    public function get_description(): string {
        return get_string('cipher_key_rollover_queued_description', 'core');
    }

    /**
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'c';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }
}
