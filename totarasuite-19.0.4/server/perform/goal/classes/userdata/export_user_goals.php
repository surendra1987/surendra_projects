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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

namespace perform_goal\userdata;

use context;
use core\orm\query\builder;
use perform_goal\entity\goal as perform_goal_entity;
use perform_goal\settings_helper;
use totara_userdata\userdata\export;
use totara_userdata\userdata\target_user;

/**
 * This user data item class deals with exporting values for Totara Goals (introduced in 2023).
 */
class export_user_goals extends goal_item {
    /**
     * @inheritDoc
     */
    public static function is_purgeable(int $userstatus) {
        return false;
    }

    /**
     * @inheritDoc
     */
    public static function is_exportable(): bool {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected static function export(target_user $user, context $context) {
        // Do not filter by the context in this query, as we are looking for all goal references to this user to export.
        $goals = perform_goal_entity::repository()
            ->where('user_id', $user->id)
            ->with('tasks')
            ->with('tasks.resource')
            ->get();

        $export = new export();
        $export->data = $goals->to_array();
        $export->files = static::get_files($user->id, $context->id);
        return $export;
    }

    /**
     * Get the goal files for the given user.
     *
     * @param int $user_id
     * @param int $context_id
     * @return array
     */
    protected static function get_files(int $user_id): array {
        $fs = get_file_storage();
        return builder::table('files')
            ->join([perform_goal_entity::TABLE, 'goal'], 'itemid', 'goal.id')
            ->where('goal.user_id', $user_id)
            ->where('component', settings_helper::get_component())
            ->where('filearea', settings_helper::get_filearea())
            ->where('filename', '!=', '.') // Exclude directories
            ->get()
            ->map(function (object $file) use ($fs) {
                return $fs->get_file_instance($file);
            })
            ->all(true);
    }
}