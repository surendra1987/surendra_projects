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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

namespace mod_perform\webapi\middleware;

use Closure;
use coding_exception;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use mod_perform\entity\activity\participant_instance as pi_entity;
use mod_perform\models\activity\participant_instance;

/**
 * Interceptor that uses a participant instance reference structure in a payload
 * to retrieve a participant instance.
 */
class require_participant_instance implements middleware {
    // Key under which a retrieved participant instance is stored in the payload.
    public const PI_KEY = 'participant_instance';

    // Default key to access the payload's participant instance id field.
    public const DEF_ID_KEY = 'participant_instance_id';

    // Default token key to access the payload's participant instance id field.
    public const DEF_TOKEN_KEY = 'token';

    /**
     * @var bool whether to throw an exception when a participant instance
     *      cannot be found.
     */
    private bool $throw_exception_if_absent;

    /**
     * Virtual constructor.
     *
     * @param string $id_key composite key to use to get a participant instance
     *        id field in the payload. See payload_value() method header to see
     *        how this is interpreted.
     * @param bool $set_relevant_context whether to store a retrieved participant
     *        instance's context in the payload's execution context.
     *
     * @return self the object instance.
     */
    public static function create(
        string $id_key = self::DEF_ID_KEY,
        bool $set_relevant_context = true
    ): self {
        if (!$id_key) {
            throw new coding_exception('no participant instance id key given');
        }

        return new static($id_key, $set_relevant_context);
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
    protected static function payload_value(
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
     * @param string $id_key composite key to use to get a participant instance
     *        id field in the payload.
     * @param bool $set_relevant_context whether to store the retrieved
     *        participant instance's context in the payload's execution context.
     */
    private function __construct(
        protected readonly string $id_key,
        private readonly bool $set_relevant_context
    ) {
        $this->throw_exception_if_absent = true;
    }

    /**
     * Disables the default throw error on missing participant instance behavior.
     * This means the parent resolver has to deal with the missing participant
     * instance.
     *
     * @return self this object.
     */
    public function disable_throw_exception_on_missing(): self {
        $this->throw_exception_if_absent = false;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function handle(
        payload $payload,
        Closure $next
    ): result {
        $pi = $this->load_pi($payload);
        if (!$pi && $this->throw_exception_if_absent) {
            $key = $this->id_key;
            throw new coding_exception("payload has invalid '$key' value");
        }

        if ($pi && $this->set_relevant_context) {
            $payload
                ->get_execution_context()
                ->set_relevant_context($pi->context);
        }

        $payload->set_variable(static::PI_KEY, $pi);
        return $next($payload);
    }

    /**
     * Retrieves a participant instance given the data in the incoming payload.
     *
     * @param payload $payload payload to parse.
     *
     * @return ?participant_instance the participant instance if it was found.
     */
    protected function load_pi(payload $payload): ?participant_instance {
        $id = static::payload_value($this->id_key, $payload);

        $entity = $id
            ? pi_entity::repository()->where('id', '=', $id)->one(false)
            : null;

        return $entity
            ? participant_instance::load_by_entity($entity)
            : null;
    }
}
