<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This plugin provides access to Moodle data in form of analytics and reports in real time.
 *
 * @package    bi_intellidata
 * @copyright  2020 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

use bi_intellidata\services\config_service;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\services\export_service;
use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\persistent\export_logs;
use bi_intellidata\task\export_adhoc_task;
use bi_intellidata\helpers\DebugHelper;
use bi_intellidata\helpers\DBHelper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\export_id_repository;

function xmldb_bi_intellidata_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    //Remove all existing upgrade steps since they are not needed for a forked version
    //The plugin was forked at upstream version 2023010612

    // Add new datatypes to the export.
    if ($oldversion < 2022110801) {

        // Add new LTI Types datatypes datatype.
        $exportlogrepository = new export_log_repository();
        $datatype = 'coursesections';

        // Insert or update log record for datatype.
        $exportlogrepository->insert_datatype($datatype, export_logs::TABLE_TYPE_UNIFIED, true);

        // Add new datatypes to export ad-hoc task.
        $exporttask = new export_adhoc_task();
        $exporttask->set_custom_data([
            'datatypes' => [$datatype]
        ]);
        \core\task\manager::queue_adhoc_task($exporttask);
        upgrade_plugin_savepoint(true, 2022110801, 'bi', 'intellidata');
    }

    if ($oldversion < 2022110804) {

        // Add new datatypes to the export.
        $exportlogrepository = new export_log_repository();

        // Add new datatypes to the plugin config and export.
        $datatypes = [
            'coursegroups',
            'coursegroupmembers'
        ];

        foreach ($datatypes as $datatype) {
            // Insert or update log record for datatype.
            $exportlogrepository->insert_datatype($datatype, export_logs::TABLE_TYPE_UNIFIED, true);

            // Add new datatypes to export ad-hoc task.
            $exporttask = new export_adhoc_task();
            $exporttask->set_custom_data([
                'datatypes' => [$datatype]
            ]);
            \core\task\manager::queue_adhoc_task($exporttask);
        }

        // Add index to bi_intellidata_tracking.
        $table = new xmldb_table('bi_intellidata_tracking');
        $index = new xmldb_index('page_param_idx', XMLDB_INDEX_NOTUNIQUE, ['page', 'param']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $datatypes = [
            'users', 'quizattempts', 'quizquestionattempts'
        ];

        $exportlogrepository = new export_log_repository();
        foreach ($datatypes as $datatype) {
            $record = datatypeconfig::get_record(['datatype' => $datatype]);

            // Reset export logs.
            $exportlogrepository->reset_datatype($datatype);

            // Delete old export files.
            $exportservice = new export_service();
            $exportservice->delete_files([
                'datatype' => $datatype,
                'timemodified' => time()
            ]);

            // Add task to migrate records.
            if ($record->get('tabletype') == datatypeconfig::TABLETYPE_REQUIRED) {
                $exporttask = new export_adhoc_task();
                $exporttask->set_custom_data([
                    'datatypes' => [$record->get('datatype')]
                ]);

                \core\task\manager::queue_adhoc_task($exporttask);
            }
        }

        upgrade_plugin_savepoint(true, 2022110804, 'bi', 'intellidata');
    }

    // upstream 2023060700
    if ($oldversion < 2022110805) {

        $datatypes = [
            'competency', 'competency_usercomp', 'competency_coursecomp',
            'competency_usercompcourse', 'competency_modulecomp', 'competency_plan',
            'competency_usercompplan', 'tenant', 'tool_tenant', 'tool_tenant_user',
            'roleassignments'
        ];

        $exportlogrepository = new export_log_repository();
        foreach ($datatypes as $datatype) {
            $record = datatypeconfig::get_record(['datatype' => $datatype]);

            if (!$record) {
                continue;
            }

            // Reset export logs.
            $exportlogrepository->reset_datatype($datatype);
        }

        \bi_intellidata\helpers\TasksHelper::init_refresh_export_progress_adhoc_task();

        $table = new xmldb_table('bi_intellidata_config');
        $field = new xmldb_field('deletedevent', XMLDB_TYPE_CHAR, '255', null, null, null, null);

        if ($dbman->table_exists($table) && !$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Remove DB triggers.
        $datatypes = datatypes_service::get_datatypes();
        try {
            foreach ($datatypes as $datatype) {
                if (isset($datatype['table'])) {
                    DBHelper::remove_deleted_id_triger($datatype['name'], $datatype['table']);
                }
            }

            DBHelper::remove_deleted_id_functions();
        } catch (moodle_exception $e) {
            DebugHelper::error_log($e->getMessage());
        }

        // Delete old export files.
        $datatypes = datatypes_service::get_optional_datatypes_for_export();
        $exportservice = new export_service();
        $exportservice->delete_files(['datatypes' => $datatypes]);

        // Delete old datatype export ids.
        $DB->execute("DELETE FROM {bi_intellidata_export_ids} WHERE datatype NOT IN
        (SELECT datatype FROM {bi_intellidata_config} WHERE tabletype=:tabletype OR tabletype=:tabletype2)",
            [
                'tabletype' => datatypeconfig::TABLETYPE_REQUIRED,
                'tabletype2' => datatypeconfig::TABLETYPE_LOGS,
            ]);

        // Delete old datatype storage.
        $DB->execute("DELETE FROM {bi_intellidata_storage} WHERE datatype NOT IN
        (SELECT datatype FROM {bi_intellidata_config} WHERE tabletype=:tabletype OR tabletype=:tabletype2)",
            [
                'tabletype' => datatypeconfig::TABLETYPE_REQUIRED,
                'tabletype2' => datatypeconfig::TABLETYPE_LOGS
            ]);

        // Delete old datatype logs.
        $DB->execute("DELETE FROM {bi_intellidata_logs} WHERE datatype NOT IN
        (SELECT datatype FROM {bi_intellidata_config} WHERE tabletype=:tabletype OR tabletype=:tabletype2)",
            [
                'tabletype' => datatypeconfig::TABLETYPE_REQUIRED,
                'tabletype2' => datatypeconfig::TABLETYPE_LOGS
            ]);

        $prefix = datatypeconfig::OPTIONAL_TABLE_PREFIX;
        $DB->execute(
            "UPDATE {bi_intellidata_config}
            SET datatype = CONCAT('{$prefix}', datatype)
            WHERE tabletype = :tabletype AND  datatype NOT LIKE '" . $prefix . "%'",
            [
                'tabletype' => datatypeconfig::TABLETYPE_OPTIONAL
            ]
        );

        $DB->execute(
            "UPDATE {bi_intellidata_export_log}
            SET datatype = CONCAT('{$prefix}', datatype),
                last_exported_time=0,
                last_exported_id=0,
                migrated=0,
                timestart=0,
                recordsmigrated=0,
                recordscount=0
            WHERE tabletype = :tabletype AND  datatype NOT LIKE '" . $prefix . "%'",
            [
                'tabletype' => datatypeconfig::TABLETYPE_OPTIONAL
            ]
        );

        // Delete duplicates config datatypes.
        $ids = $DB->get_records_sql('SELECT max(id)
                                           FROM {bi_intellidata_config}
                                       GROUP BY datatype
                                         HAVING COUNT(*) > 1');
        $ids = array_keys($ids);
        if ($ids) {
            $DB->execute("DELETE FROM {bi_intellidata_config}
                            WHERE id IN (" . implode(',', $ids) . ")");
        }

        $datatypes = datatypes_service::get_datatypes();
        try {
            foreach ($datatypes as $datatype) {
                if (isset($datatype['table'])) {
                    $datatypename = datatypes_service::get_optional_table($datatype['name']);
                    DBHelper::remove_deleted_id_triger($datatypename, $datatype['table']);
                }
            }

            DBHelper::remove_deleted_id_functions();
        } catch (moodle_exception $e) {
            DebugHelper::error_log($e->getMessage());
        }

        $configservice = new config_service(datatypes_service::get_all_optional_datatypes());
        $configservice->setup_config();

        $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*');
        if ($role) {
            $capabilities = [
                'bi/intellidata:viewadhoctasks',
                'bi/intellidata:deleteadhoctasks',
            ];
            $system_context = \context_system::instance();
            foreach ($capabilities as $capability) {
                $cap_obj = new stdClass();
                $cap_obj->name = $capability;
                $cap_obj->captype = 'read';
                $cap_obj->contextlevel = CONTEXT_SYSTEM;
                $cap_obj->component = 'bi_intellidata';
                // We may have to create a capability manually here, since the new role does not have an archetype.
                if (!$DB->record_exists('capabilities', [
                    'name' => $cap_obj->name,
                    'captype' => $cap_obj->captype,
                    'contextlevel' => $cap_obj->contextlevel,
                    'component' => $cap_obj->component
                ])) {
                    $DB->insert_record('capabilities', $cap_obj);
                }
                assign_capability($capability, CAP_ALLOW, $role->id, $system_context->id);
            }
        }

        upgrade_plugin_savepoint(true, 2022110805, 'bi', 'intellidata');
    }

    return true;
}
