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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\totara_engage\interactor;

use engage_course\totara_engage\resource\course;
use totara_engage\access\accessible;
use totara_engage\interactor\interactor;

class course_interactor extends interactor {
    /**
     * @param accessible $resource
     * @param int|null $actor_id
     * @return interactor
     */
    public static function create_from_accessible(accessible $resource, ?int $actor_id = null): interactor {
        if (!($resource instanceof course)) {
            throw new \coding_exception('Invalid accessible resource for course interactor');
        }

        return new self($resource->to_array(), $actor_id);
    }

    /**
     * No comments available
     *
     * @return bool
     */
    public function can_comment(): bool {
        return false;
    }

    /**
     * No reactions available
     *
     * @return bool
     */
    public function can_react(): bool {
        return false;
    }

    /**
     * No side panel available.
     *
     * @return bool
     */
    public function show_share_button(): bool {
        return false;
    }

}