<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package mod_perform
 */

namespace mod_perform\entity\activity;

use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * Section element response object snapshot entity
 *
 * Properties:
 * @property-read int $id ID
 * @property int $response_id element response id this snapshot belongs to
 * @property string $item_type component name of object/record being snapshotted
 * @property int $item_id database id of object/record being snapshotted
 * @property string|null $snapshot JSON encoded snapshot data
 * @property-read int $created_at
 * @property-read int $updated_at
 *
 * Relationships:
 * @property-read element_response $element_response Parent response
 *
 */
class element_response_snapshot extends entity {
    public const TABLE = 'perform_element_response_snapshot';
    public const CREATED_TIMESTAMP = 'created_at';
    public const UPDATED_TIMESTAMP = 'updated_at';
    public const SET_UPDATED_WHEN_CREATED = true;

    /**
     * Get the parent response
     *
     * @return belongs_to
     */
    public function element_response(): belongs_to {
        return $this->belongs_to(element_response::class, 'response_id');
    }
}
