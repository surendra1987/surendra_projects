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
 * The mod_hvp user grades
 *
 * @package    mod_hvp
 * @copyright  2016 Joubel AS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_hvp;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

/**
 * Handles grade storage for users
 * @package mod_hvp
 */
class user_grades {

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
     * @param string $token
     * @param int $contextId
     * @param int $score
     * @param int $maxScore
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function handle_ajax(string $token, int $contextId, int $score, int $maxScore) {
        global $DB, $USER, $CFG;

        $core = static::get_core();

        if (!$core::validToken('result', $token)) {
            $core::ajaxError(get_string('invalidtoken', 'hvp'));
            return;
        }

        $cm = get_coursemodule_from_id('hvp', $contextId);
        if (!$cm) {
            $core::ajaxError('No such content');
            \mod_hvp\local\helper::http_response_code(404);
            return;
        }

        // Check permission.
        $context = \context_module::instance($cm->id);
        if (!has_capability('mod/hvp:saveresults', $context)) {
            $core::ajaxError(get_string('nopermissiontosaveresult', 'hvp'));
            \mod_hvp\local\helper::http_response_code(403);
            return;
        }

        // Get hvp data from content.
        $hvp = $DB->get_record('hvp', array('id' => $cm->instance));
        if (!$hvp) {
            $core::ajaxError('No such content');
            \mod_hvp\local\helper::http_response_code(404);
            return;
        }

        // Create grade object and set grades.
        $grade = (object) array(
            'userid' => $USER->id
        );

        // Set grade using Gradebook API.
        $hvp->cmidnumber = $cm->idnumber;
        $hvp->name = $cm->name;
        $hvp->rawgrade = $score;
        $hvp->rawgrademax = $maxScore;
        hvp_grade_item_update($hvp, $grade);

        // Get content info for log.
        $content = $DB->get_record_sql(
                "SELECT c.name AS title, l.machine_name AS name, l.major_version, l.minor_version
                   FROM {hvp} c
                   JOIN {hvp_libraries} l ON l.id = c.main_library_id
                  WHERE c.id = ?",
                array($hvp->id)
        );

        require_once($CFG->libdir.'/completionlib.php');

        // Load Course.
        $course = $DB->get_record('course', array('id' => $cm->course));
        $completion = new \completion_info( $course );

        if ( $completion->is_enabled( $cm) ) {
            $completion->update_state($cm, COMPLETION_COMPLETE);
        }

        // Log results set event.
        new \mod_hvp\event(
                'results', 'set',
                $hvp->id, $content->title,
                $content->name, $content->major_version . '.' . $content->minor_version
        );

        // Trigger Moodle event for async notification messages.
        $event = \mod_hvp\event\attempt_submitted::create([
            'context' => $context,
        ]);
        $event->trigger();

        $core::ajaxSuccess();
    }

    /**
     *  Since the subcontent types do not have their own row in the table,
     *  we use the hvp_results_table as a 'staging area' to set and get
     *  dynamically graded scores.
     *
     * @param string $token
     * @param int $contextId
     * @param int $subContentId
     * @param int $score
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function handle_dynamic_grading(string $token, int $contextId, int $subContentId, int $score) {
        global $DB;

        $core = static::get_core();

        if (!$core::validToken('result', $token)) {
            $core::ajaxError(get_string('invalidtoken', 'hvp'));
            return;
        }

        $cm = get_coursemodule_from_id('hvp', $contextId);
        if (!$cm) {
            $core::ajaxError('No such content');
            \mod_hvp\local\helper::http_response_code(404);
            return;
        }

        // Update the answer's score.
        $data = (object) [
            'id' => $subContentId,
            'raw_score' => $score
        ];
        $DB->update_record('hvp_xapi_results', $data, false);

        // Load freshly updated record.
        $answer = $DB->get_record('hvp_xapi_results', array('id' => $subContentId));

        // Get the sum of all the OEQ scores with the same parent.
        $totalgradablesscore = intval($DB->get_field_sql(
            "SELECT SUM(raw_score)
            FROM {hvp_xapi_results}
            WHERE parent_id = ?
            AND additionals = ?", array($answer->parent_id,
            '{"extensions":{"https:\/\/h5p.org\/x-api\/h5p-machine-name":"H5P.FreeTextQuestion"}}')
        ));

        // Get the original raw score from the main content type.
        $baseanswer = $DB->get_record('hvp_xapi_results', array(
            'id' => $answer->parent_id
        ));

        // Get hvp data from content.
        $hvp = $DB->get_record('hvp', array('id' => $cm->instance));
        if (!$hvp) {
            $core::ajaxError('No such content');
            \mod_hvp\local\helper::http_response_code(404);
            return;
        }

        // Set grade using Gradebook API.
        $hvp->rawgrade = $baseanswer->raw_score + $totalgradablesscore;
        $hvp->rawgrademax = $baseanswer->max_score;
        $hvp->cmidnumber = $contextId;
        hvp_grade_item_update($hvp, (object) array(
            'userid' => $answer->user_id
        ));

        // Get the num of ungraded OEQ answers.
        $numungraded = intval($DB->get_field_sql(
            "SELECT COUNT(*)
            FROM {hvp_xapi_results}
            WHERE parent_id = ?
            AND raw_score IS NULL
            AND additionals = ?", array($answer->parent_id,
            '{"extensions":{"https:\/\/h5p.org\/x-api\/h5p-machine-name":"H5P.FreeTextQuestion"}}')
        ));

        $response = [
            'score' => $answer->raw_score,
            'maxScore' => $answer->max_score,
            'totalUngraded' => $numungraded,
        ];
        $core::ajaxSuccess($response);
    }

    /**
     * Fetch score/maxScore for ungraded item + number of ungraded items.
     *
     * @param int $subContentId
     * @throws \dml_exception
     */
    public static function return_subcontent_grade(int $subContentId) {
        global $DB;

        $core = static::get_core();

        // Content parameters.
        $answer = $DB->get_record('hvp_xapi_results', array('id' => $subContentId));

        // Get the num of ungraded OEQ answers.
        $numungraded = intval($DB->get_field_sql(
            "SELECT COUNT(*)
            FROM {hvp_xapi_results}
            WHERE parent_id = ?
            AND raw_score IS NULL
            AND additionals = ?", array($answer->parent_id,
            '{"extensions":{"https:\/\/h5p.org\/x-api\/h5p-machine-name":"H5P.FreeTextQuestion"}}')
        ));

        $response = [
            'score' => $answer->raw_score,
            'maxScore' => $answer->max_score,
            'totalUngraded' => $numungraded,
        ];
        $core::ajaxSuccess($response);
    }
}
