<?php
/**
 * This file is part of Totara Core
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\webapi\resolver\query;

use core\webapi\execution_context;
use core\webapi\middleware\reopen_session_for_writing;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use moodle_exception;
use totara_catalog\exception\items_query_exception;
use totara_catalog\local\config;
use totara_catalog\output\details as output_details;
use totara_catalog\provider_handler;
use totara_catalog\webapi\schema_objects\item_details as details;

/**
 * Query resolver to fetch catalog item details.
 */
class item_details extends query_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $DB;

        $object = $DB->get_record('catalog', ['id' => $args['input']['itemid']], 'id, objecttype, objectid, contextid');

        if (!$object) {
            throw new items_query_exception('Item not found.');
        }

        $config = config::instance();

        $instance = provider_handler::instance();
        $provider = $instance->get_provider($object->objecttype);

        // Check that the current user is allowed to see the data.
        if (!$provider->can_see([$object])[$object->objectid]) {
            throw new items_query_exception('Tried to access data without permission');
        }

        // Get the dataholders required to display details for the provider.
        $providerrequireddataholders = output_details::get_required_dataholders($provider);
        $requireddataholders = [$object->objecttype => $providerrequireddataholders];

        // Add the formatted data from the dataholders.
        [$object] = $instance->get_data_for_objects([$object], $requireddataholders);

        // Get processed object for UI.
        $details = details::from_record($object, $provider, $config);

        return ['details' => $details];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login(),
            new reopen_session_for_writing()
        ];
    }
}
