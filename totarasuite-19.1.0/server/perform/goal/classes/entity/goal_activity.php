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

use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;

/**
 * Perform goal activity entity
 *
 * Properties:
 * @property-read int $id
 * @property int $goal_id Related goal ID
 * @property int|null $user_id User ID related to the activity
 * @property int $timestamp
 * @property string $activity_type Type code
 * @property string $activity_info Information text about the activity
 *
 * Relationships:
 * @property-read goal $goal Related goal
 * @property-read user $user The subject user for this goal
 */
class goal_activity extends entity {

    public const TABLE = 'perform_goal_activity';
    public const CREATED_TIMESTAMP = 'timestamp';

    /**
     * @return belongs_to
     */
    public function goal(): belongs_to {
        return $this->belongs_to(goal::class, 'goal_id');
    }

    /**
     * The user related to this activity.
     *
     * @return belongs_to
     */
    public function user(): belongs_to {
        return $this->belongs_to(user::class, 'user_id');
    }
}