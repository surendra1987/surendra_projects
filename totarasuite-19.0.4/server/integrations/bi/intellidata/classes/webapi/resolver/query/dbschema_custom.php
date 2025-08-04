<?php
/**
 * This file is part of Totara Learn
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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\webapi\resolver\query;

use bi_intellidata\services\config_service;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\services\dbschema_service;
use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_plugin_enabled;
use core\webapi\middleware\require_user_capability;
use core\webapi\query_resolver;
use external_api;

/**
 * Query to dbschema_custom for IB.
 */
class dbschema_custom extends query_resolver {

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');
        // Ensure the current user is allowed to run this function.
        external_api::validate_context(context_system::instance());

        // Update config in database.
        $config_service = new config_service(datatypes_service::get_all_datatypes());
        $config_service->setup_config(false);

        return [
            'items' => (new dbschema_service())->format_tables()
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata'),
            new require_user_capability('bi/intellidata:viewdbschema'),
        ];
    }
}