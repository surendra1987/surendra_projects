<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@totaralms.com>
 * @package totara_core
 */

defined('MOODLE_INTERNAL') || die();

$ADMIN->add('modules', new admin_category('tabexports', new lang_string('tabexports', 'totara_core')));
foreach (core_plugin_manager::instance()->get_plugins_of_type('tabexport') as $plugin) {
    /** @var \core\plugininfo\tool $plugin */
    $plugin->load_settings($ADMIN, 'tabexports', $hassiteconfig);
}

// Virtual room service providers.
$ADMIN->add('modules', new admin_category('virtualmeetingfolder', new lang_string('plugintype_virtualmeetings', 'totara_core')));

foreach (\core\plugininfo\virtualmeeting::get_all_plugins(true) as $plugin) {
    $plugin->load_settings($ADMIN, 'virtualmeetingfolder', $hassiteconfig);
}

$ADMIN->add('modules', new admin_category('bifolder', new lang_string('bi', 'totara_core')));
$ADMIN->add('bifolder', new admin_externalpage('managebiplugins', new lang_string('managebi', 'core_admin'),
    $CFG->wwwroot . '/integrations/bi/managebi.php'));

foreach (core_plugin_manager::instance()->get_plugins_of_type('bi') as $plugin) {
    /** @var \core\plugininfo\bi $plugin */
    $plugin->load_settings($ADMIN, 'bifolder', $hassiteconfig);
}

// Add AI experimental feature
if ($hassiteconfig) {
    $temp = $ADMIN->locate('experimentalsettings');
    $ai_enabled_setting = new admin_setting_configcheckbox(
        'enable_ai',
        get_string('enable_ai', 'ai'),
        get_string('enable_ai_description', 'ai'),
        0
    );
    $temp->add($ai_enabled_setting);
    $ADMIN->add('experimental', new admin_category(\core_ai\subsystem::ADMIN_TREE_FOLDER, new lang_string('ai', 'ai')));

    // experimental setting enabled
    if ($ai_enabled_setting->get_setting()) {
        $ADMIN->add(
            \core_ai\subsystem::ADMIN_TREE_FOLDER,
            new admin_externalpage(
                'manageaiplugins',
                new lang_string('manage_ai', 'ai'),
                \core\plugininfo\ai::get_manage_url()->out()
            )
        );

        foreach (core_plugin_manager::instance()->get_plugins_of_type('ai') as $plugin) {
            /** @var \core\plugininfo\ai $plugin */
            $plugin->load_settings($ADMIN, \core_ai\subsystem::ADMIN_TREE_FOLDER, $hassiteconfig);
        }
    }
}
