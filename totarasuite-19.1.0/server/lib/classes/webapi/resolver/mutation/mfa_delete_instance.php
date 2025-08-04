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
 * @package core_mfa
 */

namespace core\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\mutation_resolver;
use core_mfa\model\instance;
use core\webapi\middleware\require_mfa_instance_authorisation;

/**
 * GraphQL mutation to delete a mfa instance
 */
class mfa_delete_instance extends mutation_resolver {
    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_login::class,
            require_mfa_instance_authorisation::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec): bool {
        /** @var instance $instance */
        $instance = $args['instance'];
        $instance->delete();
        return true;
    }
}
