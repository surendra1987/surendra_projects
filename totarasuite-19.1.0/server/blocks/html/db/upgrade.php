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
 * This file keeps track of upgrades to the html block
 *
 * @since Moodle 2.0
 * @package block_html
 * @copyright 2010 Dongsheng Cai
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the HTML block.
 *
 * @param int $oldversion
 */
function xmldb_block_html_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024102201) {
        // Load all instances have settings 'block_html_allowcssclasses'
        $records = \core\orm\query\builder::table('block_instances')
            ->get()
            ->filter(function ($record) {
                $configdata = unserialize_object(base64_decode($record->configdata));
                if (isset($configdata->classes)) {
                    return $record;
                }
            })
            ->all();

        foreach ($records as $record) {
            $configdata = unserialize_object(base64_decode($record->configdata));
            $common_config = $record->common_config ? json_decode($record->common_config) : new stdClass();
            $common_config->customclass = $configdata->classes;
            $record->common_config = json_encode($common_config);

            \core\orm\query\builder::get_db()->update_record('block_instances', $record);
        }

        unset_config('block_html_allowcssclasses');

        // Main savepoint reached.
        upgrade_plugin_savepoint(true, 2024102201, 'block', 'html');
    }

    return true;
}
