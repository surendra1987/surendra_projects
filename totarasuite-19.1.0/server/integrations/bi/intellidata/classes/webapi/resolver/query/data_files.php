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

use bi_intellidata\helpers\ParamsHelper;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\services\export_service;
use bi_intellidata\services\storage_service;
use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_plugin_enabled;
use core\webapi\middleware\require_user_capability;
use core\webapi\query_resolver;
use external_api;
use moodle_exception;
use stdClass;

/**
 * Query to data files for IB.
 */
class data_files extends query_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        $input = $args['input'] ?? [];
        if (isset($input)) {
            if (isset($input['timestart']) && isset($input['timeend']) && $input['timestart'] > $input['timeend']) {
                throw new moodle_exception('Time start must be earlier than time end.');
            }
        }
        // Ensure the current user is allowed to run this function.
        external_api::validate_context(context_system::instance());

        $export_service = new export_service();
        $export_log_repository = new export_log_repository();

        if (isset($input['datatype']) && !array_key_exists($input['datatype'], $export_service->get_datatypes())) {
            throw new moodle_exception('Invalid datatype.');
        }

        $all_data_types = [];
        foreach ($export_service->get_datatypes() as $name => $datatype) {
            if ($datatype['migration'] && $datatype['tabletype'] == datatypeconfig::TABLETYPE_REQUIRED) {
                $all_data_types[] = $name;
            }
        }

        $not_migrated_datatypes = array_diff($all_data_types, $export_log_repository->get_migrated_datatypes());
        if (count($not_migrated_datatypes) > 0) {
            throw new moodle_exception('Migrations not ready: '. implode(', ', $not_migrated_datatypes));
        }

        $items = [];
        $export_files = $export_service->get_files($input);
        foreach ($export_files as $data_type => $files) {
            if(!str_starts_with($data_type, "migration_")) {
                $item = new stdClass();
                $item->datatype = $data_type;
                $item->files = $files;

                // Manually find migration files.
                if (isset($export_files["migration_" . $data_type]) && !empty($export_files["migration_" . $data_type])) {
                    $item->migration_files = $export_files["migration_" . $data_type];
                } else {
                    $item->migration_files = [];
                }

                $items[]= $item;
            }
        }

        return [
            'items' => $items,
            'metadata' => ParamsHelper::get_exportfiles_metadata()
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata'),
            new require_user_capability('bi/intellidata:managefiles')
        ];
    }
}