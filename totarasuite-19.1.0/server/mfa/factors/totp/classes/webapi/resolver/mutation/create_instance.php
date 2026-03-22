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

namespace mfa_totp\webapi\resolver\mutation;

use core_mfa\model\instance;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core_mfa\webapi\middleware\require_can_register;
use mfa_totp\webapi\middleware\validate_no_instances;
use mfa_totp\webapi\middleware\validate_token;
use mfa_totp\webapi\middleware\validate_secret;

/**
 * GraphQL mutation to create a mfa instance
 */
class create_instance extends mutation_resolver {
    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_login::class,
            require_can_register::class,
            validate_no_instances::class,
            validate_secret::class,
            validate_token::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec): instance {
        global $USER;
        $secure_config = [
            'secret' => $args['input']['secret']
        ];
        return instance::create($USER->id, 'totp', null, [], $secure_config);
    }
}
