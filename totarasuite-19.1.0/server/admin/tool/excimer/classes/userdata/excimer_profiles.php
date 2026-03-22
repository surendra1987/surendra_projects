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
 * @author Maria Torres <maria.torres@totara.com>
 * @package tool_excimer
 */

namespace tool_excimer\userdata;

use tool_excimer\profile;
use totara_userdata\userdata\export;
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;

/**
 * GDPR implementation.
 */
final class excimer_profiles extends item {
    /**
     * @param int $user_status
     * @return bool
     */
    public static function is_purgeable(int $user_status): bool {
        return true;
    }

    /**
     * @return bool
     */
    public static function is_exportable(): bool {
        return true;
    }

    /**
     * @return bool
     */
    public static function is_countable(): bool {
        return true;
    }

    /**
     * @param target_user $user
     * @param \context $context
     *
     * @return export
     */
    protected static function export(target_user $user, \context $context): export {
        global $DB;

        // Get profiles created by the user.
        $rows = $DB->get_records(profile::TABLE, ['userid' => $user->id]);
        $data = [];
        foreach ($rows as $row) {
            $profile = [
                'userid' => $user->id,
                'useragent' => $row->useragent,
                'request' => $row->request,
                'pathinfo' => $row->pathinfo,
                'parameters' => $row->parameters,
                'referer' => $row->referer,
                'duration' => $row->duration,
                'courseid' => $row->courseid,
                'timecreated' => $row->timecreated,
            ];

            // Add lock reason if the user modified the profile and provided a reason.
            if ($row->usermodified === $user->id && !empty($row->lockreason)) {
                $profile['usermodified'] = $user->id;
                $profile['lockreason'] = $row->lockreason;
                $profile['timemodified'] = $row->timemodified;
            }
            $data[] = $profile;
        }

        // NOTE: If a user modified the profile but wasn't the one who created... should we add the profile?
        // If so, how much data should we add?

        $export = new export();
        $export->data = $data;

        return $export;
    }

    /**
     * @param target_user $user
     * @param \context $context
     *
     * @return int
     */
    protected static function count(target_user $user, \context $context): int {
        global $DB;

        return $DB->count_records(profile::TABLE, ['userid' => $user->id]);
    }

    /**
     * Purge records for a user.
     *
     * @param target_user $user
     * @param \context $context
     *
     * @return int
     */
    protected static function purge(target_user $user, \context $context): int {
        global $DB;

        // Add conditional if we decide to add course context here.
        $DB->delete_records(profile::TABLE, ['userid' => $user->id]);
        $DB->set_field(profile::TABLE, 'usermodified', 0, ['usermodified' => $user->id]);

        return self::RESULT_STATUS_SUCCESS;
    }

    /**
     * NOTE: For the moment, we will be checking only system context, but I believe we may want to
     * include CONTEXT_COURSE here as courseid is also a column in the profile table?
     *
     * @return array
     */
    public static function get_compatible_context_levels(): array {
        return [CONTEXT_SYSTEM];
    }
}