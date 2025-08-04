<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package format_pathway
 */

namespace format_pathway\webapi\resolver\mutation;

use coding_exception;
use container_course\interactor\course_interactor;
use core\webapi\execution_context;
use core\webapi\middleware\require_login_course;
use core\webapi\mutation_resolver;
use core_enrol\enrolment_approval_helper;
use format_pathway\webapi\resolver\middleware\validate_format_pathway_course;

/**
 *
 * Mutation to request non-interactive enrol for the pathway course format.
 *
 */
class request_non_interactive_enrol_v2 extends mutation_resolver {
    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $DB;

        $course_id = $args['course_id'];
        if (!isset($course_id)) {
            throw new coding_exception("Missing required field {$course_id}");
        }

        // The following code duplicates much of the code from container_course\external::process_non_interactive_enrol and should be fixed in future.
        $interactor = course_interactor::from_course_id($course_id);

        if ($interactor->is_enrolled() && $interactor->is_enrolled_not_pending_approval()) {
            throw new coding_exception('You have already enroled.');
        }

        if ($interactor->is_site_guest()) {
            throw new coding_exception('Site guest can not request self enrol');
        }

        // If self enrol configuration has been customised, you cannot do self enrol
        if (!$interactor->supports_non_interactive_enrol()) {
            throw new coding_exception('Not support non interactive enrol');
        }

        $instances = enrol_get_instances($course_id, true);

        $result = false;
        foreach($instances as $instance) {
            if ($plugin = enrol_get_plugin($instance->enrol)) {
                $result = $plugin->do_non_interactive_enrol($instance, $interactor->get_actor_id());
                if ($result) {
                    // If approval is available, try to get the application url as the result.
                    if (enrolment_approval_helper::approval_available_for($instance->id, $interactor->get_actor_id())) {
                        $result = enrolment_approval_helper::get_application_url_after_non_interactive_enrolment($instance->id, $interactor->get_actor_id());

                        if ($result) {
                            return [
                                'redirect_url' => $result,
                                'success' => true
                            ];
                        }
                    }
                    break;
                }
            }
        }

        // If no enrol plugin supports non interactive enrol, we throw exception.
        if (!$result) {
            throw new coding_exception('No enrol plugin supports non interactive enrol');
        }

        return [
            'redirect_url' => '',
            'success' => true
        ];
    }

    /**
     * @inheritDoc
     */
    public static function get_middleware(): array {
        return [
            new require_login_course('course_id'),
            new validate_format_pathway_course('course_id')
        ];
    }
}