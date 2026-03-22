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
 * @package hierarchy_organisation
 */

namespace hierarchy_organisation\entity;

use core\orm\entity\relations\has_many;
use totara_hierarchy\entity\hierarchy_type;

/**
 * @property string $shortname
 * @property string $description
 * @property int $timecreated
 * @property int $timemodified
 * @property int $usermodified
 * @property string $fullname
 * @property string $idnumber
 *
 * @property-read organisation_type_info_field $type_info_field Organisation type info field
 *
 * Organisation type entity
 */
class organisation_type extends hierarchy_type {

    public const TABLE = 'org_type';

    /**
     * Relationship with organisation type info field entities.
     *
     * @return has_many
     */
    public function type_info_field(): has_many {
        return $this->has_many(organisation_type_info_field::class, 'typeid');
    }

}