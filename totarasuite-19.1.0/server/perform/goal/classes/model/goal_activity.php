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

namespace perform_goal\model;

use coding_exception;
use core\entity\user;
use core\orm\entity\entity;
use core\orm\entity\model;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_activity as goal_activity_entity;

/**
 * Perform goal activity model class
 *
 * Properties:
 * @property-read int $id
 * @property-read int $goal_id
 * @property-read int|null $user_id
 * @property-read int $timestamp
 * @property-read string $activity_type
 * @property-read string $activity_info
 *
 * Relations:
 * @property-read goal $goal
 * @property-read user $user
 *
 */
class goal_activity extends model {

    /** @var int Maximum string length for the activity_type property */
    private const TYPE_MAX_LENGTH = 100;

    /** @var goal_activity_entity */
    protected $entity;

    /** @var string[] */
    protected $entity_attribute_whitelist = [
        'id',
        'goal_id',
        'user_id',
        'timestamp',
        'activity_type',
    ];

    /** @var string[] */
    protected $model_accessor_whitelist = [
        'goal',
        'user',
        'activity_info',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return goal_activity_entity::class;
    }

    /**
     * Creates a new goal_activity record and returns a model instance for it.
     *
     * @param int $goal_id
     * @param int|null $user_id
     * @param string $activity_type
     * @param string $activity_info
     * @return goal_activity
     * @throws coding_exception
     */
    public static function create(
        int $goal_id,
        ?int $user_id,
        string $activity_type,
        string $activity_info
    ): goal_activity {
        $activity_type = trim($activity_type);
        $activity_info = trim($activity_info);

        self::validate_goal_id($goal_id);
        self::validate_type($activity_type);
        self::validate_info($activity_info);
        self::validate_user_id($user_id);

        $entity = new goal_activity_entity();
        $entity->goal_id = $goal_id;
        $entity->user_id = $user_id;
        $entity->activity_type = $activity_type;
        $entity->activity_info = $activity_info;
        $entity->timestamp = time();
        $entity->save();

        return self::load_by_id($entity->id);
    }

    /**
     * Get the related goal model instance.
     *
     * @return goal
     */
    public function get_goal(): goal {
        return goal::load_by_id($this->goal_id);
    }

    /**
     * Get the related user entity.
     * Returns null when there is no (or an invalid) user_id for this activity,
     * which could happen in the case of system-created activity.
     *
     * @return user|entity|null
     */
    public function get_user(): ?user {
        return is_null($this->user_id)
            ? null
            : user::repository()->find($this->user_id);
    }

    /**
     * Get activity info.
     *
     * @return string
     */
    public function get_activity_info(): string {
        // For now just the property. This will be expanded later.
        return $this->entity->activity_info;
    }

    /**
     * Validate the info string for a goal activity.
     *
     * @param string $info
     * @return void
     * @throws coding_exception
     */
    private static function validate_info(string $info): void {
        if ($info === '') {
            throw new coding_exception('Goal activity must have an info string.');
        }
    }

    /**
     * Validate the type string for a goal activity.
     *
     * @param string $type
     * @return void
     * @throws coding_exception
     */
    private static function validate_type(string $type): void {
        if ($type === '') {
            throw new coding_exception('Goal activity must have a type.');
        }

        if (strlen($type) > self::TYPE_MAX_LENGTH) {
            throw new coding_exception('Goal activity type must not be longer than ' . self::TYPE_MAX_LENGTH . ' characters.');
        }
    }

    /**
     * Validate the given goal id for a goal activity.
     *
     * @param int|null $goal_id
     * @return void
     * @throws coding_exception
     */
    private static function validate_goal_id(?int $goal_id): void {
        if (!goal_entity::repository()->where('id', $goal_id)->exists()) {
            throw new coding_exception('Invalid goal_id: ' . $goal_id);
        }
    }

    /**
     * Validate the given user id for a goal activity.
     *
     * @param int|null $user_id
     * @return void
     * @throws coding_exception
     */
    private static function validate_user_id(?int $user_id): void {
        if (is_null($user_id)) {
            return; // null is fine.
        }

        if (!user::repository()->where('id', $user_id)->exists()) {
            throw new coding_exception('Invalid user_id: ' . $user_id);
        }
    }

    /**
     * Reload the properties on the model's entity.
     *
     * @return self
     */
    public function refresh(): self {
        $this->entity->refresh();

        return $this;
    }
}