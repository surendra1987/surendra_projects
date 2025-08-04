<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

use mod_approval\entity\application\application;
use mod_approval\entity\form\form_version;

/**
 * Local database upgrade script
 *
 * @param   integer $oldversion Current (pre-upgrade) local db version timestamp
 * @return  boolean $result
 */
function xmldb_approval_upgrade($oldversion) {
    global $CFG, $DB;
    require_once("{$CFG->dirroot}/totara/notification/db/upgradelib.php");
    require_once(__DIR__ . '/upgradelib.php');

    $dbman = $DB->get_manager();

    if ($oldversion < 2024012501) {
        mod_approval_sync_assignment_id_numbers();

        // Approval workflows savepoint reached.
        upgrade_mod_savepoint(true, 2024012501, 'approval');
    }

    if ($oldversion < 2024061101) {
        mod_approval_fix_submission_date_format();

        // Approval workflows savepoint reached.
        upgrade_mod_savepoint(true, 2024061101, 'approval');
    }

    if ($oldversion < 2024073101) {
        $table = new xmldb_table('user_enrolments_application');
        $key = new xmldb_key('approval_application_fk', XMLDB_KEY_FOREIGN, array('approval_application_id'), 'approval_application', array('id'), 'cascade');
        // Make sure adding the key into tbale 'user_enrolments_application due to the order of totara upgrade system.
        if ($dbman->table_exists($table) && !$dbman->key_exists($table, $key)) {
            $dbman->add_key($table, $key);
        }

        // Approval workflows savepoint reached.
        upgrade_mod_savepoint(true, 2024073101, 'approval');
    }

    if ($oldversion < 2024111100) {
        mod_approval_sync_org_and_pos_assignment_id_numbers();

        // Approval workflows savepoint reached.
        upgrade_mod_savepoint(true, 2024111100, 'approval');
    }

    if ($oldversion < 2024111101) {
        global $CFG;

        // Conditionally drop the id key, and delete the id field.
        $table = new xmldb_table('approval_role_capability_map');

        $id_field = new xmldb_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);

        if ($dbman->field_exists($table, $id_field)) {
            // Drop and re-install the table.
            $dbman->drop_table($table);
            $dbman->install_one_table_from_xmldb_file($CFG->dirroot . '/mod/approval/db/install.xml', 'approval_role_capability_map');
        }

        // Approval workflows savepoint reached.
        upgrade_mod_savepoint(true, 2024111101, 'approval');
    }

    /**
     * Mechanism for approvalform plugin upgrades which must be executed after mod_approval upgrade.
     * This must remain at the end of this file, put all mod_approval upgrades above it.
     */
    // For each approvalform plugin in use, look for and call upgradelib approvalform_x_upgrade() if it exists.
    $form_versions = form_version::repository()->get();
    foreach ($form_versions as $form_version) {
        /** @var form_version $form_version */
        $exists = $DB->record_exists(application::TABLE, ['form_version_id' => $form_version->id]);
        if ($exists) {
            $lib_path = $CFG->dirroot . '/mod/approval/form/' . $form_version->form->plugin_name . '/lib.php';
            if (file_exists($lib_path)) {
                require_once($lib_path);
                $function_name = 'approvalform_' . $form_version->form->plugin_name . '_mod_approval_upgrade';
                if (function_exists($function_name)) {
                    call_user_func($function_name, $oldversion, $form_version);
                }
            }
        }
    }

    return true;
}
