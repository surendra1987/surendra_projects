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
 * @package core_course
 */

namespace core_course;

use container_course\interactor\course_interactor;
use context_coursecat;
use context_user;
use core\entity\course;
use core\entity\user;
use core\exception\unresolved_record_reference;
use core\webapi\reference\base_record_reference;
use core\record\tenant;
use stdClass;

final class course_record_reference extends base_record_reference {

    /**
     * @inheritDoc
     */
    protected array $refine_columns = ['id', 'idnumber', 'shortname'];

    /**
     * @inheritDoc
     */
    protected function get_table_name(): string {
        return course::TABLE;
    }

    /**
     * @inheritDoc
     */
    protected function get_entity_name(): string {
        return 'Course';
    }

    /**
     * @inheritDoc
     */
    protected function convert_ref_columns_to_conditions(array $ref_columns = []): array {
        $conditions = parent::convert_ref_columns_to_conditions($ref_columns);
        $conditions['containertype'] = "container_course";

        return $conditions;
    }

    /**
     * @param array $ref_columns
     * @param user $actor
     * @return stdClass
     */
    public static function load_for_viewer(array $ref_columns = [], user $actor = null): stdClass {
        global $CFG;

        $course_record_reference = new self();
        $record = $course_record_reference->get_record($ref_columns);

        if (is_null($actor)) {
            $actor = user::logged_in();
        }

        if ($CFG->tenantsenabled && $actor->tenantid) {
            $user_context = context_user::instance($actor->id);
            $tenant = tenant::fetch($user_context->tenantid);

            $can_view = false;
            // Check whether the course is under tenant child category.
            foreach ((context_coursecat::instance($tenant->categoryid))->get_child_contexts() as $context) {
                if ($context->instanceid == $record->category) {
                    $can_view = true;
                }
            }

            // Check whether the course is the same tenancy with tenant api user.
            if (!$can_view && $record->category === $tenant->categoryid) {
                $can_view = true;
            }

            if (!$can_view) {
                throw new unresolved_record_reference('You do not have capabilities to view a target course.');
            }
        }

        $course_interactor = course_interactor::from_course_id($record->id, $actor->id);
        if (!$course_interactor->can_view()) {
            throw new unresolved_record_reference('You do not have capabilities to view a target course.');
        }

        if (!$record->visible && !$course_interactor->can_view_hidden_course()) {
            throw new unresolved_record_reference('You do not have capabilities to view a hidden course.');
        }

        return $record;
    }
}