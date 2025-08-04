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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\webapi\resolver\mutation;

use auth_ssosaml\model\idp;
use auth_ssosaml\webapi\middleware\require_capability;
use auth_ssosaml\webapi\middleware\verify_idp;
use core\webapi\execution_context;
use core\webapi\mutation_resolver;

/**
 * GraphQL mutation to set IdP enabled status
 */
class set_idp_enabled extends mutation_resolver {
    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec): idp {
        /** @var idp $idp*/
        $idp = $args['idp']; // from verify_idp
        $status = $args['input']['status'];
        return $idp->update(['status' => $status], []);
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            require_capability::class,
            verify_idp::from('input.id'),
        ];
    }
}
