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
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\export_id_repository;
use bi_intellidata\repositories\tracking\tracking_repository;
use bi_intellidata\services\config_service;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\services\intelliboard_service;
use bi_intellidata\helpers\DBHelper;
use bi_intellidata\helpers\DebugHelper;

function xmldb_bi_intellidata_install() {

    // Set exportformat for csv.
    set_config('exportdataformat', 'csv', 'bi_intellidata');

    // Disable compress tracking by default
    set_config('compresstracking', tracking_repository::TYPE_LIVE, 'bi_intellidata');

    // Set the default layout setting
    set_config('defaultlayout', SettingsHelper::get_defaut_config_value('defaultlayout'), 'bi_intellidata');

    // Setup config in database.
    $configservice = new config_service(datatypes_service::get_all_datatypes());
    $configservice->setup_config();

    // Create a role for accessing Intelliboard services via integration with totara (note: no role archetype for this).
    global $DB;
    $shortname = 'intelliboardapiuser';
    if (!$DB->record_exists('role', ['shortname' => $shortname])) {
        $newroleid = create_role('', $shortname, '', $shortname);
    }
    $role = $DB->get_record('role', ['shortname' => 'intelliboardapiuser'], '*', MUST_EXIST);

    set_role_contextlevels($role->id, [CONTEXT_SYSTEM]);

    // Add capabilities for the role here.
    $capabilities = [
        'bi/intellidata:viewlogs',
        'bi/intellidata:viewconfig',
        'bi/intellidata:editconfig',
        'bi/intellidata:viewdbschema',
        'bi/intellidata:viewlivedata',
        'bi/intellidata:managefiles',
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

    return true;
}
