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
 * @author David Curry <david.curry@totara.com>
 * @package totara_core
 */

namespace totara_core\webapi\resolver\query;

use core_collator as collator;
use core\webapi\execution_context;
use core\webapi\middleware\require_login;
use core\webapi\query_resolver;
use totara_core\user_learning\item_base;
use totara_core\user_learning\item_helper as learning_item_helper;
use totara_plan\user_learning\item as plan_item;

/**
 * Query to return my programs.
 */
class my_completed_learning extends query_resolver {

    /**
     * Returns the user's completed learning items.
     *
     * @param array $args
     * @param execution_context $ec
     * @return array|item_base[]
     */
    public static function resolve(array $args, execution_context $ec) {
        global $USER;

        // Get all the completed learning items.
        $items = learning_item_helper::get_users_completed_learning_items($USER->id);



        $learning_items = [];
        // Loop through to add component, any other transformations/pre-formatting can happen here.
        foreach ($items as $item) {
            if ($item instanceof plan_item) {
                // We don't need the plan itself, just the contents.
                continue;
            }

            // Note: Persistant queries are <component>_<type> i.e. core_course_course(id);
            $item->itemtype = $item->get_type();
            $item->itemcomponent = $item->get_component(); // totara_certification, totara_program, core_course

            // Make sure we have the percentage in the progress.
            if (method_exists($item, 'get_progress_percentage')) {
                $item->progress = $item->get_progress_percentage();
            }

            // Find the image.
            if (method_exists($item, 'get_image')) {
                $item->image_src = $item->get_image();
            }

            // Make sure the timecompleted is loaded in for sorting.
            if (method_exists($item, 'ensure_completion_loaded')) {
                $item->ensure_completion_loaded();
            }

            $learning_items[] = $item;
        }

        // Sort them by time completed.
        $sort = collator::SORT_NUMERIC | collator::REVERSED;
        collator::asort_objects_by_property($learning_items, 'timecompleted', $sort);

        return $learning_items;
    }

    public static function get_middleware(): array {
        return [
            require_login::class
        ];
    }
}
