<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * The mod_hvp content user data.
 *
 * @package    mod_hvp
 * @since      Moodle 2.7
 * @copyright  2016 Joubel AS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hvp;

defined('MOODLE_INTERNAL') || die();

/**
 * Class content_user_data handles user data and corresponding db operations.
 *
 * @package mod_hvp
 * @copyright   2018 Joubel AS <contact@joubel.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class content_user_data {

    /**
     * Retrieves ajax parameters for content and update or delete
     * user data depending on params.
     *
     * @param int $contentid
     * @param int $subcontentid
     * @param string $dataid
     * @param string $token
     * @param string|null $data
     * @param int|null $preload
     * @param int|null $invalidate
     * @param int|null $contextid
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function handle_ajax(int $contentid, int $subcontentid, string $dataid, string $token, ?string $data = null, ?int $preload = null, ?int $invalidate = null, ?int $contextid = null) {
        // Saving data.
        if ($data !== null && $preload !== null && $invalidate !== null) {
            self::store_data($contentid, $subcontentid, $dataid, $data, $preload, $invalidate, $contextid, $token);
        } else {
            self::fetch_existing_data($contentid, $subcontentid, $dataid);
        }
    }


    /**
     * Stores content user data
     *
     * @param $contentid
     * @param $subcontentid
     * @param $dataid
     * @param $data
     * @param $preload
     * @param $invalidate
     * @param $contextid
     * @param $token
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    private static function store_data($contentid, $subcontentid, $dataid, $data, $preload, $invalidate, $contextid = null, $token = null) {
        // Validate token.
        if (is_null($token)) {
            $token = required_param('token', PARAM_ALPHANUM);
        }
        if (!\H5PCore::validToken('contentuserdata', $token)) {
            \H5PCore::ajaxError(get_string('invalidtoken', 'hvp'));
            return;
        }

        if ($contentid === 0) {
            $context = \context::instance_by_id($contextid);
        } else {
            // Load course module for content to get context.
            $cm = get_coursemodule_from_instance('hvp', $contentid);
            if (!$cm) {
                \H5PCore::ajaxError('No such content');
                http_response_code(404);
                return;
            }
            $context = \context_module::instance($cm->id);
        }

        // Check permissions.
        if (!has_capability('mod/hvp:savecontentuserdata', $context)) {
            \H5PCore::ajaxError(get_string('nopermissiontosavecontentuserdata', 'hvp'));
            http_response_code(403);
            return;
        }

        if ($data === '0') {
            // Delete user data.
            self::delete_user_data($contentid, $subcontentid, $dataid);
        } else {
            // Save user data.
            self::save_user_data($contentid, $subcontentid, $dataid, $preload, $invalidate, $data);
        }
        \H5PCore::ajaxSuccess();
    }

    /**
     * Return existing content user data
     *
     * @param $contentid
     * @param $subcontentid
     * @param $dataid
     *
     * @throws \dml_exception
     */
    private static function fetch_existing_data($contentid, $subcontentid, $dataid) {
        // Fetch user data.
        $userdata = self::get_user_data($contentid, $subcontentid, $dataid);
        \H5PCore::ajaxSuccess($userdata ? $userdata->data : null);
    }

    /**
     * Get user data for content.
     *
     * @param $contentid
     * @param $subcontentid
     * @param $dataid
     *
     * @return mixed
     * @throws \dml_exception
     */
    public static function get_user_data($contentid, $subcontentid, $dataid) {
        global $DB, $USER;

        $result = $DB->get_record('hvp_content_user_data', array(
                'user_id' => $USER->id,
                'hvp_id' => $contentid,
                'sub_content_id' => $subcontentid,
                'data_id' => $dataid
            )
        );

        return $result;
    }

    /**
     * Save user data for specific content in database.
     *
     * @param $contentid
     * @param $subcontentid
     * @param $dataid
     * @param $preload
     * @param $invalidate
     * @param $data
     *
     * @throws \dml_exception
     */
    public static function save_user_data($contentid, $subcontentid, $dataid, $preload, $invalidate, $data) {
        global $DB, $USER;

        // Determine if we should update or insert.
        $update = self::get_user_data($contentid, $subcontentid, $dataid);

        // Wash values to ensure 0 or 1.
        $preload = ($preload === '0' || $preload === 0) ? 0 : 1;
        $invalidate = ($invalidate === '0' || $invalidate === 0) ? 0 : 1;

        // New data to be inserted.
        $newdata = (object)array(
            'user_id' => $USER->id,
            'hvp_id' => $contentid,
            'sub_content_id' => $subcontentid,
            'data_id' => $dataid,
            'data' => $data,
            'preloaded' => $preload,
            'delete_on_content_change' => $invalidate
        );

        // Does not exist.
        if ($update === false) {
            // Insert new data.
            $DB->insert_record('hvp_content_user_data', $newdata);
        } else {
            // Get old data id.
            $newdata->id = $update->id;

            // Update old data.
            $DB->update_record('hvp_content_user_data', $newdata);
        }
    }

    /**
     * Delete user data with specific content from database
     *
     * @param $contentid
     * @param $subcontentid
     * @param $dataid
     *
     * @throws \dml_exception
     */
    public static function delete_user_data($contentid, $subcontentid, $dataid) {
        global $DB, $USER;

        $DB->delete_records('hvp_content_user_data', array(
            'user_id' => $USER->id,
            'hvp_id' => $contentid,
            'sub_content_id' => $subcontentid,
            'data_id' => $dataid
        ));
    }

    /**
     * Load user data for specific content
     *
     * @param $contentid
     *
     * @return array User data for specific content if found, else null
     * @throws \dml_exception
     */
    public static function load_pre_loaded_user_data($contentid) {
        global $DB, $USER;

        $preloadeduserdata = array(
            'state' => '{}'
        );

        $results = $DB->get_records('hvp_content_user_data', array(
            'user_id' => $USER->id,
            'hvp_id' => $contentid,
            'sub_content_id' => 0,
            'preloaded' => 1
        ));

        // Get data for data ids.
        foreach ($results as $contentuserdata) {
            $preloadeduserdata[$contentuserdata->data_id] = $contentuserdata->data;
        }

        return $preloadeduserdata;
    }
}
