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

namespace perform_goal\model;

use coding_exception;
use mod_perform\constants;
use moodle_exception;
use core\orm\entity\repository;
use core\entity\user;
use core\orm\entity\model;
use mod_perform\models\activity\activity;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\status\status;
use totara_core\entity\relationship;
use mod_perform\models\activity\subject_instance;
use mod_perform\models\activity\participant_instance;
use perform_goal\model\status\status_helper;
use perform_goal\entity\perform_status_change as perform_status_change_entity;
use perform_goal\model\goal as goal_model;
use perform_goal\event\perform_goal_perform_status_changed;
use totara_core\relationship\relationship as relationship_model;

/**
 * perform_goal_perform_status_change model.
 *
 * Properties:
 * @property-read int $id
 * @property int $goal_id
 * @property int $subject_user_id
 * @property int $current_value
 * @property int $activity_id
 * @property int|null $subject_instance_id
 * @property int|null $status_changer_user_id
 * @property int|null $status_changer_relationship_id
 * @property int $created_at
 *
 * Relations:
 * @property-read goal $goal
 * @property-read status $status
 * @property-read user $subject_user
 * @property-read activity|null $activity
 * @property-read subject_instance|null $subject_instance
 * @property-read user|null $status_changer_user
 * @property-read string $status_changer_role
 * @property-read relationship|null $status_changer_relationship
 * @property-read participant_instance|null $participant_instance
 */
class perform_status_change extends model {
    /**
     * @var perform_status_change_entity
     */
    protected $entity;

    /**
     * @var string[]
     */
    protected $entity_attribute_whitelist = [
        'id',
        'goal_id',
        'subject_user_id',
        'current_value',
        'activity_id',
        'subject_instance_id',
        'status_changer_user_id',
        'status_changer_user',
        'status_changer_relationship_id',
        'created_at',
    ];

    /**
     * @var string[]
     */
    protected $model_accessor_whitelist = [
        'status',
        'status_changer_relationship',
        'status_changer_role',
        'activity',
        'subject_instance',
        'participant_instance',
    ];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return perform_status_change_entity::class;
    }

    /**
     * Get the current status (as a status instance) of this goal.
     *
     * @return status
     */
    public function get_status(): status {
        return status_helper::status_from_code($this->entity->status);
    }

    /**
     * Create a new change of status for a goal assignment
     * @param int $participant_instance_id
     * @param int $section_element_id
     * @param string $status
     * @param float $current_value
     * @param int|null $goal_id
     * @return static
     */
    public static function create(
        int $participant_instance_id,
        int $section_element_id,
        string $status,
        float $current_value,
        int $goal_id
    ): self {
        $participant_instance = participant_instance::load_by_id($participant_instance_id);
        $subject_instance = $participant_instance->subject_instance;
        $status_changer_relationship = $participant_instance->core_relationship_id;

        // Validate can_change_status here from patch in TL-38161? TODO
//        $section_element = section_element::load_by_id($section_element_id);

        if (static::get_existing_status($goal_id, $subject_instance->id, $status_changer_relationship) !== null) {
            throw new coding_exception(
                "A status has already been saved for subject instance {$subject_instance->id},"
                . " goal ID {$goal_id}."
            );
        }

        if (!static::is_status_value_valid($status)) {
            throw new coding_exception("The specified status value '{$status}' is not valid for the goal with ID {$goal_id}");
        }

        // Update an existing perform_goal record.
        $goal_entity = goal_entity::repository()->find($goal_id);
        if (!$goal_entity) {
            throw new moodle_exception("Could not update goal status for goal ID {$goal_id}");
        }
        $goal_entity = goal_entity::repository()->find($goal_id);
        $goal_model = goal_model::load_by_entity($goal_entity);
        $goal_model = $goal_model->update_progress($status, $current_value);

        // Create a new perform_goal_perform_status_change record.
        $entity = new perform_status_change_entity();
        $entity->goal_id = $goal_id;
        $entity->subject_user_id = $subject_instance->subject_user_id;
        $entity->status = $status;
        $entity->current_value = $current_value;
        $entity->activity_id = $subject_instance->activity->id;
        $entity->subject_instance_id = $subject_instance->id;
        $entity->status_changer_user_id = $participant_instance->participant_id;
        $entity->status_changer_relationship_id = $status_changer_relationship;
        $entity->save();

        perform_goal_perform_status_changed::create_from_instance($entity)->trigger();

        return self::load_by_id($entity->id);
    }

    /**
     * Get an already existing status for the subject instance, status_changer relationship and goal assignment.
     *
     * @param int $goal_id
     * @param int $subject_instance_id
     * @param int|null $status_changer_relationship_id Optional - if not specified gets the latest status across all relationships.
     * @return static|null
     */
    public static function get_existing_status(
        int $goal_id,
        int $subject_instance_id,
        int $status_changer_relationship_id = null
    ): ?self {
        $perform_status = perform_status_change_entity::repository()
            ->where('goal_id', $goal_id)
            ->where('subject_instance_id', $subject_instance_id)
            ->when(
                $status_changer_relationship_id !== null,
                static function (repository $repository) use ($status_changer_relationship_id) {
                    $repository->where('status_changer_relationship_id', $status_changer_relationship_id);
                }
            )
            ->order_by('created_at', 'DESC')
            ->order_by('id', 'DESC')
            ->first();

        return $perform_status ? static::load_by_entity($perform_status) : null;
    }

    /**
     * @param string $status
     * @return bool
     */
    protected static function is_status_value_valid(string $status): bool {
        if (!in_array($status, status_helper::all_status_codes())) {
            return false;
        }
        return true;
    }

    /**
     * Get the status_changer's role with respect to the user viewing the status and the status_changer relationship.
     *
     * @return string
     */
    public function get_status_changer_role(): string {
        $user = user::logged_in();

        if (!is_null($user)
            && (int)$user->id === (int)$this->status_changer_user_id
            && $this->status_changer_relationship->idnumber === constants::RELATIONSHIP_SUBJECT
        ) {
            return get_string('perform_review_goal_status_changer_you', 'perform_goal');
        }

        return $this->status_changer_relationship->name;
    }

    /**
     * Returns the activity.
     *
     * @return activity|null
     */
    public function get_activity(): ?activity {
        return $this->entity->activity ?
            activity::load_by_entity($this->entity->activity)
            : null;
    }

    /**
     * Returns the relationship of the status changer.
     *
     * In case the related participant instance got deleted it returns null.
     *
     * @return relationship_model|null
     */
    public function get_status_changer_relationship(): ?relationship_model {
        return $this->entity->status_changer_relationship
            ? relationship_model::load_by_entity($this->entity->status_changer_relationship)
            : null;
    }
}
