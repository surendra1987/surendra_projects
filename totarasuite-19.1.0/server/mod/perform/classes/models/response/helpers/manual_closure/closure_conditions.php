<?php
/*
 * This file is part of Totara Perform
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
 * @author Murali Nair <murali.nair@totara.com>
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\response\helpers\manual_closure;

use core\collection;
use core\entity\user;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\participant;
use mod_perform\state\participant_instance\open;
use mod_perform\state\participant_section\open as participant_section_open;

/**
 * Holds the conditions required for manual closure of a participant instance
 * by the participant.
 *
 * The default conditions for allowing manual closures are:
 * 1) activity's manual_close setting must be enabled
 * 2) participant's activity is still open ie participant instance availability
 *    is 'open'
 * 3) all required questions in the participant activity have been answered
 */
final class closure_conditions {
    /**
     * Virtual constructor.
     *
     * @param participant_instance $participant_instance participant instance whose activity is to be checked.
     */
    public static function create(participant_instance $participant_instance): closure_conditions {
        $verifiers = [
            self::verify_manual_close_setting(...),
            self::verify_user_can_manually_close(...),
            self::verify_participant_instance_availability(...),
            self::verify_required_questions_completed(...),
        ];

        return new self($participant_instance, collection::new($verifiers));
    }

    /**
     * Checks if the participant activity's manual close setting is enabled.
     *
     * @param participant_instance $participant_instance participant instance whose activity is to be checked.
     *
     * @return closure_evaluation_result the result for _this_ specific condition.
     */
    private static function verify_manual_close_setting(
        participant_instance $participant_instance
    ): closure_evaluation_result {
        $setting = (bool)$participant_instance->subject_instance->activity->settings->lookup(
            activity_setting::MANUAL_CLOSE,
            false
        );

        return $setting === true
            ? closure_evaluation_result::ALLOWED
            : closure_evaluation_result::NOT_APPLICABLE;
    }

    /**
     * Checks if the participant instance is open.
     *
     * @param participant_instance $participant_instance participant instance whose availability is to be checked.
     * @return closure_evaluation_result the result for _this_ specific condition.
     */
    private static function verify_participant_instance_availability(
        participant_instance $participant_instance
    ): closure_evaluation_result {
        return $participant_instance->availability_state::get_code() === open::get_code()
            ? closure_evaluation_result::ALLOWED
            : closure_evaluation_result::ALREADY_CLOSED;
    }

    /**
     * Checks if the participant has answered all the required questions in the activity.
     *
     * @param participant_instance $participant_instance
     * @return closure_evaluation_result the result for _this_ specific condition.
     */
    private static function verify_required_questions_completed(
        participant_instance $participant_instance
    ): closure_evaluation_result {
        foreach ($participant_instance->participant_sections as $participant_section) {
            if ((int)$participant_section->availability !== participant_section_open::get_code()) {
                // The $participant_section is either closed or not applicable (i.e. participant is view-only).
                // So skip the checking of whether required questions have been answered.
                // The participant should still be able to manually close their activity participant instance.
                continue;
            }

            // Note: If a participant_section's progress is not complete, it means it was only 'saved as draft', not
            // properly submitted.
            if ($participant_section->get_has_unanswered_required_element()) {
                return closure_evaluation_result::UNANSWERED_MANDATORY_QUESTIONS;
            }
        }

        return closure_evaluation_result::ALLOWED;
    }

    /**
     * Checks if logged-in user can manually close their participant instance.
     *
     * @param participant_instance $participant_instance
     * @return closure_evaluation_result
     */
    private static function verify_user_can_manually_close(
        participant_instance $participant_instance
    ): closure_evaluation_result {
        /** @var participant $participant */
        $participant = $participant_instance->get_participant();

        if ($participant->is_external()) {
            return closure_evaluation_result::ALLOWED;
        }

        if ((int)user::logged_in()?->id === (int)$participant->user->id) {
            return closure_evaluation_result::ALLOWED;
        }

        return closure_evaluation_result::INSUFFICIENT_PERMISSIONS;
    }

    /**
     * Default constructor.
     *
     * @param participant_instance $participant_instance associated participant instance.
     * @param collection<callable> $verifiers complete set of verifier functions
     *        to evaluate in turn. If any of these verifiers fail, the participant instance
     *        cannot be manually closed by the participant.
     */
    private function __construct(
        private readonly participant_instance $participant_instance,
        private readonly collection $verifiers
    ) {
        // EMPTY BLOCK.
    }

    /**
     * Check whether the associated participant instance can be manually closed.
     *
     * @return closure_evaluation_result indicates whether the participant's activity can be closed.
     */
    public function evaluate(): closure_evaluation_result {
        return $this->verifiers->reduce(
            fn (closure_evaluation_result $status, callable $verify): closure_evaluation_result
                => $status->passed()
                    ? $verify($this->participant_instance)
                    : $status,
            closure_evaluation_result::ALLOWED
        );
    }
}
