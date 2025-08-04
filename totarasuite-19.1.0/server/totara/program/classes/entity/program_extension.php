<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  Gihan Hewaralalage <gihan.hewaralalage@totaralearning.com>
 * @package totara_program
 */

namespace totara_program\entity;

use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * Entity class represent for table "prog_exception"
 *
 * Properties:
 * @property-read int $id ID
 * @property int $programid
 * @property int $userid
 * @property int $extensiondate
 * @property string $extensionreason
 * @property int $status
 * @property string $reasonfordecision
 *
 * Relationships:
 * @property-read program $program
 * @property-read user $user
 *
 * @method static program_extension_repository repository()
 */
class program_extension extends entity {
    /**
     * @var string
     */
    public const TABLE = 'prog_extension';

    /**
     * @return belongs_to|program
     */
    public function program(): belongs_to {
        return $this->belongs_to(program::class, 'programid', 'id');
    }

    /**
     * @return belongs_to|user
     */
    public function user(): belongs_to {
        return $this->belongs_to(user::class, 'userid', 'id');
    }
}