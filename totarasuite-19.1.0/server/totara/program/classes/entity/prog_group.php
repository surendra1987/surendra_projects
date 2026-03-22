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

use core\orm\entity\entity;
use core\orm\entity\relations\has_many;

/**
 * Program group assignment entity.
 *
 * Properties:
 * @property-read int $id
 * @property string $name Identifies the assignment group
 * @property ?string $description Description about the assignment group
 * @property bool $can_self_enrol true if this assignment group can have self
 *           enrolled members.
 * @property bool $can_self_unenrol true if the members in this assignment group
 *           can unenrol themselves from the program.
 *
 * Relationships:
 * @property-read collection|prog_group_user[] $members
 */
class prog_group extends entity {
    /**
     * @var string
     */
    public const TABLE = 'prog_group';

    /**
     * Get users in this assignment group.
     *
     * @return has_many
     */
    public function group_users(): has_many {
        return $this->has_many(prog_group_user::class, 'prog_group_id');
    }
}
