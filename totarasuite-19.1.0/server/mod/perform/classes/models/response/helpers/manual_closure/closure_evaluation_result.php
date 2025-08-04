<?php
/*
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\response\helpers\manual_closure;

/**
 * Convenience enum indicating whether a participant's activity can be manually
 * closed.
 */
enum closure_evaluation_result : string {
    // Manual closure is disabled for the participant's activity.
    case NOT_APPLICABLE = 'NOT_APPLICABLE';

    // Participant can manually close the participant instance.
    case ALLOWED = 'ALLOWED';

    // Participant CANNOT manually close the participant instance because it's already closed.
    case ALREADY_CLOSED = 'ALREADY_CLOSED';

    // Participant CANNOT manually close the participant instance; mandatory questions have not
    // been submitted yet. Activity remains open.
    case UNANSWERED_MANDATORY_QUESTIONS = 'UNANSWERED_MANDATORY_QUESTIONS';

    // Logged-in user CANNOT manually close someone else's participant instance.
    case INSUFFICIENT_PERMISSIONS = 'INSUFFICIENT_PERMISSIONS';

    /**
     * Indicates if this enum is considered as a failed result.
     *
     * @return bool true if the enum is a failed result.
     */
    public function passed(): bool {
        return $this === self::ALLOWED;
    }

    /**
     * Returns a localized string describing this enum.
     *
     * @return string the description.
     */
    public function description(): string {
        $lang_key = 'manual_closure_condition_result:' . strtolower($this->name);
        return get_string($lang_key, 'mod_perform');
    }
}
