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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

namespace engage_course\totara_engage\query\provider;

use core\orm\query\builder;
use engage_course\totara_engage\resource\course;
use totara_engage\query\provider\resource_provider;
use totara_engage\query\query;

final class course_provider extends resource_provider {
    /**
     * @inheritDoc
     */
    public function get_base_builder(): builder {
        global $USER, $CFG;
        require_once($CFG->dirroot . "/totara/coursecatalog/lib.php");

        $builder = parent::get_base_builder();

        // Only want courses
        $builder->where('er.resourcetype', '=', course::get_resource_type());

        // Visibility rules require a join to the context table
        $builder->left_join(
            ['context', 'ctx'],
            function (builder $builder): void {
                $builder->where_field('er.instanceid', 'ctx.instanceid');
                $builder->where('ctx.contextlevel', CONTEXT_COURSE);
            }
        );

        // Join to the courses table for visible courses
        $builder->join(['course', 'c'], function (builder $join) use ($USER) {
            [$totara_visibility_sql, $totara_visibility_params] = totara_visibility_where(
                $USER->id,
                'c.id',
                'c.visible',
                'c.audiencevisible',
                'c'
            );

            $join
                ->where_raw('c.id = er.instanceid')
                ->where(function (builder $subjoin) {
                    // Courses may have null or container_course
                    $subjoin
                        ->where('c.containertype', 'container_course')
                        ->or_where_null('c.containertype');
                })
                ->where_raw($totara_visibility_sql, $totara_visibility_params);
        });

        return $builder;
    }

    /**
     * @inheritDoc
     */
    protected function get_resource_type(): string {
        return course::get_resource_type();
    }

    /**
     * @inheritDoc
     */
    public static function provide_query_type(query $query): bool {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function get_builder(query $query): ?builder {
        if (!$query->is_library_from_workspace() && !($query->is_saved() || $query->is_search())) {
            return null;
        }

        return parent::get_builder($query);
    }
}