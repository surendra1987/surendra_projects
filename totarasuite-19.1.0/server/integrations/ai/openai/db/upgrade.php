<?php
/**
 * This file is part of Totara Core
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package ai_openai
 */

use ai_openai\plugininfo;

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade the plugin.
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool result
 */
function xmldb_ai_openai_upgrade($oldversion) {
    if ($oldversion < 2025032701) {
        $model = get_config('ai_openai', 'model_selection');
        if (empty($model)) {
            // Set the default model.
            set_config('model_selection', plugininfo::DEFAULT_MODEL, 'ai_openai');
        }
        upgrade_plugin_savepoint(true, 2025032701, 'ai', 'openai');
    }

    return true;
}
