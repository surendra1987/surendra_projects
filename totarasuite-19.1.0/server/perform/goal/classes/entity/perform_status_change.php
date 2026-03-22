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
 * @author Scott Davies <scott.davies@totara.com>
 * @package perform_goal
 */

namespace perform_goal\entity;

use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;
use core\orm\query\builder;
use mod_perform\entity\activity\activity;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\subject_instance;
use mod_perform\models\activity\participant_source;
use totara_core\entity\relationship;

/**
 * perform_goal_perform_status_change entity.
 *
 * Properties:
 * @property-read int $id
 * @property int $goal_id
 * @property int|null $subject_user_id
 * @property string $status
 * @property float $current_value
 * @property int $activity_id
 * @property int $subject_instance_id
 * @property int|null $status_changer_user_id
 * @property int|null $status_changer_relationship_id
 * @property int $created_at
 *
 * Relationships:
 * @property-read goal $goal
 * @property-read user $subject_user
 * @property-read activity|null $activity
 * @property-read subject_instance|null $subject_instance
 * @property-read user|null $status_changer_user
 * @property-read relationship|null $status_changer_relationship
 * @property-read participant_instance|null $participant_instance
 */
class perform_status_change extends entity {
    /**
     * @var string
     */
    public const TABLE = 'perform_goal_perform_status_change';

    /**
     * @var string
     */
    public const CREATED_TIMESTAMP = 'created_at';

    /**
     * Returns user whose goal status got changed
     *
     * @return belongs_to
     */
    public function subject_user(): belongs_to {
        return $this->belongs_to(user::class, 'subject_user_id');
    }

    /**
     * Personal goal.
     *
     * @return belongs_to
     */
    public function goal(): belongs_to {
        return $this->belongs_to(goal::class, 'goal_id');
    }

    /**
     * Associated activity for this status change
     *
     * @return belongs_to
     */
    public function activity(): belongs_to {
        return $this->belongs_to(activity::class, 'activity_id');
    }

    /**
     * Associated subject instance for this status change
     *
     * @return belongs_to
     */
    public function subject_instance(): belongs_to {
        return $this->belongs_to(subject_instance::class, 'subject_instance_id');
    }

    /**
     * The user changing the status
     *
     * @return belongs_to
     */
    public function status_changer_user(): belongs_to {
        return $this->belongs_to(user::class, 'status_changer_user_id');
    }

    /**
     * The status changer relationship for this rating
     *
     * @return belongs_to
     */
    public function status_changer_relationship(): belongs_to {
        return $this->belongs_to(relationship::class, 'status_changer_relationship_id');
    }

    /**
     * Participant instance for this record
     *
     * @return belongs_to
     */
    public function participant_instance(): belongs_to {
        $table = '"' . participant_instance::TABLE . '"';
        return $this
            ->belongs_to(participant_instance::class, 'subject_instance_id', 'subject_instance_id')
            ->join([self::TABLE, 'pr'], function (builder $builder) use ($table) {
                $builder->where_field('status_changer_relationship_id', "{$table}.core_relationship_id")
                    ->where_field('status_changer_user_id', "{$table}.participant_id")
                    ->where("{$table}.participant_source", participant_source::INTERNAL)
                    ->where_not_null('subject_instance_id')
                    ->where_not_null('status_changer_user_id')
                    ->where_not_null('status_changer_relationship_id');
            });
    }
}
