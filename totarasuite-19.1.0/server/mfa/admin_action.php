<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

require_once(__DIR__ . '/../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$return_url = new moodle_url('/admin/settings.php', ['section' => 'mfa_settings']);

$PAGE->set_url($return_url);

$action = required_param('action', PARAM_ALPHANUMEXT);
$plugin = required_param('plugin', PARAM_PLUGIN);

$plugins = core_plugin_manager::instance()->get_plugins_of_type('mfa');
$enabled = \core\plugininfo\mfa::get_enabled_plugins();

if (!confirm_sesskey() || !isset($plugins[$plugin])) {
    redirect($return_url);
}

$old_value = $CFG->mfa_plugins ?? null;

switch ($action) {
    case 'disable':
        if (isset($enabled[$plugin])) {
            unset($enabled[$plugin]);
            $new_value = $enabled ? implode(',', $enabled) : null;
            add_to_config_log('mfa_plugins', $old_value, $new_value, null);
            set_config('mfa_plugins', $new_value);
            \core\event\mfa_factor_enabled_changed::disabled($plugin)->trigger();
            core_plugin_manager::reset_caches();
        }
        break;

    case 'enable':
        if (!isset($enabled[$plugin])) {
            $enabled[$plugin] = $plugin;
            $new_value = $enabled ? implode(',', $enabled) : null;
            add_to_config_log('mfa_plugins', $old_value, $new_value, null);
            set_config('mfa_plugins', $new_value);
            \core\event\mfa_factor_enabled_changed::enabled($plugin)->trigger();
            core_plugin_manager::reset_caches();
        }
        break;

    default:
        break;
}

redirect($return_url);
