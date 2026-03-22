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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rami Habib <rami.habib@totaralearning.com>
 * @package block_totara_trending
 */

namespace block_totara_trending\entity;

use block_totara_trending\repository\totara_trending_repository;
use core\orm\entity\entity;

/**
 * @property int            $id
 * @property string|null    $unique_id
 * @property int            $item_id
 * @property string|null    $component
 * @property string|null    $area
 * @property int            $counter
 * @property int            $time_created
 * @method static totara_trending_repository repository()
 */
final class totara_trending extends entity {
    /**
     * @var string
     */
    public const TABLE = 'ml_recommender_trending';

    /**
     * @var string
     */
    public const CREATED_TIMESTAMP = 'time_created';

    /**
     * @inheritDoc
     */
    public static function repository_class_name(): string {
        return totara_trending_repository::class;
    }

    /**
     * Custom handling for exists flag due to manual creation of entities with repository::from_records().
     *
     * @return bool
     */
    public function exists(): bool {
        if ($this->get_id_attribute() == null) {
            return false;
        } else {
            return $this->exists;
        }
    }
}
