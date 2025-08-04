<?php
/**
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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

namespace perform_goal\totara_notification\recipient;

use coding_exception;
use perform_goal\totara_notification\constants;
use totara_notification\recipient\recipient;

final class goal_subject implements recipient {
    /**
     * @inheritDoc
     *
     * @see recipient
     */
    public static function get_name(): string {
        return get_string('notification_recipient_goal_subject', 'perform_goal');
    }

    /**
     * @inheritDoc
     *
     * @see recipient
     *
     * @throws coding_exception if the incoming data is invalid.
     */
    public static function get_user_ids(array $data): array {
        self::validate($data);

        $subject = (int)$data[constants::DATA_GOAL_SUBJECT_UID];
        $commenter = (int)$data[constants::DATA_COMMENTER_UID];

        // No need to send notifications to the goal subject if the subject was
        // the one that created the comment.
        return $subject !== $commenter ? [$subject] : [];
    }

    /**
     * Validates the incoming notification event data.
     *
     * @param array<string,mixed> $data incoming notification event data.
     *
     * @throws coding_exception if the incoming data is invalid
     */
    private static function validate(array $data): void {
        $required = [
            constants::DATA_COMMENTER_UID,
            constants::DATA_GOAL_SUBJECT_UID
        ];

        array_map(
            fn(string $key): int => key_exists($key, $data)
                ? (int)$data[$key]
                : throw new coding_exception(
                    "Incoming recipient data has invalid '$key' value"
                ),
            $required
        );
    }
}