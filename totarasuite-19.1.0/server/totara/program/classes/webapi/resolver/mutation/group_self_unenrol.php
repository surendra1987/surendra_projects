<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author  Rami Habib <rami.habib@totara.com>
 * @package totara_program
 */

namespace totara_program\webapi\resolver\mutation;

use core\entity\user;
use core\webapi\execution_context;
use core\webapi\middleware\require_advanced_feature;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use stdClass;
use Throwable;
use totara_program\assignment\group;
use totara_program\interactor\group_interactor;

/**
 * Handles the "totara_program_group_self_unenrol" GraphQL mutation
 */
final class group_self_unenrol extends mutation_resolver {
    /**
     * {@inheritDoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        try {
            $parsed = self::parse($args, $ec);
            if ($parsed->error) {
                return static::success_result(false, $parsed->error->message, $parsed->error->code);
            }

            $can_self_unenrol_result = group_interactor::from($parsed->group, $parsed->context)
                ->can_self_unenrol();

            if ($can_self_unenrol_result->success) {
                $parsed->group->remove_users([$parsed->user->id]);
                return static::success_result(true);
            } else {
                return static::success_result(false, $can_self_unenrol_result->message, $can_self_unenrol_result->code);
            }
        } catch (Throwable $throwable) {
            return static::success_result(false, $throwable->getMessage());
        }
    }

    /**
     * Parses the incoming graphql operation parameters.
     *
     * @param array<string,mixed> $args incoming arguments
     * @param execution_context $ec graphql execution context
     *
     * @return stdClass an object with these fields:
     *         - stdClass error: error (code, message) key value pairs if the
     *           parsing failed
     *         - group group: group to update or null if there was an error
     *         - user user: user to enrol or null if there was an error
     *         - ?context: context if any
     */
    private static function parse(
        array $args,
        execution_context $ec
    ): stdClass {
        $parsed = (object) [
            'error' => null,
            'group' => null,
            'user' => user::logged_in(),
            'context' => $ec->has_relevant_context()
                ? $ec->get_relevant_context()
                : null
        ];

        $root = $args['input'] ?? [];
        $group_id = (int)($root['id'] ?? 0);
        $assignment_id = (int)($root['assignment_id'] ?? 0);

        if ($group_id > 0) {
            $parsed->group = group::create_from_group_id($group_id);
        } elseif ($assignment_id > 0) {
            $parsed->group = group::create_from_id($assignment_id);
        }

        if (!$parsed->group) {
            $parsed->error = (object) [
                'code' => 'group:error:invalid_group_reference',
                'message' => get_string('group:error:invalid_group_reference', 'totara_program')
            ];
        }

        return $parsed;
    }

    /**
     * {@inheritDoc}
     */
    public static function get_middleware(): array {
        // Note middleware throws exceptions before self::resolve() runs. Which
        // is unfortunate; otherwise everything could have been nicely packaged
        // into the self::resolve() return result.
        return [
            new require_advanced_feature('programs'),
            new require_login(),
            new require_authenticated_user()
        ];
    }
}
