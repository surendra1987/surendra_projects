<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Maxime Claudel <maxime.claudel@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package admin
 */

use totara_core\version_specific\version_specific_feature;
use totara_core\version_specific\version_specific_setting_base;

defined('MOODLE_INTERNAL') || die();

// Only do this if the user is a site admin.
if ($hassiteconfig) {
    $available_version_specific_settings = version_specific_feature::get_available();
    if (!empty($available_version_specific_settings)) {
        $new_page = new \admin_settingpage('versionspecific', new lang_string('versionspecific', 'totara_core'));
        // Add the on/off switches.
        foreach (version_specific_feature::get_available() as $class => $setting_name) {
            /** @var version_specific_setting_base $class */
            $setting = new totara_core_admin_setting_feature_checkbox(
                $setting_name,
                $class::get_display_name(),
                $class::get_help_text(),
                $class::get_default(),
            );
            if ($class::require_purge_caches()) {
                $setting->set_updatedcallback('purge_all_caches');
            }
            $new_page->add($setting);
        }
        /** @var admin_root $ADMIN */
        $ADMIN->add('experimental', $new_page);
    }
}
