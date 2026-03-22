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
namespace perform_goal\testing;

use coding_exception;
use core\entity\course as course_entity;
use core\entity\user;
use core\testing\component_generator;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_activity as goal_activity_entity;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\model\goal;
use perform_goal\model\goal_activity;
use perform_goal\model\goal_category;
use perform_goal\model\goal_task;
use perform_goal\model\goal_task_resource;
use perform_goal\model\goal_task_type\course_type;
use perform_goal\model\goal_task_type\type as goal_type;
use totara_comment\comment as totara_comment;
use totara_comment\entity\comment as comment_entity;
use totara_reaction\entity\reaction as reaction_entity;

/**
 * Perform goal generator
 */
class generator extends component_generator {

    /**
     * Create a goal_category
     *
     * @param goal_category_generator_config|null $data
     * @return goal_category
     */
    public function create_goal_category(goal_category_generator_config $data = null): goal_category {
        if (is_null($data)) {
            $data = goal_category_generator_config::new();
        }

        $goal_category = goal_category::create(
            $data->plugin_name,
            $data->name,
            $data->id_number
        );

        if ($data->active) {
            $goal_category->activate();
        }

        return $goal_category;
    }

    /**
     * Create a goal.
     *
     * @param goal_generator_config|null $data
     * @return goal
     */
    public function create_goal(goal_generator_config $data = null): goal {
        if (is_null($data)) {
            $data = goal_generator_config::new();
        }
        if ($data->user_id) {
            $data->context = \context_user::instance($data->user_id);
        }
        // Create goal.
        $goal = goal::create(
            $data->context,
            $data->goal_category,
            $data->name,
            $data->start_date,
            $data->target_type,
            $data->target_date,
            $data->target_value,
            $data->current_value,
            $data->status,
            $data->owner_id,
            $data->user_id,
            $data->id_number,
            $data->description
        );

        // Create goal activities if configured.
        if ($data->number_of_activities > 0) {
            for ($i = 1; $i <= $data->number_of_activities; $i ++) {
                $this->create_goal_activity(
                    goal_activity_generator_config::new(['goal_id' => $goal->id])
                );
            }
        }

        // Create goal tasks if configured.
        return $this->create_goal_tasks($goal, $data->number_of_tasks);
    }

    /**
     * Create a goal activity.
     *
     * @param goal_activity_generator_config $data
     *
     * @return goal_activity
     */
    public function create_goal_activity(goal_activity_generator_config $data): goal_activity {
        return goal_activity::create(
            $data->goal_id,
            $data->user_id,
            $data->activity_type,
            $data->activity_info,
        );
    }

    /**
     * Create tasks for the given goal.
     *
     * @param goal $goal parent goal.
     * @param int $no_of_tasks no of tasks to create for parent goal.
     *
     * @return goal the updated goal.
     */
    public function create_goal_tasks(goal $goal, int $no_of_tasks = 1): goal {
        $goal_id = $goal->id;
        $goal_name = $goal->name;

        if ($no_of_tasks > 0) {
            array_map(
                fn(int $i): goal_task => goal_task::create(
                    $goal_id, "$goal_name task#$i"
                ),
                range(1, $no_of_tasks)
            );
        }

        return $goal->refresh(true);
    }

    /**
     * Create a goal from behat parameters
     *
     * @param array $data
     * @return goal
     */
    public function create_goal_for_behat(array $data) {
        if (empty($data['id_number'])) {
            throw new coding_exception('id_number must exist for behat test');
        }
        $data_to_update = [];
        $dates = ['start_date', 'target_date', 'current_value_updated_at', 'status_updated_at', 'closed_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            if (array_key_exists($date, $data)) {
                $data_to_update[$date] = self::compute_timestamp($data[$date]);
                unset($data[$date]);
            }
        }
        if (!empty($data['owner'])) {
            $owner = user::repository()
                ->where('username', $data['owner'])
                ->one(true);
            $data['owner_id'] = $owner->id;
            unset($data['owner']);
        }
        if (!empty($data['username'])) {
            $user = user::repository()
                ->where('username', $data['username'])
                ->one(true);
            $data['user_id'] = $user->id;
            unset($data['username']);
        }

        // Only Weka format is supported for description, so embed the configured description text in Weka JSON here.
        if (!empty($data['description'])) {
            $data['description'] = '{"type":"doc","content":[{"type":"paragraph","content":[{"type":"text","text":"' . $data['description'] . '"}]}]}';
        }

