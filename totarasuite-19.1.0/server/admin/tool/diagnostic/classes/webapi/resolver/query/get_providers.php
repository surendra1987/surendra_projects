<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <gary.liu@totara.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_site_admin;
use core\webapi\query_resolver;
use tool_diagnostic\config_json_provider;
use tool_diagnostic\manager;

/**
 * Query for getting all the diagnostic providers.
 */
final class get_providers extends query_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(array $args, execution_context $ec): array {
        $config_provider = new config_json_provider();
        return (new manager($config_provider, true))->get_all_provider_instances();
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_site_admin(),
        ];
    }

}
