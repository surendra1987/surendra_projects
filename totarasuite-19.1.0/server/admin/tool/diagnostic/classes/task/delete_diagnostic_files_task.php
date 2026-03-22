<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package tool_diagnostic
 */


namespace tool_diagnostic\task;

use core\orm\query\builder;
use core\task\scheduled_task;
use file_storage;

/**
 * Delete old diagnostic files
 */
class delete_diagnostic_files_task extends scheduled_task {

    public function get_name() {
        return get_string('delete_diagnostic_files_task', 'tool_diagnostic');
    }

    public function execute() {
        // Remove all tool_diagnostic export files that are older than 15 minutes.
        $age_limit = 15 * 60;

        $files = builder::table('files')
            ->where('contextid', SYSCONTEXTID)
            ->where('component', 'tool_diagnostic')
            ->where('filearea', 'export')
            ->where('timecreated', '<', time() - $age_limit)
            ->get();

        /** @var file_storage $fs */
        $fs = get_file_storage();
        foreach ($files as $file) {
            $file = $fs->get_file_by_id($file->id);
            $file->delete();
        }
    }
}
