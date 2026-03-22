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

namespace bi_intellidata\watcher;

use bi_intellidata\helpers\ParamsHelper;
use context_system;
use core\entity\user;
use totara_webapi\hook\external_pluginfile_allowed_hook;

class external_pluginfile_watcher {
    /**
     * @param external_pluginfile_allowed_hook $hook
     * @return void
     */
    public static function allow_external_pluginfile(external_pluginfile_allowed_hook $hook): void {
        if ($hook->get_component() !== ParamsHelper::PLUGIN) {
            return;
        }

        $hook->set_allowed(true);
    }

}