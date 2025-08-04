<?php
/**
 * This file is part of Totara Core
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\webapi\resolver\query;

use bi_intellidata\services\export_service;
use context_system;
use core\webapi\middleware\require_plugin_enabled;
use core\webapi\middleware\require_user_capability;
use external_api;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use bi_intellidata\exception\invalid_live_data_exception;
use bi_intellidata\services\datatypes_service;

/**
 * Resolves a request for the query 'bi_intellidata_live_data'.
 */
class live_data extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        external_api::validate_context($context);

        $args = $args['input'];
        if (!isset($args['datatype'])) {
            throw new invalid_live_data_exception('Datatype parameter missing.');
        }
        $params['datatype'] = trim($args['datatype']);
        if (!in_array($params['datatype'], datatypes_service::LIVE_DATA_DATATYPES)) {
            throw new invalid_live_data_exception('Invalid datatype specified.');
        }

        $exportservice = new export_service();
        $datatype_obj = $exportservice->get_datatype($params['datatype']);

        $params['start'] = 0;
        $params['limit'] = datatypes_service::LIVE_DATA_LIMIT_DEFAULT;
        $migration = datatypes_service::init_migration($datatype_obj, 'json');
        $data = $migration->get_records($params);

        return [
            'data' => $data
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata'),
            new require_user_capability('bi/intellidata:viewlivedata')
        ];
    }
}
