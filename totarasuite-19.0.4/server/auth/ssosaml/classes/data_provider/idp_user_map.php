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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\data_provider;

use auth_ssosaml\entity\idp_user_map as map_entity;
use auth_ssosaml\model\idp_user_map as map_model;

/**
 * Data provider class for Identity Providers
 */
class idp_user_map {

    /**
     * @param int $idp_id
     * @param int $user_id
     * @return map_model|null
     */
    public static function get_latest_for_user(int $idp_id, int $user_id): ?map_model {
        return map_entity::repository()
            ->where('idp_id', $idp_id)
            ->where('user_id', $user_id)
            ->where('status', '<>', map_model::STATUS_INVALID)
            ->order_by('id', 'DESC')
            ->get()
            ->map_to(map_model::class)
            ->first();
    }
}
