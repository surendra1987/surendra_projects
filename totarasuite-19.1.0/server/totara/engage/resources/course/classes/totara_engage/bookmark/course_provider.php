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
namespace engage_course\totara_engage\bookmark;

use engage_course\totara_engage\resource\course;
use totara_engage\access\accessible;
use totara_engage\bookmark\provider;

final class course_provider extends provider {

    /**
     * @inheritDoc
     */
    public function get_item_instance(int $id): accessible {
        return course::from_resource_id($id);
    }
}