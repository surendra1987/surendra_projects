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

use tool_diagnostic\manager;

/**
 * File serving code for tool_diagnostic plugin.
 *
 * @param stdClass $course course object
 * @param cm_info $cm course module object
 * @param context $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether to force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function tool_diagnostic_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $USER;

    // Only system context allowed.
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    require_login();

    // Admins only.
    if (!is_siteadmin($USER)) {
        throw new \moodle_exception('adminaccessrequired');
    }

    if ($filearea !== 'export') {
        return false;
    }

    $file_item_id = (int)array_shift($args);
    $relative_path = implode('/', $args);

    if ($relative_path !== manager::EXPORT_FILE_NAME) {
        return false;
    }

    $fs = get_file_storage();
    $file_record = manager::get_result_file_record($file_item_id);
    if (!$file_record) {
        throw new moodle_exception('filenotfound', 'tool_diagnostic');
    }
    $file = $fs->get_file_instance($file_record);

    if (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING) {
        echo 'behat export file access success';
        die;
    }

    // Finally send the file.
    send_stored_file(
        $file,
        0,
        0,
        1,
        //  Prevent exiting so we can delete the file right after download.
        ['dontdie' => true]
    );

    $file->delete();

    die;
}
