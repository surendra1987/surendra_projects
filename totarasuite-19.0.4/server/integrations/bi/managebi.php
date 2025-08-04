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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package core_bi
 */

/**
 * Allows the admin to manage bi plugins
 *
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot.'/integrations/bi/bi_plugin_manager.php');

$action = optional_param('action', null, PARAM_PLUGIN);
$plugin = optional_param('plugin', null, PARAM_PLUGIN);

if (!empty($plugin)) {
    require_sesskey();
}

// Create the class for this controller.
$pluginmanager = new bi_plugin_manager();

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/integrations/bi/managebi.php'));

// Execute the controller.
$pluginmanager->execute($action, $plugin);
