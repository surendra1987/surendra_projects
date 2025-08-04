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

namespace bi_intellidata\webapi\resolver\mutation;

use bi_intellidata\helpers\ParamsHelper;
use bi_intellidata\services\database_service;
use bi_intellidata\services\export_service;
use context_system;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_plugin_enabled;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use external_api;
use stdClass;

/**
 * Mutation to export dat for IB.
 */
class export_data extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        external_api::validate_context($context);

        // Export static tables.
        $database_service = new database_service(false);
        $database_service->export_tables();

        $export_service = new export_service();
        // Generate and save files to filesdir.
        $export_service->save_files();

        $items = [];
        $export_files = $export_service->get_files();
        foreach ($export_files as $data_type => $files) {
            if (count($files) === 0) {
                continue;
            }

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