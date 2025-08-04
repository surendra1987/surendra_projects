<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

namespace perform_goal\webapi\middleware;

use Closure;
use coding_exception;
use core\collection;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use perform_goal\entity\goal as goal_entity;
use perform_goal\entity\goal_task as goal_task_entity;
use perform_goal\model\goal_task;

/**
 * Interceptor that uses a goal task reference structure in a payload to get a
 * goal task.
 */
class require_perform_goal_task implements middleware {
    // Key under which a retrieved goal task model is stored in the payload.
    public const TASK_KEY = 'goal_task';

    // Default keys to access the payload's goal task reference fields.
    public const DEF_REF_KEY = 'goal_task_reference';
    public const DEF_TASK_ID_KEY = 'id';
    public const DEF_GOAL_ID_KEY = 'goal_id';
    public const DEF_ORDINAL_KEY = 'ordinal';

    /**
     * @var collection<string> metadata to use to look up goal tasks.
     */
    private collection $metadata;

    /**
     * @var bool whether to throw an exception when a goal task cannot be found.
     */
    private bool $throw_exception_on_missing_task;

    /**
     * Virtual constructor.
     *
     * @param string $ref_key composite key to use to get a goal task reference
     *        field in the payload. See the payload_value() method header to see
     *        how this is interpreted.
     * @param string $id_key tag to append to $ref_key when looking up a task id
     *        value from the payload.
     * @param string $goal_id_key tag to append to $ref_key when looking up a
     *        goal id value from the payload.
     * @param string $ord_key tag to append to $ref_key when looking up a task
     *        ordinal value from the payload.
     *
     * @return self the object instance.
     */
    public static function create(
        string $ref_key = self::DEF_REF_KEY,
        string $id_key = self::DEF_TASK_ID_KEY,
        string $goal_id_key = self::DEF_GOAL_ID_KEY,
        string $ord_key = self::DEF_ORDINAL_KEY
    ): self {
        if (!$ref_key) {
            throw new coding_exception('no reference key provided');
        }
        if (!$id_key) {
            throw new coding_exception('no task id key provided');
        }
        if (!$goal_id_key) {
            throw new coding_exception('no goal id key provided');
        }
        if (!$ord_key) {
            throw new coding_exception('no ordinal key provided');
        }

        $metadata = collection::new([
            "{$ref_key}.$id_key",
            "{$ref_key}.$goal_id_key",
            "{$ref_key}.$ord_key"
        ]);

        return new self($metadata);
    }

    /**
     * Extracts a value from the incoming payload.
     *
     * @param string $key composite key to use to extract a payload value. For
     *        example if the key is 'a.b.c', the payload value is retrieved from
     *        $payload['a']['b']['c'].
     * @param payload $payload the incoming payload to parse.
     *
     * @return mixed the extracted value.
     */
    private static function payload_value(
        string $key,
        payload $payload
    ) {
        $subkeys = explode('.', $key);

        $initial = array_shift($subkeys);
        $result = $payload->get_variable($initial);

        if ($result) {
            foreach ($subkeys as $subkey) {
                $result = $result[$subkey] ?? null;
            }
        }

        return $result;
    }

    /**
     * Default constructor.
     *
     * @param collection<string> $metadata metadata list of payload lookup keys
     *        in this order: task id, goal id, ordinal.
     */
    private function __construct(collection $metadata) {
        $this->metadata = $metadata;
        $this->throw_exception_on_missing_task = true;
    }

    /**
     * Disables the default throw error on missing task behavior. This means the
     * parent resolver has to deal with the missing task.
     *
     * @return self this object.
     */
    public function disable_throw_exception_on_missing_task(): self {
        $this->throw_exception_on_missing_task = false;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handle(
        payload $payload,
        Closure $next
    ): result {
        $task = $this->load_task($payload);
        if (!$task && $this->throw_exception_on_missing_task) {
            throw new coding_exception('Unknown task');
        }

        $payload->set_variable(self::TASK_KEY, $task);

        return $next($payload);
    }

    /**
     * Retrieves a goal task given the data in the incoming payload.
     *
     * @param payload $payload payload to parse.
     *
     * @return ?goal_task the goal task if it was found.
     */
    private function load_task(payload $payload): ?goal_task {
        [$task_id, $goal_id, $ordinal] = $this->metadata->map(
            fn(string $key): ?int => self::payload_value($key, $payload)
        );

        // Load by task id if it is present.
        if (!is_null($task_id)) {
            $task = goal_task_entity::repository()
                ->where('id', $task_id)
                ->one(false);

            return $task ? goal_task::load_by_entity($task) : null;
        }

        // Load by task order otherwise.
        if (is_null($goal_id) || is_null($ordinal)) {
            return null;
        }

        $goal = goal_entity::repository()->where('id', $goal_id)->one(false);
        if (!$goal) {
            return null;
        }

        // By default a goal returns tasks sorted by ids (see goal::get_tasks())
        // which also means they are sorted by creation time. This is why the
        // code below does not do any more sorting.
        $task = $goal->tasks->all()[$ordinal] ?? null;
        return $task ? goal_task::load_by_entity($task) : null;
    }
}
