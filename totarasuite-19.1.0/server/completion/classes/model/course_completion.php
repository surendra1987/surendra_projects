<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Riana Rossouw <riana.rossouw@totaralearning.com>
 * @package core_completion
 */

namespace core_completion\model;

use cache;
use core\entity\course_completion as course_completion_entity;
use core\orm\entity\model;
use core\orm\query\builder;
use core_completion\helper as course_completion_helper;
use core_course\model\course as course_model;

/**
 * Class course_completion
 * This is a model that represent a course_completion record
 *
 * @package core_completion\model
 */
class course_completion extends model {

    /**
     * constructor. It's here for the purpose of type-hint
     *
     * @param course_completion_entity $entity
     */
    public function __construct(course_completion_entity $entity) {
        parent::__construct($entity);
    }

    protected static function get_entity_class(): string {
        return course_completion_entity::class;
    }

    /**
     * Recalculate and update the due date
     */
    public function update_duedate(): void {
        /** @var course_completion_entity $course_completion */
        $course_completion = $this->entity;
        /** @var course_model $course */
        $course_entity = $course_completion->course_instance;
        $course_model = course_model::load_by_entity($course_entity);

        if ($course_entity->enablecompletion != COMPLETION_ENABLED || (!$course_entity->duedate && !$course_entity->duedateoffsetunit)) {
            // Nothing to see here, carry on.
            return;
        }

        if ($course_entity->duedate) {
            // Static duedate.
            $updated_duedate = $course_entity->duedate;
        } else {
            if (!empty($course_completion->timeenrolled)) {
                $updated_duedate = $course_model->calculate_duedate_from_time($course_completion->timeenrolled);
            } else {
                debugging("course enrolment duedate could not be updated for user:{$course_completion->userid} in course:{$course_completion->course} due to missing timeenrolled field");
                return;
            }
        }

        if ($course_completion->duedate != $updated_duedate) {
            $course_completion->duedate = $updated_duedate;
            $course_completion->save();

            // Delete any cached value for this user
            $cache = cache::make('core', 'coursecompletion');
            $key = $course_completion->userid . '_' . $course_completion->course;
            $cache->delete($key);

            course_completion_helper::save_completion_log(
                $course_completion->course,
                $course_completion->userid,
                "Due date updated in update_duedate to " . course_completion_helper::format_log_date($updated_duedate)
            );
        }
    }

    /**
     * @param int $course_id
     * @return array
     */
    public static function load_by_course_id(int $course_id): array {
        return builder::table(course_completion_entity::TABLE)
            ->where('course', $course_id)
            ->get()
            ->map_to(function ($course_completion) {
                return self::load_by_id($course_completion->id);
            })->all();
    }
}
