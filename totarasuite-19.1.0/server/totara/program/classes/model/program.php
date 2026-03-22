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
 * @author  Gihan Hewaralalage <gihan.hewaralalage@totaralearning.com>
 * @package totara_program
 */

namespace totara_program\model;

use coding_exception;
use core\entity\course;
use core\orm\entity\model;
use dml_exception;
use totara_program\content\course_set;
use totara_program\content\course_set as content;
use totara_program\content\program_content;
use totara_program\entity\program as entity;
use totara_program\entity\program_courseset as program_courseset_entity;
use totara_program\entity\program_courseset_course as program_courseset_course_entity;
use totara_program\event\program_contentupdated;
use totara_program\model\program_courseset as program_courseset_model;

class program extends model {
    /**
     * @var entity
     */
    protected $entity;

    /**
     * @param entity $entity
     * @throws coding_exception
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
     * @return program
     * @throws coding_exception
     */
    public static function from_entity(entity $entity): program {
        if (!$entity->exists()) {
            throw new coding_exception("Cannot instantiate a courseset from a non-existing entity");
        }

        return new program($entity);
    }

    /**
     * Properties:
     * @param int $category
     * @param int $sortorder
     * @param string $fullname
     * @param string $shortname
     * @param string $idnumber
     * @param string $summary
     * @param string $endnote
     * @param int $visible
     * @param int $availablefrom
     * @param int $availableuntil
     * @param int $available
     * @param int $timecreated
     * @param int $timemodified
     * @param int $usermodified
     * @param string $icon
     * @param int $exceptionssent
     * @param int $audiencevisible
     * @param int $certifid
     * @param int $assignmentsdeferred
     * @param int $allowextensionrequests
     * @return program
     * @throws coding_exception
     * @property int $category
     * @property int $sortorder
     * @property string $fullname
     * @property string $shortname
     * @property string $idnumber
     * @property string $summary
     * @property string $endnote
     * @property int $visible
     * @property int $availablefrom
     * @property int $availableuntil
     * @property int $available
     * @property int $timecreated
     * @property int $timemodified
     * @property int $usermodified
     * @property string $icon
     * @property int $exceptionssent
     * @property int $audiencevisible
     * @property int $certifid
     * @property int $assignmentsdeferred
     * @property int $allowextensionrequests
     */
    public static function create(
        int $category,
        int $sortorder,
        string $fullname,
        string $shortname,
        string $idnumber,
        string $summary,
        string $endnote,
        int $visible,
        int $availablefrom,
        int $availableuntil,
        int $available,
        int $timecreated,
        int $timemodified,
        int $usermodified,
        string $icon,
        int $exceptionssent,
        int $audiencevisible,
        int $certifid,
        int $assignmentsdeferred,
        int $allowextensionrequests
    ): program {
        $entity = new entity();

        $entity->category = $category;
        $entity->sortorder = $sortorder;
        $entity->fullname = $fullname;
        $entity->shortname = $shortname;
        $entity->idnumber = $idnumber;
        $entity->summary = $summary;
        $entity->endnote = $endnote;
        $entity->visible = $visible;
        $entity->availablefrom = $availablefrom;
        $entity->availableuntil = $availableuntil;
        $entity->available = $available;
        $entity->timecreated = $timecreated;
        $entity->timemodified = $timemodified;
        $entity->usermodified = $usermodified;
        $entity->icon = $icon;
        $entity->exceptionssent = $exceptionssent;
        $entity->audiencevisible = $audiencevisible;
        $entity->certifid = $certifid;
        $entity->assignmentsdeferred = $assignmentsdeferred;
        $entity->allowextensionrequests = $allowextensionrequests;

        $entity->save();

        return static::from_entity($entity);
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
     * Get all the coursesets associated with the current program.
     *
     * @return array
     * @throws dml_exception
     */
    public function get_coursesets(): array {
        $course_sets = [];
        // Get all course sets for current program
        $all_course_sets = program_courseset_entity::repository()
            ->where('programid', $this->entity->id)->get()->to_array();

        // Get courses for each course set
        foreach ($all_course_sets as $course_set) {
            $courses = program_courseset_course_entity::repository()
                ->select(['courseid', 'sortorder'])
                ->where('coursesetid', $course_set['id'])
                ->get()
                ->to_array();
            foreach ($courses as $cinfo) {
                /** @var course $course */
                $course = course::repository()->where('id', $cinfo['courseid'])->one();
                if ($course) {
                    $course_set['courses'][] = (object) [
                        'id' => $course->id,
                        'category' => $course->category,
                        'sortorder' => $cinfo['sortorder'], // It's order within the courseset.
                        'fullname' => $course->fullname,
                        'shortname' => $course->shortname,
                        'idnumber' => $course->idnumber,
                        'summary' => $course->summary,
                        'image' => course_get_image($course)->out()
                    ];
                }
                switch ($course_set['completiontype']) {
                    case course_set::COMPLETIONTYPE_ALL:
                        $course_set['completiontype'] = 'ALL';
                        break;
                    case course_set::COMPLETIONTYPE_ANY:
                        $course_set['completiontype'] = 'ANY';
                        break;
                    case course_set::COMPLETIONTYPE_SOME:
                        $course_set['completiontype'] = 'SOME';
                        break;
                    case course_set::COMPLETIONTYPE_OPTIONAL:
                        $course_set['completiontype'] = 'OPTIONAL';
                        break;
                }
            }
            $course_sets[] = $course_set;
        }

        return $course_sets;
    }

    /**
     * Makes sure that an array of course sets is in order in terms of each
     * set's sortorder property and resets the sortorder properties to ensure
     * that it begins from 1 and there are no gaps in the order.
     *
     * Also adds properties to enable the first and last set in the array to be
     * easily detected.
     *
     * @return void
     * @throws coding_exception
     */
    public function reset_coursesets_sortorder() {
        $course_set_ids = program_courseset_entity::repository()
            ->select('id')
            ->where('programid', $this->entity->id)
            ->order_by('sortorder')
            ->get()
            ->to_array();
        if (count($course_set_ids) === 0) {
            // Its still null OR there are no course sets.
            return;
        }

        $count = 1;
        foreach ($course_set_ids as $course_set_id) {
            $course_set = new program_courseset_entity($course_set_id);
            $course_set->sortorder = $count;
            $course_set->save();
            $count++;
        }
    }

    /**
     * Check whether a given course set belongs to the current program.
     * @param int $course_set_id
     * @return bool
     */
    public function does_courseset_belongs_to_program(int $course_set_id): bool {
        $course_set = program_courseset_entity::repository()
            ->where('id', $course_set_id)
            ->where('programid', $this->entity->id)
            ->one();
        return (bool)$course_set;
    }

    /**
     * Check whether a given sort order is valid for a given course set in the program.
     *
     * @param int $sortorder
     * @return bool
     */
    public function is_valid_sortorder(int $sortorder): bool {
        $all_sortorders = program_courseset_entity::repository()
            ->where('programid', $this->entity->id)
            ->get()
            ->map(function (program_courseset_entity $courseset) {
                return $courseset->sortorder;
            })
            ->all();

        if (!in_array($sortorder, $all_sortorders)) {
            return false;
        }

        return true;
    }

    /**
     * Move course set up with given sort order.
     *
     * @param int $course_set_id
     * @param int $sortorder
     * @return bool
     * @throws coding_exception
     */
    public function move_courseset_up(int $course_set_id, int $sortorder): bool {
        // Get all course sets except the one being is moved
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->entity->id)
            ->where('id', '<>', $course_set_id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Get previous course set id and next course set id.
        $current_course_set_entity = new program_courseset_entity($course_set_id);
        $courseset_model = program_courseset_model::from_entity($current_course_set_entity);
        list($previous_courseset_id, $next_courseset_id) = $courseset_model->get_previous_and_next_courseset_ids();

        // Reset sort order in the relevant course sets.
        foreach ($course_sets as $course_set) {
            if ($course_set['sortorder'] < $current_course_set_entity->sortorder && $course_set['sortorder'] >= $sortorder) {
                $course_set_entity = new program_courseset_entity($course_set['id']);
                $course_set_entity->sortorder =  $course_set_entity->sortorder + 1;
                $course_set_entity->save();
            }
        }

        // Reset next_operator when course set move up.
        if ($next_courseset_id === null && $previous_courseset_id) {
            $previous_course_set_entity = new program_courseset_entity($previous_courseset_id);
            $current_course_set_entity->nextsetoperator = content::NEXTSETOPERATOR_THEN;
            $previous_course_set_entity->nextsetoperator = 0;
            $previous_course_set_entity->save();
        } else if ($next_courseset_id && $previous_courseset_id) {
            $current_course_set_entity->nextsetoperator = content::NEXTSETOPERATOR_THEN;
        }

        // Set new sort order to current course set.
        $current_course_set_entity->sortorder = $sortorder;
        $current_course_set_entity->save();

        // Make sure that an array of course sets is in order in terms of each
        // set's sort order property and reset the sort order properties to ensure
        // that it begins from 1 and there are no gaps in the order.
        $this->reset_coursesets_sortorder();

        return true;
    }


    /**
     * Move course set down with given sort order.
     *
     * @param int $course_set_id
     * @param int $sortorder
     * @return bool
     * @throws coding_exception
     */
    public function move_courseset_down(int $course_set_id, int $sortorder): bool {
        // Get all course sets except the one being is moved
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->entity->id)
            ->where('id', '<>', $course_set_id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Get previous course set id and next course set id.
        $current_course_set_entity = new program_courseset_entity($course_set_id);
        $courseset_model = program_courseset_model::from_entity($current_course_set_entity);
        list($previous_courseset_id, $next_courseset_id) = $courseset_model->get_previous_and_next_courseset_ids();

        // Reset sort order in the relevant course sets.
        foreach ($course_sets as $course_set) {
            if ($course_set['sortorder'] > $current_course_set_entity->sortorder && $course_set['sortorder'] <= $sortorder) {
                $courseset = new program_courseset_entity($course_set['id']);
                $courseset->sortorder =  $courseset->sortorder - 1;
                $courseset->save();
            }
        }

        /// Reset next_operator when course set move down.
        $current_course_set_entity->nextsetoperator = content::NEXTSETOPERATOR_THEN;

        // If the next_course_set is the last_set, set the current_course_set
        // to 0 and the next_course_set to THEN.
        if (end($course_sets)['id'] === $next_courseset_id) {
            $current_course_set_entity->nextsetoperator = 0;
            $next_course_set_entity = new program_courseset_entity($next_courseset_id);
            $next_course_set_entity->nextsetoperator = content::NEXTSETOPERATOR_THEN;
            $next_course_set_entity->save();
        }

        // Set new sort order to current course set.
        $current_course_set_entity->sortorder = $sortorder;
        $current_course_set_entity->save();

        // Make sure that an array of course sets is in order in terms of each
        // set's sort order property and reset the sort order properties to ensure
        // that it begins from 1 and there are no gaps in the order.
        $this->reset_coursesets_sortorder();

        return true;
    }

    /**
     * Trigger the program content updated event.
     *
     * @throws dml_exception
     */
    public function trigger_content_updated_event() {
        $course_sets = $this->get_coursesets();

        $course_set_ids = array_map(function ($course_set) {
            return $course_set['id'];
        }, $course_sets);

        $dataevent = array('id' => $this->entity->id, 'other' => array('coursesets' => $course_set_ids));
        program_contentupdated::create_from_data($dataevent)->trigger();
    }

    /**
     * Check whether a given program contains a competency or recurring courseset.
     *
     * @param int $program_id
     * @return bool
     */
    public static function contains_legacy_content(int $program_id): bool {
        $legacy_content = program_courseset_entity::repository()
            ->where('programid', $program_id)
            ->where('contenttype', '<>', program_content::CONTENTTYPE_MULTICOURSE)
            ->exists();

        return $legacy_content;
    }
}
