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
 * @copyright  2017 Joubel AS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_hvp;

defined('MOODLE_INTERNAL') || die();

/**
 * Class xapi_result handles xapi results and corresponding db operations.
 *
 * @package mod_hvp
 */
class xapi_result {

    private static ?\H5PCore $core = null;

    /**
     * Singleton for setting the H5P instance
     * allows for unit testing to take place with overrides in place
     * for the H5PCore class
     *
     * @param \H5PCore|null $core
     * @return \H5PCore
     */
    public static function get_core(?\H5PCore $core = null): \H5PCore{
        if ($core === null) {
            if (static::$core === null) {
                static::$core = framework::instance();
            }
        } else {
            static::$core = $core;
        }
        return static::$core;
    }

    /**
     * Handle xapi results endpoint
     */
    public static function handle_ajax(string $token, int $contextId, string $xapiResult) {
        $core = static::get_core();
        // Validate token.
        if (!self::validate_token($token)) {
            $core::ajaxError($core->h5pF->t('Invalid security token.'),
                'INVALID_TOKEN');
            return;
        }

        $cm = get_coursemodule_from_id('hvp', $contextId);
        if (!$cm) {
            $core::ajaxError('No such content');
            \mod_hvp\local\helper::http_response_code(404);
            return;
        }

        // Validate.
        $context = \context_module::instance($cm->id);
        if (!has_capability('mod/hvp:saveresults', $context)) {
            $core::ajaxError(get_string('nopermissiontosaveresult', 'hvp'));
            return;
        }

        $xapijson = json_decode($xapiResult);
        if (!$xapijson) {
            $core::ajaxError('Invalid json in xAPI data.');
            return;
        }

        if (!self::validate_xapi_data($xapijson)) {
            $core::ajaxError('Invalid xAPI data.');
            return;
        }

        // Delete any old results.
        self::remove_xapi_data($cm->instance);

        // Store results.
        self::store_xapi_data($cm->instance, $xapijson);

        // Successfully inserted xAPI result.
        $core::ajaxSuccess();
    }

    /**
     * Validate xAPI results token
     *
     * @return bool True if token was valid
     */
    private static function validate_token(string $token) {
        $core = static::get_core();
        return $core::validToken('xapiresult', $token);
    }

    /**
     * Validate xAPI data
     *
     * @param object $xapidata xAPI data
     *
     * @return bool True if valid data
     */
    private static function validate_xapi_data($xapidata) {
        $xapidata = new \H5PReportXAPIData($xapidata);
        return $xapidata->validateData();
    }

    /**
     * Store xAPI result(s)
     *
     * @param int $contentid Content id
     * @param object $xapidata xAPI data
     * @param int $parentid Parent id
     */
    private static function store_xapi_data($contentid, $xapidata, $parentid = null) {
        global $DB, $USER;

        $xapidata = new \H5PReportXAPIData($xapidata, $parentid);
        $insertedid = $DB->insert_record('hvp_xapi_results', (object) array(
            'content_id' => $contentid,
            'user_id' => $USER->id,
            'parent_id' => $xapidata->getParentID(),
            'interaction_type' => $xapidata->getInteractionType(),
            'description' => $xapidata->getDescription(),
            'correct_responses_pattern' => $xapidata->getCorrectResponsesPattern(),
            'response' => $xapidata->getResponse(),
            'additionals' => $xapidata->getAdditionals(),
            'raw_score' => $xapidata->getScoreRaw(),
            'max_score' => $xapidata->getScoreMax(),
        ));

        // Save sub content statements data.
        if ($xapidata->isCompound()) {
            foreach ($xapidata->getChildren($contentid) as $child) {
                self::store_xapi_data($contentid, $child, $insertedid);
            }
        }
    }

    /**
     * Remove xAPI result(s)
     *
     * @param int $contentid Content id
     */
    private static function remove_xapi_data($contentid) {
        global $DB, $USER;

        $DB->delete_records('hvp_xapi_results', array(
            'content_id' => $contentid,
            'user_id' => $USER->id
        ));
    }
}
