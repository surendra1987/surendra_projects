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
 * @copyright  2022 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

use bi_intellidata\helpers\DBManagerHelper;
use bi_intellidata\services\datatypes_service;
use bi_intellidata\helpers\DBHelper;
use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\repositories\export_id_repository;
use bi_intellidata\repositories\config_repository;
use bi_intellidata\helpers\DebugHelper;

function xmldb_bi_intellidata_uninstall() {

    // Remove custom indexes.
    $configrepository = new config_repository();
    $configs = $configrepository->get_config();

    if (count($configs)) {
        $datatypes = datatypes_service::get_all_datatypes();

        foreach ($configs as $config) {
            if (!empty($config->tableindex) && !empty($datatypes[$config->datatype]['table'])) {
                DBManagerHelper::delete_index(
                    $datatypes[$config->datatype]['table'],
                    $config->tableindex
                );
            }
        }
    }

}