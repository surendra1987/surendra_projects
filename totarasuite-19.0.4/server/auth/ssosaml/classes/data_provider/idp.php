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

use auth_ssosaml\entity\idp as idp_entity;
use auth_ssosaml\model\idp as idp_model;

/**
 * Data provider class for Identity Providers
 */
class idp {

    /**
     * Get all configured Identity providers
     *
     * @return iterable|idp_model[]
     */
    public static function get_all(): iterable {
        return idp_entity::repository()
            ->order_by('id')
            ->get()
            ->map_to(idp_model::class);
    }

    /**
     * Get all enabled Identity providers
     *
     * @return iterable|idp_model[]
     */
    public static function get_enabled(): iterable {
        return idp_entity::repository()
            ->where('status', 1)
            ->order_by('id')
            ->get()
            ->map_to(idp_model::class);
    }

    /**
     * Return all enabled and visible IdPs
     *
     * @return iterable|idp_model[]
     */
    public static function get_visible(): iterable {
        return idp_entity::repository()
            ->where('status', 1)
            ->where('login_hide', 0)
            ->order_by('id')
            ->get()
            ->map_to(idp_model::class);
    }
}
