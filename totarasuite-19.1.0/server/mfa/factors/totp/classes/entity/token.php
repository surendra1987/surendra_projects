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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package mfa_totp
 */

namespace mfa_totp\entity;

use core\orm\entity\entity;

/**
 * MFA totp used token.
 *
 * @property-read int $id
 * @property string $token
 * @property int $user_id
 * @property-read int $created_at
 */
class token extends entity {
    public const TABLE = 'mfa_totp_used_token';
    public const CREATED_TIMESTAMP = 'created_at';
}
