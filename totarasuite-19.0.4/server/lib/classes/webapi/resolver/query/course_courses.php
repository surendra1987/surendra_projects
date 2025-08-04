<?php
/**
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package core
 */

namespace core\webapi\resolver\query;


use core\entity\course_categories;
use core\entity\user;
use core\exception\course_courses_exception;
use core\orm\collection;
use core\orm\query\order;
use core\pagination\cursor;
use core\webapi\execution_context;
use core\webapi\middleware\require_authenticated_user;
use core\webapi\query_resolver;
use core_course\data_provider\course as course_data_provider;
use core_tag\entity\tag_instance;
use totara_core\data_provider\provider;
use core_course\entity\filter\course_filter_factory;

/**
 * The external API query for retrieving a list of all courses.
 * Optional argument $pagination allows user to specify how many courses to
 * be returned in a single call.
 */
class course_courses extends query_resolver {

    /**
     * @inheritDoc
     */
    public static function resolve(array $args, execution_context $ec) {
        global $CFG;

        // Pagination array contains keys: cursor (str), limit (int), page (int)
        $decoy_pagination = ['limit' => null, 'cursor' => null, 'page' => null];
        $pagination = $args['query']['pagination'] ?? $decoy_pagination;
        $sort = $args['query']['sort'] ?? [];
        $filter = $args['query']['filters'] ?? [];

        $order_by = null;
        $order_dir = null;
        if (!empty($sort)) {
            if (count($sort) > 1) {
                throw new course_courses_exception("Sorting by more than one column is not currently supported.");
            }

            if (empty($sort[0]['column'])) {
                throw new course_courses_exception("Required parameter 'column' not being passed.");
            }

            $sort = reset($sort);
            $order_by = $sort['column'];
            $order_dir = $sort['direction'] ?? order::DIRECTION_ASC;
        }

        $user = user::logged_in();
        $filters = [];
        if (!empty($CFG->tenantsenabled) && $user->tenantid) {
            $tenant = \core\record\tenant::fetch($user->tenantid);

            $category_ids = [$tenant->categoryid];

            $context_coursecat = \context_coursecat::instance($tenant->categoryid);
            foreach ($context_coursecat->get_child_contexts() as $context) {
                if ($context->contextlevel == CONTEXT_COURSECAT) {
                    $category_ids[] = $context->instanceid;
                }
            }

            // Exclude system categories.
            $filters['category_ids'] = course_categories::repository()
                ->where_in('id', $category_ids)
                ->where('issystem', 0)
                ->get()->pluck('id');
        }

        if (isset($filter['tag_filter'])) {
            if (empty($CFG->usetags)) {
                throw new course_courses_exception('Tag feature is disabled.');
            }

            $item_ids = tag_instance::repository()->get_item_ids_by_tag_rawname($filter['tag_filter'], 'course', 'core');
            $filters['ids'] = $item_ids;
        }

        if (isset($filter['since_timecreated'])) {
            $filters['since_timecreated'] = $filter['since_timecreated'];
        }

        if (isset($filter['since_timemodified'])) {
            $filters['since_timemodified'] = $filter['since_timemodified'];
        }

        $courses = course_data_provider::create_with_course_visibility(new course_filter_factory())
            ->set_filters($filters)
            ->set_order($order_by, $order_dir)
            ->set_page_size($pagination['limit'] ?? provider::DEFAULT_PAGE_SIZE)
            ->fetch_paginated($pagination['cursor'] ? cursor::decode($pagination['cursor']) : null);

        if (isset($courses['items'])) {
            $courses_collection = collection::new($courses['items']);
            $course_ids = $courses_collection->pluck('id');
            // grab the custom fields related to the courses
            $custom_fields = customfield_get_custom_fields_and_data_for_items('course', $course_ids);
            // add the custom fields to the execution context for the course type resolver
            $ec->set_variable('custom_fields', $custom_fields);
        }

        return $courses;
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