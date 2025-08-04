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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\plugininfo;

use core_plugin_manager;
use moodle_url;
use part_of_admin_tree;

class bi extends base {

    /**
     * @inheritDoc
     */
    public function is_uninstall_allowed() {
        return true;
    }

    /**
     * @param bool $sorted_by_displayname
     * @return array
     */
    public static function get_all_plugins(bool $sorted_by_displayname = false): array {
        $plugins = core_plugin_manager::instance()->get_plugins_of_type('bi');
        if ($sorted_by_displayname) {
            uasort($plugins, function ($x, $y) {
                return $x->get_name() <=> $y->get_name();
            });
        }

        return $plugins;
    }

    /**
     * @inheritDoc
     * @codeCoverageIgnore
     */
    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        if (!$this->is_installed_and_upgraded()) {
            return;
        }

        $ADMIN = $adminroot; // May be used in settings.php.
        $plugininfo = $this; // Also can be used inside settings.php.

        if (($hassiteconfig) && file_exists($this->full_path('settings.php'))) {
            include($this->full_path('settings.php'));
        }
    }

    /**
     * Return URL used for management of plugins of this type.
     * @return moodle_url
     */
    public static function get_manage_url() {
        return new moodle_url('/integrations/bi/managebi.php');
    }
}