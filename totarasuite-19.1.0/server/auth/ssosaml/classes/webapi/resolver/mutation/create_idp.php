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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\resolver\mutation;

use auth_ssosaml\model\idp;
use auth_ssosaml\webapi\middleware\openssl_check;
use auth_ssosaml\webapi\middleware\require_capability;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;

/**
 * GraphQL mutation to create a metadata
 */
class create_idp extends mutation_resolver {

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_capability::class,
            openssl_check::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec): idp {
        return idp::create([], []);
    }
}