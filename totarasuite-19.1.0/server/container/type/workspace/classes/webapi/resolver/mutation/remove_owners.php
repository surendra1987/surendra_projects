<?php
/**
 * This file is part of Totara Engage
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package container_workspace
 */

namespace container_workspace\webapi\resolver\mutation;

use core\entity\user;
use core_container\factory;
use core\webapi\mutation_resolver;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_advanced_feature;
use container_workspace\webapi\middleware\workspace_availability_check;
use moodle_exception;
use Throwable;

/**
 * Handles the "container_workspace_remove_owners" mutation
 */
class remove_owners extends mutation_resolver {

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        $workspace_id = $args['workspace_id'];
        $errors = null;

        try {
            $errors = factory::from_id($workspace_id)
                ->remove_owners(self::get_user_ids($args), user::logged_in())
                ->map(self::format_error(...))
                ->all();
        } catch (Throwable $error) {
            $errors = [self::format_error($error->getMessage())];
        }

        return ['success' => empty($errors), 'errors' => $errors];
     }

    /**
     * {@inheritdoc}
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_advanced_feature('container_workspace'),
            new workspace_availability_check('workspace_id')
        ];
    }

    /**
     * Get owner ids to be removed.
     *
     * @param array $args
     *
     * @return int[]
     */
    private static function get_user_ids(array $args): array {
        $owner_ids = array_filter($args['owner_ids'], fn(?int $id): bool => !empty($id));

        return $owner_ids
            ? $owner_ids
            : throw new moodle_exception(
                'owner_removal_condition_failed:empty_list',
                'container_workspace'
            );
    }

    /**
     * Formats an error response.
     *
     * @param string $err error message.
     *
     * @return array the formatted error.
     */
    private static function format_error(string $err): array {
        return ['code' => '', 'message' => $err];
    }
}
