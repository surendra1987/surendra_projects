<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
use core\orm\entity\repository;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core\orm\query\exceptions\record_not_found_exception;
use perform_goal\entity\goal as goal_entity;
use perform_goal\model\goal;

/**
 * Interceptor that uses a goal reference structure in a payload to retrieve a
 * goal.
 */
class require_perform_goal implements middleware {
    // Key under which a retrieved goal is stored in the payload.
    public const GOAL_KEY = 'goal';

    // Default keys to access the payload's goal reference fields.
    public const DEF_REF_KEY = 'goal_reference';
    public const DEF_ID_KEY = 'id';
    public const DEF_IDN_KEY = 'id_number';

    /**
     * @var collection<mixed[]> metadata to use to look up goals.
     */
    private collection $metadata;

    /**
     * @var bool whether to store the retrieved goal's context in the payload's
     *      execution context.
     */
    private bool $set_relevant_context;

    /**
     * @var bool whether to throw an exception when a goal cannot be found.
     */
    private bool $throw_exception_on_missing_goal;

    /**
     * Virtual constructor.
     *
     * @param string $ref_key composite key to use to get a goal reference field
     *        in the payload. See the payload_value() method header to see how
     *        this is interpreted.
     * @param string $id_key tag to append to $ref_key when looking up a goal id
     *        value from the payload.
     * @param string $idn_key tag to append to $ref_key when looking up a goal
     *        id number value from the payload.
     * @param bool $set_relevant_context whether to store the retrieved goal's
     *        context in the payload's execution context.
     *
     * @return self the object instance.
     */
    public static function create(
        string $ref_key = self::DEF_REF_KEY,
        string $id_key = self::DEF_ID_KEY,
        string $idn_key = self::DEF_IDN_KEY,
        bool $set_relevant_context = true
    ): self {
        if (!$ref_key) {
            throw new coding_exception('no reference key provided');
        }
        if (!$id_key) {
            throw new coding_exception('no id key provided');
        }
        if (!$idn_key) {
            throw new coding_exception('no id number key provided');
        }

        $metadata = collection::new([
            ['id', "{$ref_key}.$id_key"],
            ['id_number', "{$ref_key}.$idn_key"]
        ]);

        return new self($metadata, $set_relevant_context);
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
     * @param collection<string[]> $metadata metadata to use when retrieving a
     *        goal. Each collection item is a [goal table column, payload lookup
     *        key] tuple.
     * @param bool $set_relevant_context whether to store the retrieved goal's
     *        context in the payload's execution context.
     */
    private function __construct(
        collection $metadata,
        bool $set_relevant_context
    ) {
        $this->metadata = $metadata;
        $this->set_relevant_context = $set_relevant_context;
        $this->throw_exception_on_missing_goal = true;
    }

    /**
     * Disables the default throw error on missing goal behavior. This means the
     * parent resolver has to deal with the missing goal.
     *
     * @return self this object.
     */
    public function disable_throw_exception_on_missing_goal(): self {
        $this->throw_exception_on_missing_goal = false;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handle(
        payload $payload,
        Closure $next
    ): result {
        $goal = $this->load_goal($payload);
        if (!$goal && $this->throw_exception_on_missing_goal) {
            throw new record_not_found_exception(
                get_string('invalidrecordunknown', 'error')
            );
        }

        // The set_relevant_context() call below gets really unhappy if it gets
        // the system context. However, many goals will be created at the system
        // context. Hence the check.
        $context = $goal ? $goal->get_context() : null;
        if ($this->set_relevant_context
            && $context
            && $context->contextlevel !== CONTEXT_SYSTEM
        ) {
            $payload->get_execution_context()->set_relevant_context($context);
        }

        $payload->set_variable(self::GOAL_KEY, $goal);

        return $next($payload);
    }

    /**
     * Retrieves a goal given the data in the incoming payload.
     *
     * @param payload $payload payload to parse.
     *
     * @return ?goal the goal if it was found.
     */
    private function load_goal(payload $payload): ?goal {
        $query = $this->metadata->reduce(
            function (repository $repo, array $tuple) use ($payload): repository {
                [$column, $payload_key] = $tuple;
                $value = self::payload_value($payload_key, $payload);

                return is_null($value) ? $repo : $repo->where($column, $value);
            },
            goal_entity::repository()
        );

        return $query->has_conditions() && $query->count() === 1
            ? goal::load_by_entity($query->one(false))
            : null;
    }
}
