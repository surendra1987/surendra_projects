<?php
/**
 * This file is part of Totara Core
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_job
 */

namespace totara_job\webapi\resolver\query;

use core\entity\user;
use core\exception\unresolved_record_reference;
use core\reference\user_record_reference;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use totara_job\reference\job_assignment_record_reference;
use totara_job\webapi\resolver\helper;

/**
 * Query to fetch a specific job assignment for the External API.
 */
class job_assignment extends query_resolver {
    use helper;

    /**
     * {@inheritdoc}
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG;
        require_once($CFG->dirroot . '/totara/job/lib.php');

        $api_user = user::logged_in();
        $job_assignment_reference = new job_assignment_record_reference();
        try {
            $target_job_assignment = ($args['target_job'] ?? []);
            // If a user reference for the job_assignment was passed in, find the user's id.
            if (array_key_exists('user', $target_job_assignment) && is_array($target_job_assignment['user'])) {
                $target_user = user_record_reference::load_for_viewer($target_job_assignment['user'], $api_user);
                // If user is not found, an unresolved_record_reference would have been thrown.
                $target_job_assignment['userid'] = $target_user->id;
            }
            $job_assignment_record = $job_assignment_reference->get_record($target_job_assignment);
        } catch (unresolved_record_reference $exception) {
            return [
                'job_assignment' => null,
                'found' => false,
            ];
        }

        $job_assignment_user_obj = self::get_user_from_args(['userid' => $job_assignment_record->userid], 'userid', false);

        // Check permissions for the user that the job assignment belongs to.
        // If multi-tenancy has been implemented this should be restricted to the client's tenant if the request comes from
        // a tenant client - done by the capability checks below.
        if (!totara_job_can_view_job_assignments($job_assignment_user_obj)) {
            return [
                'job_assignment' => null,
                'found' => false,
            ];
        }

        $job_assignment_model = \totara_job\job_assignment::get_with_id($job_assignment_record->id);
        if (!$job_assignment_model) {
            return [
                'job_assignment' => null,
                'found' => false,
            ];
        }

        return [
            'job_assignment' => $job_assignment_model,
            'found' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_authenticated_user()
        ];
    }
}
