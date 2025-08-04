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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\entity;

use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;
use core\orm\entity\relations\has_one;

/**
 * Perform goal task entity
 *
 * Properties:
 * @property-read int $id
 * @property int $goal_id The goal used for this task
 * @property string|null $description Always in Weka format
 * @property int|null $completed_at Null means uncompleted task
 * @property-read int $created_at
 * @property int $updated_at
 *
 * Relations:
 * @property-read goal_task_resource|null $resource
 */
class goal_task extends entity {
    /**
     * @var string
     */
    public const TABLE = 'perform_goal_task';

    /**
     * @var string
     */
    public const CREATED_TIMESTAMP = 'created_at';

    /**
     * @var string
     */
    public const UPDATED_TIMESTAMP = 'updated_at';

    /**
     * @var bool
     */
    public const SET_UPDATED_WHEN_CREATED = true;

    /**
     * The goal for this task.
     *
     * @return belongs_to
     */
    public function goal(): belongs_to {
        return $this->belongs_to(goal::class, 'goal_id');
    }

    /**
     * Get the related goal task resource.
     *
     * @return goal_task_resource|null
     */
    public function resource(): has_one {
        return $this->has_one(goal_task_resource::class, 'task_id');
    }
}