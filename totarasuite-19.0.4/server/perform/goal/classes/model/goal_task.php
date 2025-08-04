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

namespace perform_goal\model;

use coding_exception;
use core\orm\entity\model;
use perform_goal\model\goal as goal;
use perform_goal\model\goal_task_type\type;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\entity\goal as goal_entity;
use perform_goal\event\goal_task_created;
use perform_goal\event\goal_task_deleted;
use perform_goal\event\goal_task_progress_changed;
use perform_goal\event\goal_task_updated;

/**
 * Perform goal task model.
 *
 * Properties:
 * @property-read int $id
 * @property-read int $goal_id
 * @property-read string $description
 * @property-read int|null $completed_at
 * @property-read int $created_at
 * @property-read int $updated_at
 *
 * Relations:
 * @property-read bool $completed
 * @property-read goal $goal
 * @property-read goal_task_resource $resource
 * @property-read bool resource_can_view
 * @property-read bool resource_exists
 */

class goal_task extends model {

    /** @var goal_task_entity */
    protected $entity;

    /** @var string[] */
    protected $entity_attribute_whitelist = [
        'id',
        'goal_id',
        'description',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    /** @var string[] */
    protected $model_accessor_whitelist = [
        'completed',
        'goal',
        'resource_exists',
        'resource_can_view',
        'resource'
    ];

    /**
     * @var array
     */
    protected static $task_resources  = [];

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return goal_task_entity::class;
    }

    /**
     * Create a goal task
     *
     * @param int $goal_id
     * @param string|null $description
     * @return goal_task
     * @throws coding_exception
     */
    public static function create(int $goal_id, ?string $description = null): goal_task {
        global $DB;

        self::validate_goal($goal_id);
        $entity = (new goal_task_entity())
            ->set_attribute('goal_id', $goal_id)
            ->set_attribute('description', $description);

        $DB->transaction(
            function () use ($entity) {
                $entity->save()->refresh();

                $entity->goal
                    ->set_attribute('updated_at', $entity->created_at)
                    ->save();

                goal_task_created::create_from_instance($entity)->trigger();
            }
        );
        // Need to set a default value for unit tests correctness.
        self::$task_resources[$entity->id] = null;

        return self::load_by_entity($entity);
    }

    /**
     * Update the goal task
     *
     * @param ?string $description the new description.
     * @param ?type $resource new resource type.
     *
     * @return goal_task
     * @throws coding_exception
     */
    public function update(?string $description, ?type $resource): goal_task {
        global $DB;

        $DB->transaction(
            function () use ($description, $resource) {
                $current_resource = $this->get_resource(true);
                switch (true) {
                    case $resource && !$current_resource:
                        self::$task_resources[$this->id] = goal_task_resource::create($this, $resource);
                        break;

                    case $resource:
                        $current_resource->update($resource);
                        self::$task_resources[$this->id] = $current_resource;
                        break;

                    case !$resource && $current_resource:
                        $current_resource->delete();
                        unset(self::$task_resources[$this->id]);
                        break;

                    case !$resource && !$current_resource:
                        // Nothing to do.
                }

                $this->entity
                    ->set_attribute('description', $description)
                    ->save()
                    ->refresh();

                $this->entity->goal
                    ->set_attribute('updated_at', $this->entity->updated_at)
                    ->save();

                goal_task_updated::create_from_instance($this->entity)->trigger();
            }
        );

        return self::load_by_entity($this->entity);
    }

    /**
     * Indicates whether this task has been completed.
     *
     * @return bool true if the task has been completed.
     */
    public function get_completed(): bool {
        return !is_null($this->entity->completed_at);
    }

    /**
     * Sets the completion status of this task.
     *
     * @param bool $completed true if the task has been completed.
     *
     * @return goal_task the updated object.
     */
    public function set_completed(bool $completed): goal_task {
        global $DB;
        $already_completed = $this->get_completed();

        switch(true) {
            case $already_completed && $completed:
                return $this;

            case !$already_completed && !$completed:
                return $this;

            default:
                $this->entity->completed_at = $completed ? time() : null;

                $DB->transaction(
                    function () {
                        $this->entity->save()->refresh();

                        $this->entity->goal
                            ->set_attribute('updated_at', $this->entity->updated_at)
                            ->save();

                        goal_task_progress_changed::create_from_instance($this->entity)
                            ->trigger();
                    }
                );

                return self::load_by_entity($this->entity);
        }
    }

    /**
     * Delete goal task
     *
     * @return bool
     */
    public function delete(): bool {
        global $DB;

        $DB->transaction(
            function () {
                $event = goal_task_deleted::create_from_instance($this->entity);
                $goal = $this->entity->goal;

                // Cascading delete will also delete the records from the "perform_goal_task_resource" table
                $this->entity->delete();
                $goal->set_attribute('updated_at', time())->save();
                $event->trigger();
            }
        );
        return true;
    }

    /**
     * Get the related goal model instance.
     *
     * @return goal the parent goal.
     */
    public function get_goal(): goal {
        return goal::load_by_id($this->goal_id);
    }

    /**
     * Get the related resource.
     *
     * @param bool $force_fetch
     * @return ?goal_task_resource the resource if any.
     */
    public function get_resource(bool $force_fetch = false): ?goal_task_resource {
        // We want to only make a database query if we don't already have a goal_task_resource stored in the static variable.
        if (!array_key_exists($this->id, self::$task_resources) || $force_fetch) {
            self::$task_resources[$this->id] = goal_task_resource::for_task($this);
        }
        return self::$task_resources[$this->id];
    }

    /**
     * Reload the properties on the model's entity.
     *
     * @param bool $reload
     * @return self
     */
    public function refresh(bool $reload = false): self {
        if ($reload) {
            $class = static::get_entity_class();
            $this->entity = new $class($this->id);
            $this->get_resource(true);
        } else {
            $this->entity->refresh();
        }
        return $this;
    }

    /**
     * Validate a goal for exists
     *
     * @param int $goal_id
     * @return void
     * @throws coding_exception
     */
    private static function validate_goal(int $goal_id): void {
        if (!goal_entity::repository()->where('id', $goal_id)->exists()) {
            throw new coding_exception("Goal with id {$goal_id} does not exists");
        }
    }

    /**
     * @return bool
     */
    public function get_resource_can_view(): bool {
        $resource = $this->get_resource();
        return $resource ? $resource->type->authorized() : false;
    }

    /**
     * @return bool
     */
    public function get_resource_exists(): bool {
        $resource = $this->get_resource();
        return $resource ? $resource->type->resource_exists() : false;
    }
}
