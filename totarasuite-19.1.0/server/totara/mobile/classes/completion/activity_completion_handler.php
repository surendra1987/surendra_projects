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
 * @author Katherine Galano <katherine.galano@totara.com>
 * @package totara_mobile
 */

namespace totara_mobile\completion;

use completion_info;
use core\orm\query\builder;
use totara_mobile\download\download_helper;
use totara_mobile\exception\activity_completion_exception;

class activity_completion_handler {
    /**
     * Preventing this class from being constructed
     */
    private function __construct() {
    }

    /**
     * Encapsulates the whole activity completion process for any activity type.
     *
     * @param string $modname
     * @param sync_activity_completion_input $sync_activity_completion_input
     *
     * @return void
     * @throws activity_completion_exception
     */
    public static function sync_activity_completion(string $modname, sync_activity_completion_input $sync_activity_completion_input): void {
        $handler = self::instance($modname);
        $completion_info = new \completion_info($sync_activity_completion_input->get_course());

        $handler->do_validation($modname, $sync_activity_completion_input, $completion_info);
        // Input is validated already and do sync.
        $handler->do_sync_completion($sync_activity_completion_input, $completion_info);
    }

    /**
     * Get activity type handler.
     *
     * @param string $modname
     * @return activity_completion_handler
     * @throws activity_completion_exception
     */
    private static function instance(string $modname): self {
        // Child class can define validate(), automatic_sync_completion() and pre_sync_completion() to customize validation or implement specific completion.
        $class = "mod_{$modname}\\completion\\{$modname}_completion_handler";
        if (class_exists($class)) {
            if (!is_a($class, activity_completion_handler::class, true)) {
                throw new activity_completion_exception('Must extend activity_completion_handler');
            }
            return new $class();
        }

        return new self();
    }

    /**
     *
     * Validate input.
     * This method cannot be overriden. Implement a validation() method to perform additional validation if necessary.
     *
     * @param string $modname
     * @param int $completion_tracking_value
     * @param completion_info $completion_info
     *
     * @return void
     */
    final protected function do_validation(string $modname, sync_activity_completion_input $sync_activity_completion_input, completion_info $completion_info): void {
        $cm = $sync_activity_completion_input->get_cm();
        $target_state = $sync_activity_completion_input->get_completed();
        $completion_tracking_value = $sync_activity_completion_input->get_completion_tracking();

        if (strcmp($modname, $cm->modname) !== 0) {
            throw new activity_completion_exception('Endpoint activity type mismatch');
        }

        if (!download_helper::is_activity_downloadable($cm)) {
            throw new activity_completion_exception('Activity not downloadable');
        }

        // Check if completion is enabled site-wide, or for the course and allow some activities skip this checking.
        if ($completion_info->is_enabled($cm) <= COMPLETION_TRACKING_NONE && !$this->can_sync_when_disabled()) {
            throw new activity_completion_exception('Activity completion not enabled');
        }

        // In general, if an activity does not support track_view, the completion value can not be automatic
        if (!plugin_supports('mod', $modname, FEATURE_COMPLETION_TRACKS_VIEWS) &&
            $completion_tracking_value === COMPLETION_TRACKING_AUTOMATIC
        ) {
            throw new activity_completion_exception("Activity completion tracking can not be automatic");
        }

        if (is_null($target_state) && $completion_tracking_value  === COMPLETION_TRACKING_MANUAL) {
            throw new activity_completion_exception("Activity completion tracking can not be manual");
        }

        if (is_bool($target_state) && $completion_tracking_value  === COMPLETION_TRACKING_AUTOMATIC) {
            throw new activity_completion_exception("Activity completion tracking can not be automatic");
        }

        if ($completion_tracking_value === COMPLETION_TRACKING_NONE && is_bool($target_state)) {
            throw new activity_completion_exception("Activity completion tracking can not be none");
        }

        if ($cm->completion != $completion_tracking_value) {
            throw new activity_completion_exception("Completed value is invalid");
        }

        // Allow child class customize validation.
        if (method_exists($this, 'validation')) {
            $this->validation($sync_activity_completion_input, $completion_info);
        }
    }

    /**
     * Sync activity completion state.
     *
     * This method cannot be overriden. Implement pre_sync_completion() and/or automatic_sync_completion() methods if necessary.
     *
     * @param sync_activity_completion_input $input
     * @param completion_info $completion_info
     *
     * @return void
     */
    final protected function do_sync_completion(sync_activity_completion_input $sync_activity_completion_input, completion_info $completion_info): void {
        global $CFG;
        require_once $CFG->dirroot . '/course/modlib.php';
        require_once $CFG->libdir.'/gradelib.php';

        builder::get_db()->transaction(function () use ($completion_info, $sync_activity_completion_input) {
            $cm = $sync_activity_completion_input->get_cm();
            $completion_tracking = $sync_activity_completion_input->get_completion_tracking();

            $cm_info = \cm_info::create($cm);
            //Can be overridden to sync extra data such as attempts, before completion sync has done.
            if (method_exists($this, 'pre_sync_completion')) {
                $this->pre_sync_completion($cm_info, $sync_activity_completion_input);
            }

            if ($completion_tracking === COMPLETION_TRACKING_MANUAL) {
                $completion_info->update_state($cm, $sync_activity_completion_input->get_completed());
                return;
            }

            if ($completion_tracking === COMPLETION_TRACKING_AUTOMATIC) {
                $completion_info->set_module_viewed($cm);

                // Each activity module will have its own rules for further handling activity completion.
                if (method_exists($this, 'automatic_sync_completion')) {
                    // Third argument is to indicate the 'require grade is enabled or not'
                    $this->automatic_sync_completion($cm_info, $completion_info, !is_null($cm->completiongradeitemnumber));
                }
            }
        });
    }

    /**
     * Some activities need to sync some extra data such as attempt even if completion is disabled
     *
     * @return bool
     */
    public function can_sync_when_disabled(): bool {
        return false;
    }
}