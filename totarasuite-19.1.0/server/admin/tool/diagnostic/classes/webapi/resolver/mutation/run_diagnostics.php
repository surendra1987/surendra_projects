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

namespace tool_diagnostic\webapi\resolver\mutation;

use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\middleware\require_site_admin;
use core\webapi\mutation_resolver;
use tool_diagnostic\config_json_provider;
use tool_diagnostic\manager;

/**
 * Mutation for updating all the settings for the config diagnostic provider.
 */
class run_diagnostics extends mutation_resolver {

    /**
     * @inheritdoc
     */
    public static function resolve(array $args, execution_context $ec) {
        $config_provider = new config_json_provider();
        $manager = new manager(
            $config_provider,
            true,
            $args['excluded_provider_ids'] ?? []
        );
        return $manager->run_diagnostics();
    }

    /**
     * @inheritdoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new require_site_admin()
        ];
    }

}
