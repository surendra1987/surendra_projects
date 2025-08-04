<?php
/*
 * This file is part of Totara Learn
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
 * @author David Curry <david.curry@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;

use context_user;
use core\entity\user;
use core\webapi\middleware\reopen_session_for_writing;
use coursecat as category;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use moodle_exception;

class course_selector extends query_resolver {

    /**
     * @param array $args
     * @param execution_context $ec
     * @return array
     * @throws moodle_exception
     */
    public static function resolve(array $args, execution_context $ec): array {
        global $CFG;

        // Set the relevant context to the current user context if not set already.
        if (!$ec->has_relevant_context()) {
            $user_context = context_user::instance(user::logged_in()->id);
            $ec->set_relevant_context($user_context);
        }

        // Check if we need to have a tenant restriction.
        $filter_for_no_tenant = false;
        $context = $ec->get_relevant_context();
        if ($context->tenantid) {
            // Relevant context is tied to a tenant.
            $tenant_id = $context->tenantid;
        } else {
            // We can only allow filtering for tenant_id if the relevant context is not already tied to a tenant.
            $tenant_id = $args['input']['filters']['tenant_id'] ?? null;

            // When the tenant_id filter is passed in as null, it means we want to exclude tenant courses when isolation is on.
            // This needs extra handling because it's not the same as e.g. course visibility for system users (they can also see tenant courses when isolation is on).
            if (isset($args['input']['filters']) && array_key_exists('tenant_id', $args['input']['filters'])) {
                $filter_for_no_tenant = is_null($args['input']['filters']['tenant_id']);
            }
        }

        $search = $args['input']['filters']['search'] ?? null;
        $category_id = $args['input']['filters']['category_id'] ?? null;
        $completion_tracked = $args['input']['filters']['completion_tracked'] ?? null;

        if (!empty($search) && !empty($category_id)) {
            // Get all the courses matching the search and cache.
            $search_options = [
                'raw_data' => true,
                'completion_tracked' => $completion_tracked
            ];
            $courses = category::search_courses(['search' => $search], $search_options);

            // Now get a simplified list of courses in the selected category.
            $cat_options = [
                'idonly' => true,
                'recursive' => true,
                'completion_tracked' => $completion_tracked
            ];
            $category = category::get($category_id);
            $cat_courseids = $category->get_courses($cat_options);

            // Match search against category courses.
            $courses = array_filter(
                $courses,
                function ($course) use ($cat_courseids) {
                    return in_array($course->id, $cat_courseids);
                }
            );
        } else if (!empty($category_id)) {
            $options = [
                'recursive' => true,
                'raw_data' => true,
                'completion_tracked' => $completion_tracked
            ];
            $category = category::get($category_id);
            $courses = $category->get_courses($options);
        } else {
            // Use this even if search is empty to use the caching.

            $options = [
                'raw_data' => true,
                'completion_tracked' => $completion_tracked
            ];
            $courses = category::search_courses(['search' => $search], $options);
        }

        if (!empty($CFG->tenantsenabled)) {
            if ($tenant_id) {
                $courses = array_filter(
                    $courses,
                    function ($course) use ($tenant_id, $CFG) {
                        $course_context = \context_course::instance($course->id);
                        if (!empty($CFG->tenantsisolated)) {
                            return $tenant_id == $course_context->tenantid;
                        }
                        return is_null($course_context->tenantid) || $tenant_id == $course_context->tenantid;
                    }
                );
            } elseif ($filter_for_no_tenant) {
                // When it's under system category with isolation on, filter out all tenant courses.
                if (!empty($CFG->tenantsisolated)) {
                    $courses = array_filter(
                        $courses,
                        function ($course) {
                            $course_context = \context_course::instance($course->id);
                            return is_null($course_context->tenantid);
                        }
                    );
                }
            }
        }

        // Now some quick pagination.
        $paginator = $args['input']['pagination'];
        $offset = $paginator['page'] ?? 0; // Should be handled by frontend, but default to the first page.
        $per_page = $paginator['limit'] ?? 20; // Should be handled by frontend, but default to 20.

        $index = 0;
        $items = [];
        $start = $offset * $per_page;
        $limit = $start + $per_page;
        foreach ($courses as $course) {
            if ($index >= $start && $index < $limit) {
                $items[] = $course;
            }

            // Increment and break out of loop.
            if ($index++ >= $limit) {
                break;
            }
        }

        return [
            'courses' => $items,
            'total' => count($courses),
            'page' => $offset + 1,
            'next_cursor' => "$limit"
        ];
    }

    /**
     * @return string[]
     */
    public static function get_middleware(): array {
        return [
            reopen_session_for_writing::class,
            require_login::class
        ];
    }
}
