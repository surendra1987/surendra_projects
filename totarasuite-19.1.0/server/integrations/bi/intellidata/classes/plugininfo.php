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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */

namespace bi_intellidata;

defined('MOODLE_INTERNAL') || die();

use bi_intellidata\helpers\SettingsHelper;
use context_system;
use core\plugininfo\bi;
use part_of_admin_tree;

class plugininfo extends bi {
    /**
     * @return bool
     */
    public function is_enabled(): bool {
        return (bool)SettingsHelper::get_setting('enabled');
    }

    public function load_settings(part_of_admin_tree $adminroot, $parentnodename, $hassiteconfig) {
        $hassiteconfig = $hassiteconfig || has_capability('bi/intellidata:viewconfig', context_system::instance());
        parent::load_settings($adminroot, $parentnodename, $hassiteconfig);
    }
}