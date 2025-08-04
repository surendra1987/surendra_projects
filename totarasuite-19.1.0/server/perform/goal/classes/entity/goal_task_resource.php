<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\entity;

use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * Perform goal task resource entity.
 *
 * Properties:
 * @property-read int $id
 * @property int $task_id The parent task
 * @property int|null $resource_id The associated resource record id eg course
 *           id. If null, then the associated resource record was deleted since
 *           this resource was created.
 * @property int $resource_type The associated resource type
 * @property-read int $created_at
 */
class goal_task_resource extends entity {
    /**
     * @var string
     */
    public const TABLE = 'perform_goal_task_resource';

    /**
     * @var string
     */
    public const CREATED_TIMESTAMP = 'created_at';

    /**
     * The task for this resource.
     *
     * @return belongs_to
     */
    public function task(): belongs_to {
        return $this->belongs_to(goal_task::class, 'task_id');
    }
}