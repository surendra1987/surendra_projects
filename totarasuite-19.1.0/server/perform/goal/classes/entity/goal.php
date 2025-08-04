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
use core\entity\context;
use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;
use core\orm\entity\relations\has_many;
use perform_goal\settings_helper;
use perform_goal\totara_comment\comment_resolver;
use totara_comment\entity\comment;

/**
 * Perform goal entity
 *
 * Properties:
 * @property-read int $id
 * @property int $category_id The goal_category used for this goal
 * @property int $context_id Related context ID
 * @property int $owner_id ID of the goal's owner (usually the goal creator)
 * @property int|null $user_id ID of the goal's subject user
 * @property string $name
 * @property string|null $id_number Optional unique identifier
 * @property string|null $description Always in Weka format
 * @property int $start_date
 * @property string $target_type
 * @property int $target_date
 * @property float $target_value
 * @property float $current_value
 * @property int $current_value_updated_at
 * @property string $status Status code
 * @property int $status_updated_at
 * @property int|null $closed_at Null means open
 * @property-read int $created_at
 * @property int $updated_at
 *
 * Relationships:
 * @property-read context $context Related context
 * @property-read user $owner The owner user for this goal
 * @property-read user $user The subject user for this goal
 * @property-read collection|goal_activity[] $activities Related goal_activity entities
 * @property-read goal_category $category
 * @property-read collection|goal_task[] $tasks Related goal_task entities
 * @property-read collection|comment[] $comments Related totara_comment entities
 */
class goal extends entity {
    /**
     * @var string
     */
    public const TABLE = 'perform_goal';

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
     * @return belongs_to
     */
    public function context(): belongs_to {
        return $this->belongs_to(context::class, 'context_id');
    }

    /**
     * The owner user for this goal.
     *
     * @return belongs_to
     */
    public function owner(): belongs_to {
        return $this->belongs_to(user::class, 'owner_id');
    }

    /**
     * The subject user for this goal.
     *
     * @return belongs_to
     */
    public function user(): belongs_to {
        return $this->belongs_to(user::class, 'user_id');
    }

    /**
     * A goal may have activities recorded in the goal_activity table.
     *
     * @return has_many
     */
    public function activities(): has_many {
        return $this->has_many(goal_activity::class, 'goal_id')->order_by('id');
    }

    /**
     * The category for this goal.
     *
     * @return belongs_to
     */
    public function category(): belongs_to {
        return $this->belongs_to(goal_category::class, 'category_id');
    }

    /**
     * A goal may have tasks recorded in the goal_task table.
     *
     * @return has_many
     */
    public function tasks(): has_many {
        return $this->has_many(goal_task::class, 'goal_id');
    }

    /**
     * A goal can have many comments.
     *
     * @return has_many
     */
    public function comments(): has_many {
        return $this->has_many(comment::class, 'instanceid')
            ->where('component', settings_helper::get_component())
            ->where('area', comment_resolver::AREA);
    }
}