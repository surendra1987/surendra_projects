<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

namespace core_mfa\data_provider;

use core\collection;
use core_mfa\entity\instance as entity;
use core_mfa\model\instance as model;
use core_plugin_manager;

/**
 * Data provider for instances.
 */
class instance {
    /**
     * Get instances configured for a particular user.
     *
     * @param int $user_id
     * @return collection
     */
    public static function get_user_instances(int $user_id): collection {
        $enabled = core_plugin_manager::instance()->get_enabled_plugins('mfa') ?? [];
        return entity::repository()
            ->where('user_id', $user_id)
            ->where_in('type', $enabled)
            ->order_by('id', 'ASC')
            ->get()
            ->map_to(model::class);
    }

    /**
     * Delete all instances for user.
     *
     * @return void
     */
    public static function delete_user_instances(int $user_id): void {
        entity::repository()
            ->where('user_id', $user_id)
            ->delete();
    }

    /**
     * Get instances configured for a particular user.
     *
     * @param int $user_id
     * @param string $factor
     * @return collection
     */
    public static function get_user_instances_for_factor(int $user_id, string $factor): collection {
        return entity::repository()
            ->where('user_id', $user_id)
            ->where('type', $factor)
            ->order_by('id', 'ASC')
            ->get()
            ->map_to(model::class);
    }
}
