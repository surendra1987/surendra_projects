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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

defined('MOODLE_INTERNAL') || die;

$ADMIN->add(
    'authsettings',
    new admin_externalpage(
        $settings->name,
        $settings->visiblename,
        $CFG->wwwroot . '/auth/ssosaml/plugin_settings.php',
        'auth/ssosaml:manage',
        $settings->is_hidden()
    )
);

// General settings (standard admin settings page)
// This page is hidden from the admin menu, but exists to allow the settings to appear in search results
$settingspage = new admin_settingpage(
    'authsettingssosaml_hidden',
    new lang_string('settings', 'core_plugin'),
    'auth/ssosaml:manage',
    true
);

if ($ADMIN->fulltree) {
    $authplugin = get_auth_plugin('ssosaml');

    display_auth_lock_options(
        $settingspage,
        $authplugin->authtype,
        $authplugin->userfields,
        '',
        false,
        false,
        $authplugin->get_custom_user_profile_fields()
    );
}

$ADMIN->add('authsettings', $settingspage);

// Don't use the settings page the auth framework defined for us.
$settings = null;