        $config = goal_generator_config::new($data);
        $goal = $this->create_goal($config);
        if (!empty($data_to_update)) {
            $data_to_update['id'] = $goal->id;
            goal_entity::repository()->update_record($data_to_update);
        }
        return $goal->refresh(true);
    }

    /**
     * Create a goal activity from behat parameters
     *
     * @param array $data
     * @return goal_activity
     * @throws \coding_exception
     */
    public function create_goal_activity_for_behat(array $data) {
        if (empty($data['id_number'])) {
            throw new coding_exception('id_number must exist for behat test');
        }
        $goal_entity = goal_entity::repository()
            ->where('id_number', $data['id_number'])
            ->one(true);
        $data['goal_id'] = $goal_entity->id;
        unset($data['id_number']);
        /** @var goal $goal */
        $goal = goal::load_by_entity($goal_entity);
        $user = $goal->get_user();
        $data['user_id'] = $user->id ?? null;
        $data_to_update = [];
        if (array_key_exists('timestamp', $data)) {
            $data_to_update['timestamp'] = self::compute_timestamp($data['timestamp']);
            unset($data['timestamp']);
        }
        $config = goal_activity_generator_config::new($data);
        $goal_activity = $this->create_goal_activity($config);
        if (!empty($data_to_update)) {
            $data_to_update['id'] = $goal_activity->id;
            goal_activity_entity::repository()->update_record($data_to_update);
        }
        return $goal_activity->refresh();
    }

    /**
     * Create a goal task from behat parameters
     *
     * @param array $data
     * @return goal_task
     */
    public function create_goal_task_for_behat(array $data) {
        if (empty($data['goal_id_number'])) {
            throw new coding_exception('goal id_number must exist for behat test');
        }

        $goal_entity = goal_entity::repository()
            ->where('id_number', $data['goal_id_number'])
            ->one(true);
        $data['goal_id'] = $goal_entity->id;
        unset($data['goal_id_number']);

        $data_to_update = [];
        $dates = ['created_at', 'updated_at'];
        foreach ($dates as $date) {
            if (array_key_exists($date, $data)) {
                $data_to_update[$date] = self::compute_timestamp($data[$date]);
                unset($data[$date]);
            }
        }

        $completed_at = $data['completed_at'] ?? null;
        if ($completed_at) {
            $data_to_update['completed_at'] = $data_to_update['updated_at'] ?? self::compute_timestamp('now');
            unset($data['completed_at']);
        }

        if (!empty($data['resource_id_string'])) {
            $resource_type = $data['resource_type'] ?? null;
            if (!$resource_type) {
                throw new coding_exception('goal task resource type must be provided');
            }
            // We just use a course as a resource, in the future
            if ($resource_type == course_type::TYPECODE) {
                $course = course_entity::repository()
                    ->where('shortname', $data['resource_id_string'])
                    ->one();
                $data['resource_id'] = $course->id;
            }
        }
        unset($data['resource_id_string']);

        $config = goal_task_generator_config::new($data);
        $goal_task = $this->create_goal_task($config);
        if (!empty($data_to_update)) {
            $data_to_update['id'] = $goal_task->id;
            goal_task_entity::repository()->update_record($data_to_update);
        }

        $this->create_goal_task_resource($goal_task, $config);

        return $goal_task->refresh(true);
    }

    /**
     * Create task for the given goal.
     *
     * @param goal_task_generator_config $data
     * @return goal_task
     */
    public function create_goal_task(goal_task_generator_config $data): goal_task {
        if (!isset($data->goal_id)) {
            $goal = generator::instance()->create_goal(
                goal_generator_config::new([
                    'number_of_activities' => 1,
                    'number_of_tasks' => 0
                ])
            );
            $data->goal_id = $goal->id;
        }
        // Create goal task.
        return (goal_task::create(
            $data->goal_id,
            $data->description ?? null
        ))
        ->refresh(true);
    }

    /**
     * Create a goal task resource.
     *
     * @param goal_task $goal_task
     * @param goal_task_generator_config $config
     * @return goal_task_resource|null
     */
    public function create_goal_task_resource(goal_task $goal_task, goal_task_generator_config $config): ?goal_task_resource {
        if ($config->resource_type && $config->resource_id) {
            $type = goal_type::by_code($config->resource_type, $config->resource_id);
            return goal_task_resource::create($goal_task, $type);
        }
        return null;
    }

    /**
     * Translate the date/time field
     * String is passed to the DateTime constructor

     * @param string $date
     * @return int
     */
    private static function compute_timestamp(string $date): int {
        if (!empty($date) && !is_numeric($date)) {
            // This could mean that it is a format instead of normal returned value from `time()`.
            // Must be some kind of time format string that PHP is able to understand.
            // Otherwise, we should throw exception here to let the test just fail.
            try {
                // Using DateTime object, because it can throw exception, and so we could catch and fail the
                // behat step pretty much.
                $datetimeobject = new \DateTime($date);
                $date = $datetimeobject->getTimestamp();
            } catch (\Throwable $e) {
                throw new coding_exception("An exception occurred: {$e->getMessage()}'");
            }
        }
        return (int)$date;
    }

    /**
     * @param int $commentator_id
     * @param int $goal_id
     * @param array $override_data
     * @return totara_comment
     */
    public function create_goal_comment(int $commentator_id, int $goal_id, array $override_data = []): totara_comment {
        $fields = [
            'component' => 'perform_goal',
            'area' => 'goal_comment',
            'userid' => $commentator_id,
            'format' => 1,
            'instanceid' => $goal_id,
            'content' => 'example comment',
            'contenttext' => 'example comment text'
        ];

        $comment_entity = new comment_entity(array_merge($fields, $override_data));
        $comment_entity->save();

        return totara_comment::from_entity($comment_entity);
    }
}