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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\entity;

use core\collection;
use core\orm\entity\entity;
use core\orm\entity\relations\has_many;

/**
 * Perform goal category entity, associates a category name with a goaltype sub-plugin.
 *
 * Properties:
 * @property-read $id
 * @property string $plugin_name Plugin name
 * @property string $name Type name
 * @property string|null $id_number Optional unique identifier
 * @property bool $active Whether this type is selectable for new goals
 * @property collection|goal[] $goals
 *
 */
class goal_category extends entity {

    public const TABLE = 'perform_goal_category';

    /**
     * A goal category can have many goals in the goal table.
     *
     * @return has_many
     */
    public function goals(): has_many {
        return $this->has_many(goal::class, 'category_id')->order_by('id');
    }

    /**
     * @return bool
     * @internal called by the entity class
     */
    public function get_active_attribute(): bool {
        return $this->get_attributes_raw()['active'] ?? true;
    }

    /**
     * @param bool $value
     * @return bool
     * @internal called by the entity class
     */
    public function set_active_attribute(bool $value): bool {
        return (bool)$this->set_attribute_raw('active', $value);
    }
}