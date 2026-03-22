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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_oauth2
 */

namespace auth_oauth2;

use context_user;
use file_exception;
use moodle_url;
use stdClass;

class complete_user_login_callback {
    /**
     * Complete user login function.
     *
     * @param $redirecturl
     * @param $oauthpicture
     * @return void
     */
    public static function execute($redirecturl, $oauthpicture) {
        global $SESSION, $USER, $CFG;

        // No URL means validation of PARAM_LOCALURL failed in calling code, in that case go to homepage.
        $redirecturl = ($redirecturl === '') ? new moodle_url('/') : $redirecturl;

        if (user_not_fully_set_up($USER, true)) {
            unset($SESSION->wantsurl);
            $redirecturl = $CFG->wwwroot . '/user/edit.php?returnurl=' . urlencode($redirecturl);
        }
        if ($oauthpicture) {
            require_once($CFG->libdir . '/filelib.php');
            require_once($CFG->libdir . '/gdlib.php');
            require_once($CFG->dirroot . '/user/lib.php');

            if (!empty($USER->picture)) {
                redirect($redirecturl);
            }
            if (!empty($CFG->enablegravatar)) {
                redirect($redirecturl);
            }

            $fs = get_file_storage();
            $context = context_user::instance($USER->id, MUST_EXIST);
            $fs->delete_area_files($context->id, 'user', 'newicon');

            $filerecord = array(
                'contextid' => $context->id,
                'component' => 'user',
                'filearea' => 'newicon',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'image'
            );

            try {
                $fs->create_file_from_string($filerecord, $oauthpicture);
            } catch (file_exception $e) {
                redirect($redirecturl);
            }

            $iconfile = $fs->get_area_files($context->id, 'user', 'newicon', false, 'itemid', false);

            // There should only be one.
            $iconfile = reset($iconfile);

            // Something went wrong while creating temp file - remove the uploaded file.
            if (!$iconfile = $iconfile->copy_content_to_temp()) {
                $fs->delete_area_files($context->id, 'user', 'newicon');
                redirect($redirecturl);
            }

            // Copy file to temporary location and the send it for processing icon.
            $newpicture = (int) process_new_icon($context, 'user', 'icon', 0, $iconfile);
            // Delete temporary file.
            @unlink($iconfile);
            // Remove uploaded file.
            $fs->delete_area_files($context->id, 'user', 'newicon');
            // Set the user's picture.
            $updateuser = new stdClass();
            $updateuser->id = $USER->id;
            $updateuser->picture = $newpicture;
            user_update_user($updateuser);
            $USER->picture = (string)$updateuser->picture;
        }
        redirect($redirecturl);
    }
}