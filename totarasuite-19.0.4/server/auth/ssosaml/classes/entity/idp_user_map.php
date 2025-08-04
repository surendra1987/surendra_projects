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

namespace auth_ssosaml\entity;

use core\orm\entity\entity;

/**
 * @property-read int $id ID
 * @property int $idp_id
 * @property int $user_id
 * @property int $status
 * @property string|null $code
 * @property int|null $code_expiry
 * @property-read int $created_at
 * @property-read int $updated_at
 */
class idp_user_map extends entity {
    public const TABLE = 'auth_ssosaml_idp_users';
    public const UPDATED_TIMESTAMP = 'updated_at';
    public const CREATED_TIMESTAMP = 'created_at';
    public const SET_UPDATED_WHEN_CREATED = true;

    /**
     * @param int|null|string $value
     * @return int
     * @codeCoverageIgnore
     */
    public function get_status_attribute($value = false): int {
        return intval($value);
    }
}
