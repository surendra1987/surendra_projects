<?php
/**
 * This file is part of Totara Perform
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\totara_notification;

final class constants {
    // Attribute names in the event data object passed around to CN resolvers,
    // and other CN abstractions.
    public const DATA_COMMENT_ID = 'comment_id';
    public const DATA_COMMENTER_UID = 'commenter_id';
    public const DATA_GOAL_ID = 'goal_id';
    public const DATA_GOAL_SUBJECT_UID = 'goal_subject_id';
    public const DATA_TIME_CREATED = 'create_time';

    /**
     * Default constructor.
     *
     * Private because this class is not meant to be instantiated.
     */
    private function __construct() {
        // EMPTY BLOCK
    }
}