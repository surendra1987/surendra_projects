<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Player <simon.player@totara.com>
 * @package totara_program
 */

namespace totara_program\assignments;

/**
 * Abstract class for completion_events implementations.
 */
abstract class completion_event {

    /**
     * @var int
     */
    protected $programid;

    public function __construct($programid = null) {
        if (isset($programid)) {
            $this->programid = (int)$programid;
        }
    }

    /**
     * Get enum representing event.
     *
     * @return int
     */
    abstract public function get_id(): int;

    /**
     * Get name of event.
     *
     * @return int
     */
    abstract public function get_name(): string;

    /**
     * Get JavaScript snippet.
     *
     * @return string
     */
    abstract public function get_script(): string;

    /**
     * Get item name that event refers to.
     *
     * @param int $instanceid
     * @return string
     */
    abstract public function get_item_name(int $instanceid): string;

    /**
     * Get string related to this completion type.
     *
     * @return string
     */
    abstract public function get_completion_string(): string;

    /**
     * Gets timestamp related to event, if any.
     *
     * @param int $userid
     * @param $assignobject
     * @return bool|int Timestamp or false
     */
    abstract public function get_timestamp(int $userid, $assignobject);
}