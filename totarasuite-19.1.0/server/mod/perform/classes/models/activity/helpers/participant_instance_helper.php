<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\activity\helpers;

use DateTimeImmutable;
use core\entity\user;
use core\orm\query\builder;
use core\orm\collection;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_instance\not_started;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\subject_instance;
use mod_perform\models\activity\participant_source;
use mod_perform\models\activity\participant_instance as participant_instance_model;
use lang_string;

class participant_instance_helper {

    /**
     * Get participant instances that still need work (are not started or in progress) and the given user
     * is NOT the subject of the activity.
     *
     * @param user $user
     * @param int $cut_off_years Dismiss activities that haven't been touched after this time.
     * @return collection|participant_instance_model[]
     */
    public static function action_required_for(user $user, int $cut_off_years = 2): collection {
        $cut_off_timestamp = (new DateTimeImmutable())
            ->setTimestamp(time() - ($cut_off_years * YEARSECS))
            ->setTime(0, 0)
            ->getTimestamp();

        $latest_participant_section_update = "
            SELECT MAX(ps.progress_updated_at)
            FROM {perform_participant_section} ps
            WHERE ps.participant_instance_id = pi.id
        ";

        return participant_instance::repository()
            ->as('pi')
            ->join([subject_instance::TABLE, 'si'], 'si.id', 'pi.subject_instance_id')
            ->where('si.subject_user_id', '!=', $user->id)
            ->where('pi.participant_id', $user->id)
            ->where('pi.participant_source', participant_source::INTERNAL)
            ->where('pi.access_removed', '0')
            ->where(
                // Only include records that have been created/updated after the cut-off time.
                fn (builder $builder): builder => $builder
                    ->where(
                        fn(builder $builder1): builder => $builder1
                            ->where('pi.progress', not_started::get_code())
                            ->where('pi.created_at', '>=', $cut_off_timestamp)
                    )
                    ->or_where(
                        fn(builder $builder1): builder => $builder1
                            ->where('pi.progress', in_progress::get_code())
                            ->where_raw("($latest_participant_section_update) >= $cut_off_timestamp")

                    )
            )
            ->get()
            ->map_to(participant_instance_model::class);
    }

    /**
     * Finds errors if participant cannot access page.
     *
     * @param participant_instance_model $pi
     * @return null|string
     */
    public static function find_errors_for_page_access(participant_instance_model $pi): ?string {
        if ($pi->access_removed) {
            return new lang_string('remove_participant_instance_access_invalid_url', 'mod_perform');
        }
        return null;
    }

    /**
     * Finds errors if participant cannot access an API endpoint.
     *
     * @param participant_instance_model $participant_instance
     * @param int $user_id  Must not be the id of an external user.
     * @return null|string
     */
    public static function find_errors_for_user_access(participant_instance_model $participant_instance, int $user_id): ?string {
        if (
            (int)$participant_instance->participant_source === participant_source::INTERNAL
            && (int)$participant_instance->participant_id === $user_id
            && $participant_instance->access_removed
        ) {
            // The user making the API request has had their access removed to their participant_instance.
            return new lang_string('remove_participant_instance_access_operation_access_removed', 'mod_perform');
        }
        return null;
    }
}
