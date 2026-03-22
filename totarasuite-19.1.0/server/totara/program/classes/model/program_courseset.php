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
use totara_program\content\course_set as content;
use totara_program\entity\program_courseset as entity;
use totara_program\entity\program_courseset_course as program_courseset_course_entity;
use totara_program\model\program_courseset_course as program_courseset_course_model;

class program_courseset extends model {
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
     * @return program_courseset
     */
    public static function from_entity(entity $entity): program_courseset {
        if (!$entity->exists()) {
            throw new coding_exception("Cannot instantiate a courseset from a non-existing entity");
        }

        return new program_courseset($entity);
    }

    /**
     * @property int $programid
     * @property int $sortorder
     * @property int $competencyid
     * @property int $nextsetoperator
     * @property int $completiontype
     * @property int $mincourses
     * @property int $coursesumfield
     * @property int $coursesumfieldtotal
     * @property int $timeallowed
     * @property int $recurrencetime
     * @property int $recurcreatetime
     * @property int $contenttype
     * @property string $label
     * @property int $certifpath
     *
     * @return program_courseset
     */
    public static function create(
        int $programid,
        int $sortorder,
        int $competencyid,
        int $nextsetoperator,
        int $completiontype,
        int $mincourses,
        int $coursesumfield,
        int $coursesumfieldtotal,
        int $timeallowed,
        int $recurrencetime,
        int $recurcreatetime,
        int $contenttype,
        string $label,
        int $certifpath
    ): program_courseset {
        global $DB;

        $entity = new entity();
        $entity->programid = $programid;

        // If the sort order wasn't provided or invalid, get the next.
        if (empty($sortorder) || $sortorder < 0) {
            $sortorder = self::get_next_sort_order($programid);
        }
        $entity->sortorder = $sortorder;

        // If it's set, do a quick check the competencyid is valid.
        if (!empty($competencyid)) {
            $DB->get_record('comp', ['id' => $competencyid], MUST_EXIST);
        }
        $entity->competencyid = $competencyid;

        // Make sure the course set is saved with a sensible label instead of the default
        if ($label == self::get_default_label()) {
            $label = get_string('legend:courseset', 'totara_program', $sortorder);
        }
        $entity->label = $label;

        $entity->nextsetoperator = $nextsetoperator;
        $entity->completiontype = $completiontype;
        $entity->mincourses = $mincourses;
        $entity->coursesumfield = $coursesumfield;
        $entity->coursesumfieldtotal = $coursesumfieldtotal;
        $entity->timeallowed = $timeallowed;
        $entity->recurrencetime = $recurrencetime;
        $entity->recurcreatetime = $recurcreatetime;
        $entity->contenttype = $contenttype;
        $entity->certifpath = $certifpath;

        $entity->save();

        return static::from_entity($entity);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function delete(): void {
        // Get all courses in the course set.
        $course_set_courses = $this->get_courses();
        // Remove all courses in the course set.
        foreach ($course_set_courses as $course_set_course) {
            $course_to_be_removed = program_courseset_course_entity::repository()
                ->where('coursesetid', $this->id)
                ->where('courseid', $course_set_course['courseid'])
                ->one();
            $course_to_be_removed->delete();
        }
        // Remove course set.
        $this->entity->delete();
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
     * @return array
     */
    public function get_courses(): array {
        return program_courseset_course_entity::repository()
            ->where('coursesetid', $this->entity->id)->get()->to_array();
    }

    /**
     * Get the next courseset.sortorder for a given program
     * Default = 1
     *
     * @param int $program_id - the id of the program to check
     * @return int
     */
    public static function get_next_sort_order($program_id): int {
        $max_sortorder = (int) entity::repository()
            ->select_raw('MAX(sortorder) AS max')
            ->where('programid', $program_id)
            ->one()
            ->max;

        return ++$max_sortorder;
    }

    /**
     * Given the database value (int) of the nextset operator,
     * return the string for the constant.
     *
     * @param int|null $operator
     * @return string
     */
    public static function get_nextset_operator_constant(?int $operator): string {
        // No need to get fancy, there are 3 options.
        switch ($operator) {
            case content::NEXTSETOPERATOR_OR:
                return 'NEXTSETOPERATOR_OR';
            case content::NEXTSETOPERATOR_AND:
                return 'NEXTSETOPERATOR_AND';
            default:
                return 'NEXTSETOPERATOR_THEN';
        }
    }

    /**
     * Update the nextset_operator for the courseset.
     *
     * @param string $nextset_operator - Expects matching enum/constant
     * @return void
     * @throws coding_exception
     */
    public function update_nextset_operator($nextset_operator): void {
        // First lets verify the operator handed through.
        $content_reflection = new \ReflectionClass(content::class);
        $operator = $content_reflection->getConstant($nextset_operator);

        // We should have a valid operator.
        if (!$operator) {
            throw new \coding_exception('invalid nextset operator: ' . $nextset_operator);
        }

        $this->entity->nextsetoperator = $operator;
        $this->save();
    }

    /**
     * Update the courses in a courseset.
     *
     * @param array $course_ids - The new list of courses for the courseset
     * @return void
     */
    public function update_courses(array $course_ids): void {
        // Get all course ids in the course set
        $collection = program_courseset_course_entity::repository()
            ->select('courseid')
            ->where('coursesetid', $this->id)
            ->get();
        $current_course_ids = $collection->pluck('courseid');

        // Remove courses from the courseset that no longer require
        if ($current_course_ids) {
            // Course ids that needs to be removed.
            $course_ids_to_be_removed = array_diff($current_course_ids, $course_ids);
            if ($course_ids_to_be_removed) {
                foreach ($course_ids_to_be_removed as $course_id) {
                    $course_to_be_removed = program_courseset_course_entity::repository()
                        ->where('coursesetid', $this->id)
                        ->where('courseid', $course_id)
                        ->one();
                    $course_to_be_removed->delete();
                }

                // Remove the program enrolment plugin for courses no longer in a program.
                program_courseset_course_model::remove_enrolment_plugin($course_ids_to_be_removed);
            }
        }

        // Add new courses
        $sort_order = 1;
        foreach ($course_ids as $course_id) {
            if (!\context_course::instance($course_id, IGNORE_MISSING)) {
                // If there is no matching context we have a bad object.
                throw new \coding_exception('Invalid course id.');
            }

            // Check the course has completion enabled.
            if (!program_courseset_course_model::check_course_completion($course_id)) {
                // Course without completion selected.
                throw new \coding_exception('Course requires completion setup');
            }

            if (in_array($course_id, $current_course_ids)) {
                // Load the courseset_course entity.
                $entity = program_courseset_course_entity::repository()
                ->where('coursesetid', $this->id)
                ->where('courseid', $course_id)
                ->one();

                // Check if we need to update the sort order.
                if ($sort_order != $entity->sortorder) {
                    // And update it.
                    $entity->sortorder = $sort_order;
                    $entity->save();
                }

                // Increment sort order.
                $sort_order++;
            } else {
                // Create a new courseset_course item.
                $model = program_courseset_course_model::create(
                    $this->id,
                    $course_id,
                    $sort_order++
                );

                // And make sure it has the program enrolment plugin.
                $model->add_enrolment_plugin();
            }
        }
    }

    /**
     * Get previous and next courseset's ids for the given course set.
     *
     * @param int $course_set_id
     * @return array|null[]
     */
    public function get_previous_and_next_courseset_ids(): array {
        $course_set_ids = entity::repository()
            ->where('programid', $this->entity->programid)
            ->order_by('sortorder')
            ->get()
            ->pluck('id');

        $current_index = array_search($this->entity->id, $course_set_ids);
        if (false === $current_index) {
            // current not found in rows, unable to determine
            // previous and next
            return [null, null];
        }

        return [
            $course_set_ids[$current_index - 1] ?? null,
            $course_set_ids[$current_index + 1] ?? null,
        ];
    }

    /**
     * The is_courseset_exist is used to check if the courseset exists.
     * It returns true if the courseset exists.
     *
     * @param int $courseset_id
     * @return bool
     */
    public static function is_courseset_exists($courseset_id): bool {
        $courseset = entity::repository()
            ->where('id', $courseset_id)
            ->one();
        if ($courseset) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @return \lang_string|string
     * @throws coding_exception
     */
    public static function get_default_label(): string {
        return get_string('untitledset', 'totara_program');
    }
}
