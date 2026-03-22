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
 * @author  Gihan Hewaralalage <gihan.hewaralalage@totara.com>
 * @package totara_program
 */

namespace totara_program\model;

use coding_exception;
use core\orm\entity\model;
use totara_program\entity\program_courseset_course as entity;

class program_courseset_course extends model {
    /**
     * @var entity
     */
    protected $entity;

    /**
     * @param entity $entity
     */
    private function __construct(entity $entity) {
        parent::__construct($entity);
    }

    /**
     * @return string
     */
    protected static function get_entity_class(): string {
        return entity::class;
    }

    /**
     * @param entity $entity
     * @return program_courseset_course
     */
    public static function from_entity(entity $entity): program_courseset_course {
        if (!$entity->exists()) {
            throw new coding_exception("Cannot instantiate a program_courseset_course from a non-existing entity");
        }

        return new program_courseset_course($entity);
    }

    /**
     * @param int $coursesetid
     * @param int $courseid
     * @param int $sortorder
     * @return program_courseset_course
     * @throws coding_exception
     */
    public static function update_sort_order(
        int $coursesetid,
        int $courseid,
        int $sortorder
    ): program_courseset_course {
        $entity = new entity();
        $entity->coursesetid = $coursesetid;
        $entity->courseid = $courseid;
        $entity->sortorder = $sortorder;

        $entity->save();

        return static::from_entity($entity);
    }

    /**
     * @param int $coursesetid
     * @param int $courseid
     * @param int $sortorder
     * @return program_courseset_course
     * @throws coding_exception
     */
    public static function create(
        int $coursesetid,
        int $courseid,
        int $sortorder
    ): program_courseset_course {
        $entity = new entity();
        $entity->coursesetid = $coursesetid;
        $entity->courseid = $courseid;
        $entity->sortorder = $sortorder;

        $entity->save();

        return static::from_entity($entity);
    }

    /**
     * Remove course from course set
     * @return self
     */
    public function delete(): self {
        $this->entity->delete();
        return $this;
    }

    /**
     * Get all entity properties
     *
     * @return array
     */
    public function to_array(): array {
        return $this->entity->to_array();
    }

    /**
     * @return void
     */
    public function save(): void {
        $this->entity->save();
    }

    /**
     * @return bool
     */
    public function exists(): bool {
        return $this->entity->exists();
    }

    /**
     * Check whether a given course has completion enabled and at least one criteria set up.
     * i.e. whether the course is actually "completable"
     *
     * @param int $course - The id of the course to check completion settings for
     * @param bool $check_criteria - If true will also check the course has at least 1 completion criteria set up
     * @return bool
     */
    public static function check_course_completion(int $course_id, $check_criteria = false): bool {
        global $DB;

        $sql = "SELECT crs.id
                  FROM {course} crs
                 WHERE crs.id = :course_id
                   AND crs.enablecompletion = :enable";

        $params = [
            'course_id' => $course_id,
            'enable' => COMPLETION_ENABLED
        ];

        // Append a check for criteria.
        if ($check_criteria) {
            $sql .= "
               AND EXISTS ( SELECT crit.id
                              FROM {course_completion_criteria} crit
                             WHERE crit.course = crs.id
                          )";
        }

        return $DB->record_exists_sql($sql, $params);
    }

    /**
     * Check if the course has the program enrolment plugin, and add it if not.
     *
     * @return void
     */
    public function add_enrolment_plugin(): void {
        $program_plugin = enrol_get_plugin('totara_program');


        // Check if the program enrolment plugin is already enabled on this course.
        $instance = $program_plugin->get_instance_for_course($this->entity->courseid);
        if (!$instance) {
            // If it isn't, add it.
            $course = (object) ['id' => $this->entity->courseid];
            $program_plugin->add_instance($course);
        }
    }

    /**
     * If the course is no longer associated with any programs, remove the program enrolment plugin.
     *
     * @param array $course_ids - An array of course ids to check
     * @return void
     */
    public static function remove_enrolment_plugin(array $course_ids): void {
        global $DB;

        $program_plugin = enrol_get_plugin('totara_program');

        if (empty($course_ids)) {
            return; // Nothing to do here.
        }

        // Check whether any of the courses are still associated with programs
        list($insql, $inparams) = $DB->get_in_or_equal($course_ids);
        $sql = "SELECT c.*
                  FROM {prog_courseset_course} pcc
            INNER JOIN {course} c
                    ON c.id = pcc.courseid
                 WHERE c.id {$insql}";
        $still_associated = $DB->get_records_sql($sql, $inparams);

        // Remove the program enrolment plugin from courses no longer associated with any programs.
        $courses_to_remove_plugin_from = array_diff($course_ids, array_keys($still_associated));
        foreach ($courses_to_remove_plugin_from as $course_id) {
            $instance = $program_plugin->get_instance_for_course($course_id);
            if ($instance) {
                $program_plugin->delete_instance($instance);
            }
        }
    }
}
