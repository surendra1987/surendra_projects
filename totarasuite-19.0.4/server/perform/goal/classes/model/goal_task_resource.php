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

namespace perform_goal\model;

use core\orm\entity\model;
use perform_goal\entity\goal_task_resource as goal_task_resource_entity;
use perform_goal\model\goal_task_type\type;

/**
 * Perform goal task resource model.
 *
 * Properties:
 * @property-read int $id
 * @property-read int $task_id
 * @property-read int $created_at
 *
 * Relations:
 * @property-read goal_task $task
 * @property-read type $type
 * @property-read string $resource_custom_data
 */
class goal_task_resource extends model {
    /** @var string[] */
    protected $entity_attribute_whitelist = [
        'id',
        'task_id',
        'created_at',
        'resource_type',
        'resource_id',
    ];

    /** @var string[] */
    protected $model_accessor_whitelist = [
        'task',
        'type',
        'resource_custom_data'
    ];

    /**
     * Create a goal task resource.
     *
     * @param goal_task $task parent task.
     * @param type $type resource type.
     *
     * @return self the created resource.
     */
    public static function create(goal_task $task, type $type): self {
        $attributes = [
            'task_id' => $task->id,
            'resource_id' => $type->resource_id(),
            'resource_type' => $type->code()
        ];

        $entity = (new goal_task_resource_entity($attributes))
            ->save()
            ->refresh();

        return self::load_by_entity($entity);
    }

    /**
     * Returns the resource model associated with the given parent task.
     *
     * @param goal_task $parent the parent task to look up.
     *
     * @return ?self the resource if any.
     */
    public static function for_task(goal_task $parent): ?self {
        // Note the assumption here there is only one resource per task.
        $entity = goal_task_resource_entity::repository()
            ->where('task_id', '=', $parent->id)
            ->one();

        return $entity ? self::load_by_entity($entity) : null;
    }

    /**
     * @inheritDoc
     */
    protected static function get_entity_class(): string {
        return goal_task_resource_entity::class;
    }

    /**
     * Delete this goal task resource. After this operation, this model is not
     * valid.
     */
    public function delete(): void {
        $this->entity->delete();
    }

    /**
     * Get the related goal task instance.
     *
     * @return goal_task the parent goal task.
     */
    public function get_task(): goal_task {
        return goal_task::load_by_id($this->task_id);
    }

    /**
     * Get the related goal task type.
     *
     * @return type the goal task type.
     */
    public function get_type(): type {
        return type::by_code(
            $this->entity->resource_type, $this->entity->resource_id
        );
    }

    /**
     * Update the goal task resource.
     *
     * @param type $type the new resource type.
     *
     * @return self this object.
     */
    public function update(type $type): self {
        $this->entity
            ->set_attribute('resource_id', $type->resource_id())
            ->set_attribute('resource_type', $type->code())
            ->save()
            ->refresh();

        return $this;
    }

    /**
     * Returns custom data associated with this resource.
     *
     * @return array<string,mixed> custom data as a set of key value pairs.
     */
    public function get_resource_custom_data(): array {
        return $this->get_type()->custom_data($this);
    }
}
