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
 * @author Michael Ivanov <michael.ivanov@totaralearning.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\webapi\resolver\mutation;

use bi_intellidata\exception\invalid_set_datatype_exception;
use bi_intellidata\helpers\DBHelper;
use bi_intellidata\helpers\DebugHelper;
use bi_intellidata\helpers\RestrictedTablesHelper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\task\export_adhoc_task;
use context_system;
use core\task\manager;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\middleware\require_plugin_enabled;
use core\webapi\middleware\require_user_capability;
use core\webapi\mutation_resolver;
use external_api;

/**
 * This resolver is for adding or updating (resetting) a new custom table to the export mechanism.
 */
class set_datatype extends mutation_resolver {
    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;
        require_once($CFG->libdir . '/externallib.php');

        // Ensure the current user is allowed to run this function.
        $context = context_system::instance();
        external_api::validate_context($context);

        $params = $args['input'] ?? [];

        if (!isset($params['exportfiles'])) {
            $params['exportfiles'] = SettingsHelper::STATUS_DISABLE;
        }
        if (!in_array($params['exportfiles'], [SettingsHelper::STATUS_DISABLE, SettingsHelper::STATUS_ENABLE])) {
            throw new invalid_set_datatype_exception('Invalid exportfiles parameter.');
        }
        if (isset($params['callbackurl'])) {
            if ($params['exportfiles'] != SettingsHelper::STATUS_ENABLE) {
                throw new invalid_set_datatype_exception('Callback URL only works with exportfiles enabled.');
            }
            $params['callbackurl'] = trim($params['callbackurl']);
            if (!filter_var($params['callbackurl'], FILTER_VALIDATE_URL)) {
                throw new invalid_set_datatype_exception('Invalid callbackurl parameter.');
            }
        }

        $restricted_tables_helper = new RestrictedTablesHelper();
        foreach ($params['datatypes'] as $datatype_name) {
            if (empty(trim($datatype_name))) {
                throw new invalid_set_datatype_exception('A datatype must not be empty.');
            }
            if ($restricted_tables_helper->isRestricted($datatype_name)) {
                throw new invalid_set_datatype_exception('The table ' . $datatype_name . ' is restricted.');
            }
        }

        // Insert or update log record for datatype.
        $exportlogrepository = new export_log_repository();
        foreach ($params['datatypes'] as $datatype_name) {
            // If a datatype has been passed in for a record that already exists, it will not insert but update instead.
            $exportlogrepository->insert_datatype($datatype_name);
        }

        // Add adhoc task to process datatype.
        if ($params['exportfiles']) {
            $exporttask = new export_adhoc_task();
            $exporttask->set_custom_data([
                'datatypes' => $params['datatypes'],
                'callbackurl' => $params['callbackurl']
            ]);
            manager::queue_adhoc_task($exporttask);
        }

        return [
            'data' => 'Datatype(s) successfully added / updated.'
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user(),
            new require_plugin_enabled('bi_intellidata'),
            new require_user_capability('bi/intellidata:editconfig')
        ];
    }
}