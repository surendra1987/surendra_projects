<?php
/**
 * This file is part of Totara Learn
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package hierarchy_position
 */

namespace hierarchy_position\entity;

use core\orm\entity\entity;

/**
 * @property string $shortname
 * @property int $typeid
 * @property string $datatype
 * @property string $description
 * @property int $sortorder
 * @property int $hidden
 * @property int $locked
 * @property int $required
 * @property int $forceunique
 * @property string $defaultdata
 * @property string $param1
 * @property string $param2
 * @property string $param3
 * @property string $param4
 * @property string $param5
 * @property string $fullname
 *
 */
class position_type_info_field extends entity {

    public const TABLE = 'pos_type_info_field';

}