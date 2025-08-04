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

use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * Entity class represent for table "prog_courseset"
 *
 * Properties:
 * @property-read int $id ID
 * @property int $programid
 * @property int $sortorder
 * @property int $competencyid
 * @property int $nextsetoperator
 * @property int $completiontype
 * @property int $mincourses
 * @property int $coursesumfield
 * @property int $coursesumfieldtotal
 * @property int $timeallowed
 * @property int $recurrencetime
 * @property int $recurcreatetime
 * @property int $contenttype
 * @property string $label
 * @property int $certifpath
 *
 * Relationships:
 * @property-read program $program
 *
 * @method static program_courseset_repository repository()
 */

class program_courseset extends entity {
    /**
     * @var string
     */
    public const TABLE = 'prog_courseset';

    /**
     * @return belongs_to|program
     */
    public function program(): belongs_to {
        return $this->belongs_to(program::class, 'programid', 'id');
    }
}