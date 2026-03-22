<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package totara_program
 */

namespace totara_program\entity;

use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * Program group assignment to user entity.
 *
 * Properties:
 * @property-read int $id
 * @property int $prog_group_id The parent assignment group
 * @property int $user_id The user assigned to the assignment group
 *
 * Relationships:
 * @property-read prog_group $assignment_group
 * @property-read user $user
 */
class prog_group_user extends entity {
    /**
     * @var string
     */
    public const TABLE = 'prog_group_user';

    /**
     * The parent assignment group.
     *
     * @return belongs_to
     */
    public function assignment_group(): belongs_to {
        return $this->belongs_to(prog_group::class, 'prog_group_id');
    }

    /**
     * The assigned user.
     *
     * @return belongs_to
     */
    public function user(): belongs_to {
        return $this->belongs_to(user::class, 'user_id');
    }
}