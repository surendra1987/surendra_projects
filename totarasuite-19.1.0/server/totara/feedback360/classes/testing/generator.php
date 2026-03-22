<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace totara_feedback360\testing;

use coding_exception;
use core\testing\component_generator;
use question_manager;
use stdClass, moodle_exception, feedback360, feedback360_question;
use totara_assign_feedback360;

/**
 * feedback360 generator.
 *
 * @package totara_appaisal
 * @category test
 */
final class generator extends component_generator {

    const DEFAULT_NAME = 'Test feedback360';

    /**
     * @var integer Keep a count of how many feedback360s have been created.
     */
    private $feedback360count = 0;

    /**
     * @var integer Keep a count of how many questions have been created.
     */
    private $questioncount = 0;

    /**
     * To be called from data reset code only, do not use in tests.
     * @return void
     */
    public function reset() {
        parent::reset();
        $this->feedback360count = 0;
        $this->questioncount = 0;
    }

    /**
     * @param array $data
     * @return feedback360
     */
    public function create_feedback360(array $data = []): feedback360 {
        global $CFG, $USER;
        require_once($CFG->dirroot . '/totara/feedback360/lib.php');

        // Increment the count of feedback360s.
        $i = ++$this->feedback360count;

        // Create some default values for the feedback360.
        $defaults = [
            'userid' => $USER->id,
            'name' => self::DEFAULT_NAME . ' ' . $i,
            'description' => '<p>' . self::DEFAULT_NAME . ' ' . $i . ' description</p>',
            'status' => 0
        ];

        // Merge the defaults and the given data and cast into an object.
        $feedback360_data = (object)array_merge($defaults, (array)$data);

        // Create a new feedback360.
        $feedback360 = new feedback360();
        $feedback360->set($feedback360_data);
        $feedback360->save();

        return $feedback360;
    }

    /**
     * Create a feedback360 question for Behat.
     *
     * @param array $data Optional data.
     * @return bool
     */
    public function create_question_for_behat(array $data = []): bool {
        global $DB;

        // Increment the count of questions.
        $i = ++$this->questioncount;

        // We need to know the feedback360 name so we can look it up and get the id for the stage.
        if (isset($data['feedback360'])) {
            $data['feedback360id'] = $DB->get_field('feedback360', 'id', ['name' => $data['feedback360']], MUST_EXIST);
            unset($data['feedback360']);
        } else {
            return false;
        }

        $data['type'] = 'text';

        // Merge the defaults and the given data and cast into an object.
        // Create a new page.
        question_manager::reset();
        $qmanager = new question_manager();
        $question = new feedback360_question();
        $element = $qmanager->create_element($question, $data['type']);
        $question->attach_element($element);
        $question->set((object) $data);
        $question->save();
        $question->get_element()->save();

        return true;
    }

    /**
     * Assign a group of users to a feedback360 for Behat.
     *
     * @param array $data Optional data.
     * @return void
     */
    public function create_assignment_for_behat(array $data = []): void {
        global $DB;

        // Get the feedback360 object.
        if (isset($data['feedback360'])) {
            $feedback360id = $DB->get_field('feedback360', 'id', ['name' => $data['feedback360']], MUST_EXIST);
            $feedback360 = new feedback360($feedback360id);
        } else {
            throw new coding_exception('data missing required "feedback360"');
        }

        // validate the type of group we want to assign to the feedback360 and get the record id.
        if (isset($data['type'])) {
            $type = $data['type'];
            unset($data['type']);

            switch ($type) {
                case 'cohort':
                case 'audience':
                    $type = 'cohort';
                    break;
                case 'org':
                case 'organisation':
                    $type = 'org';
                    break;
                case 'pos':
                case 'position':
                    $type = 'pos';
                    break;
                default:
                    throw new coding_exception("unknown assignment type {$type}");
            }

            // Get the record id of the group we want to assign to the feedback360.
            $typeid = $DB->get_field($type, 'id', ['idnumber' => $data['id']], MUST_EXIST);
        } else {
            throw new coding_exception('data missing required "type"');
        }

        $this->create_group_assignment($feedback360, $type, $typeid);
    }

    /**
     * Assign group of users to a feedback360
     *
     * @param feedback360 $feedback360 The feedback360 ID where we want to assign the group of users
     * @param string $group_type The Group type. e.g: cohort, pos, org.
     * @param int $instance_id The instance ID. ID of the cohort, organisation or position.
     */
    public function create_group_assignment(feedback360 $feedback360, string $group_type, int $instance_id): void {
        global $CFG;
        require_once($CFG->dirroot . '/totara/feedback360/lib.php');

        $group_types = totara_assign_feedback360::get_assignable_grouptypes();
        if (!in_array($group_type, $group_types)) {
            $a = new stdClass();
            $a->grouptype = $group_type;
            $a->module = 'feedback360';
            print_error('error:assignmentgroupnotallowed', 'totara_core', null, $a);
        }

        // Assigning group.
        $url_params = ['includechildren' => false, 'listofvalues' => [$instance_id]];
        $assign = new totara_assign_feedback360('feedback360', $feedback360);
        $group_type_obj = $assign->load_grouptype($group_type);
        $group_type_obj->handle_item_selector($url_params);
    }
}
