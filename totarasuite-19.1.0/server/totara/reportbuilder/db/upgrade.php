<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

/**
 * Local database upgrade script
 *
 * @param   int $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean always true
 */
function xmldb_totara_reportbuilder_upgrade($oldversion) {
    global $CFG, $DB;
    require_once(__DIR__ .'/upgradelib.php');

    $dbman = $DB->get_manager();

    // Totara 13.0 release line.
    $result = true;

    if ($oldversion < 2022060900) {

        $result = $result && totara_reportbuilder_reset_cache_for_reports_with_visibility_checks();

        // Reportbuilder savepoint reached.
        upgrade_plugin_savepoint(true, 2022060900, 'totara', 'reportbuilder');
    }

    if ($oldversion < 2023070506) {
        // Define field tenantid to be added to report_builder.
        $table = new xmldb_table('report_builder');
        $field = new xmldb_field('tenantid', XMLDB_TYPE_INTEGER, '10', null, false, null, null, 'useclonedb');

        // Conditionally launch add field tenantid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add foreign key for tenantid field
        $key = new xmldb_key('tenantid_key', XMLDB_KEY_FOREIGN, array('tenantid'), 'tenant', array('id'), 'cascade', 'cascade');
        $dbman->add_key($table, $key);

        // Reportbuilder savepoint reached.
        upgrade_plugin_savepoint(true, 2023070506, 'totara', 'reportbuilder');
    }

    if ($oldversion < 2024052001) {
        $table = new xmldb_table('report_builder_columns');
        $field = new xmldb_field('rowheader', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'customheading');

        // Conditionally add rowheader
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Apply the missing rowheaders to applied embedded reports.
        totara_reportbuilder_set_embedded_default_rowheaders();

        upgrade_plugin_savepoint(true, 2024052001, 'totara', 'reportbuilder');
    }

    if ($oldversion < 2025012801) {

        // Define field userlinkcheck to be added to report_builder.
        $table = new xmldb_table('report_builder');
        $field = new xmldb_field('userlinkcheck', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'tenantid');

        // Conditionally launch add field userlinkcheck.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Reportbuilder savepoint reached.
        upgrade_plugin_savepoint(true, 2025012801, 'totara', 'reportbuilder');
    }

    if ($oldversion < 2025032701) {
        // Add compress option for reportbuilder email attachment file
        $table = new xmldb_table('report_builder_schedule');
        $field = new xmldb_field('compress', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'format');

        // Conditionally add rowheader
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025032701, 'totara', 'reportbuilder');
    }

    return $result;
}
