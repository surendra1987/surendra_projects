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

use core_phpunit\testcase;
use core\testing\generator as data_generator;
use totara_program\content\course_set;
use totara_program\entity\program as program_entity;
use totara_program\entity\program_courseset as program_courseset_entity;
use totara_program\model\program as program_model;
use totara_program\content\course_sets\competency_course_set;
use totara_program\content\course_sets\multi_course_set;
use totara_program\content\course_sets\recurring_course_set;

class totara_program_program_model_test extends testcase {
    /** @var data_generator|null */
    private ?data_generator $generator;

    /** @var \totara_program\testing\generator*/
    private $program_generator;

    /** @var stdClass|null */
    private ?stdClass $course1;
    private ?stdClass $course2;
    private ?stdClass $course3;

    /** @var program */
    private $program1;

    /**
     * @return void
     * @throws coding_exception
     */
    protected function setUp(): void {
        $this->generator = $this->getDataGenerator();
        $this->program_generator = $this->generator->get_plugin_generator('totara_program');

        // Create courses.
        $this->course1 = $this->generator->create_course();
        $this->course2 = $this->generator->create_course();
        $this->course3 = $this->generator->create_course();

        // Create a program.
        $this->program1 = $this->program_generator->create_program();
    }

    /**
     * @return void
     */
    public function test_create(): void {
        // Create a category
        $category = $this->generator->create_category();

        try {
            // Create a program
            $program1 = program_model::create(
                $category->id,
                0,
                "program1",
                "Program 1",
                "",
                "",
                "",
                1,
                0,
                0,
                1,
                1675829019,
                1675829019,
                0,
                "",
                0,
                2,
                0,
                0,
                1
            );
        } catch (\exception $ex) {
            self::fail('Unexpected error');
        }

        // Get all programs
        $programs = program_entity::repository()->get()->to_array();
        $program_ids = array_column($programs, 'id');

        // Check program_courseset1 has created.
        self::assertContains($program1->id, $program_ids);
    }

    /**
     * @return void
     * @throws coding_exception
     */
    public function test_get_coursesets(): void {
        // Create a course set.
        $uniqueid = 'courseset1';
        $multicourseset1 = new multi_course_set($this->program1->id, null, $uniqueid);

        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $this->course1->id;
        $multicourseset1->add_course($coursedata);
        // Set completion type to "All courses"
        $multicourseset1->completiontype = course_set::COMPLETIONTYPE_ALL;
        $multicourseset1->save_set();

        // Get courseset
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);
        $program_coursesets = $program_model->get_coursesets();
        $program_courseset_ids = array_column($program_coursesets,'id');

        self::assertCount(1, $program_coursesets);
        self::assertContains($multicourseset1->id, $program_courseset_ids);
        self::assertEquals($program_coursesets[0]["completiontype"], "ALL");
    }

    /**
     * Test the function that checks whether a program contains legacy
     * recurring/competency content that requires the old view.
     *
     * @return void
     */
    public function test_contains_legacy_content(): void {

        // A program containing a competency.
        $c_prog = $this->program_generator->create_program();
        $competency_set = new competency_course_set($c_prog->id, null, 'competency_set1');
        $competency_set->save_set();

        self::assertTrue(program_model::contains_legacy_content($c_prog->id));

        // A recurring program.
        $r_prog = $this->program_generator->create_program();
        $recurring_set = new recurring_course_set($r_prog->id, null, 'recurring_set1');
        $recurring_set->save_set();

        self::assertTrue(program_model::contains_legacy_content($r_prog->id));

        // The good old boring multi-course program.
        $m_prog = $this->program_generator->create_program();
        $multicourse_set = new multi_course_set($m_prog->id, null, 'multicourse_set1');
        $multicourse_set->save_set();

        self::assertFalse(program_model::contains_legacy_content($m_prog->id));

        // Add a competency set to create a mixed program.
        $second_set = new competency_course_set($m_prog->id, null, 'competency_set1');
        $second_set->save_set();

        self::assertTrue(program_model::contains_legacy_content($m_prog->id));

        // Finally test an empty program with no content.
        self::assertFalse(program_model::contains_legacy_content($this->program1->id));
    }

    /**
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_reset_coursesets_sortorder(): void {
        // Create 4 course sets.
        for ($x = 1; $x <= 4; $x++) {
            $unique_id = 'courseset_'.$x;
            $multicourseset = new multi_course_set($this->program1->id, null, $unique_id);
            $coursedata = new stdClass();
            $coursedata->{$unique_id . 'courseid'} = $this->course1->id;
            $multicourseset->add_course($coursedata);
            $multicourseset->completiontype = course_set::COMPLETIONTYPE_ALL;
            // Add random sort order.
            $multicourseset->sortorder = rand(1, 10);
            $multicourseset->save_set();
        }

        // Get all course sets.
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);

        $expected_sort_order = [1, 2, 3, 4];
        $program_model->reset_coursesets_sortorder();
        $program_courseset_sortorders = \totara_program\entity\program_courseset::repository()
            ->where('programid', $this->program1->id)
            ->order_by('sortorder')
            ->get()
            ->pluck('sortorder');

        self::assertCount(4,$program_courseset_sortorders);
        self::assertEquals($expected_sort_order, $program_courseset_sortorders);
    }

    /**
     * Test the function that course set belongs to program.
     *
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_course_set_belongs_to_program(): void {
        // Create a course set.
        $uniqueid = 'courseset1';
        $multicourseset1 = new multi_course_set($this->program1->id, null, $uniqueid);

        // Add course to courseset.
        $coursedata = new stdClass();
        $coursedata->{$uniqueid . 'courseid'} = $this->course1->id;
        $multicourseset1->add_course($coursedata);
        // Set completion type to "All courses"
        $multicourseset1->completiontype = course_set::COMPLETIONTYPE_ALL;
        $multicourseset1->save_set();

        // Get courseset
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);
        $program_coursesets = $program_model->get_coursesets();
        $program_courseset_ids = array_column($program_coursesets,'id');

        self::assertCount(1, $program_coursesets);
        self::assertContains($multicourseset1->id, $program_courseset_ids);

        // Check course set belongs to the program.
        $course_set_belongs_to_program = $program_model->does_courseset_belongs_to_program($multicourseset1->id);
        self::assertTrue($course_set_belongs_to_program);
    }

    /**
     * Test the function with course set move up to next.
     *
     * @throws coding_exception
     */
    public function test_course_set_move_up_to_next(): void {
        // Create 4 course sets.
        for ($x = 1; $x <= 4; $x++) {
            $unique_id = 'courseset_'.$x;
            $multicourseset = new multi_course_set($this->program1->id, null, $unique_id);
            $coursedata = new stdClass();
            $coursedata->{$unique_id . 'courseid'} = $this->course1->id;
            $multicourseset->add_course($coursedata);
            $multicourseset->completiontype = course_set::COMPLETIONTYPE_ALL;
            // Add random sort order.
            $multicourseset->sortorder = rand(1, 10);
            $multicourseset->save_set();
        }

        // Get all course sets.
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        self::assertCount(4,$course_sets);

        // Reset sort order to 1,2,3,4.
        $sort_order = 1;
        foreach ($course_sets as $course_set) {
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['sortorder' => $sort_order]);
            $sort_order++;
        }

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $program_courseset_ids = array_column($course_sets,'id');

        // Move up the course set.
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);
        $program_model->move_courseset_up($program_courseset_ids[1], 1);

        // Get all course sets.
        $course_sets_updated = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Expected output. Because second course set is moved up.
        $expected_output = [
            [
                'id' => $program_courseset_ids[1],
                'sortorder' => 1
            ],
            [
                'id' => $program_courseset_ids[0],
                'sortorder' => 2
            ],
            [
                'id' => $program_courseset_ids[2],
                'sortorder' => 3
            ],
            [
                'id' => $program_courseset_ids[3],
                'sortorder' => 4
            ]
        ];

        $program_updated_courseset_ids = array_column($course_sets_updated,'id');

        $actual_output = [];
        foreach ($course_sets_updated as $course_set_updated) {
            $actual_output[] = ['id' => $course_set_updated['id'], 'sortorder' => $course_set_updated['sortorder']];
        }
        self::assertCount(4,$course_sets);
        self::assertEquals($program_courseset_ids[1], $program_updated_courseset_ids[0]);
        self::assertEquals($expected_output, $actual_output);
    }

    /**
     * Test the function that course set move up to top.
     *
     * @throws coding_exception
     */
    public function test_course_set_move_up_to_top(): void {
        // Create 4 course sets.
        for ($x = 1; $x <= 4; $x++) {
            $unique_id = 'courseset_'.$x;
            $multicourseset = new multi_course_set($this->program1->id, null, $unique_id);
            $coursedata = new stdClass();
            $coursedata->{$unique_id . 'courseid'} = $this->course1->id;
            $multicourseset->add_course($coursedata);
            $multicourseset->completiontype = course_set::COMPLETIONTYPE_ALL;
            // Add random sort order.
            $multicourseset->sortorder = rand(1, 10);
            $multicourseset->save_set();
        }

        // Get all course sets.
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        self::assertCount(4,$course_sets);

        // Reset sort order to 1,2,3,4.
        $sort_order = 1;
        foreach ($course_sets as $course_set) {
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['sortorder' => $sort_order]);
            $sort_order++;
        }

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $program_courseset_ids = array_column($course_sets,'id');

        // Move up the course set.
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);
        $program_model->move_courseset_up($program_courseset_ids[3], 1);

        // Get all course sets.
        $course_sets_updated = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Expected output. Because second course set is moved up.
        $expected_output = [
            [
                'id' => $program_courseset_ids[3],
                'sortorder' => 1
            ],
            [
                'id' => $program_courseset_ids[0],
                'sortorder' => 2
            ],
            [
                'id' => $program_courseset_ids[1],
                'sortorder' => 3
            ],
            [
                'id' => $program_courseset_ids[2],
                'sortorder' => 4
            ]
        ];

        $program_updated_courseset_ids = array_column($course_sets_updated,'id');

        $actual_output = [];
        foreach ($course_sets_updated as $course_set_updated) {
            $actual_output[] = ['id' => $course_set_updated['id'], 'sortorder' => $course_set_updated['sortorder']];
        }
        self::assertCount(4,$course_sets);
        self::assertEquals($program_courseset_ids[3], $program_updated_courseset_ids[0]);
        self::assertEquals($expected_output, $actual_output);
    }

    /**
     * Test the function that course set move down to bottom.
     *
     * @throws coding_exception
     */
    public function test_course_set_move_down_to_last(): void {
        // Create 4 course sets.
        for ($x = 1; $x <= 4; $x++) {
            $unique_id = 'courseset_'.$x;
            $multicourseset = new multi_course_set($this->program1->id, null, $unique_id);
            $coursedata = new stdClass();
            $coursedata->{$unique_id . 'courseid'} = $this->course1->id;
            $multicourseset->add_course($coursedata);
            $multicourseset->completiontype = course_set::COMPLETIONTYPE_ALL;
            // Add random sort order.
            $multicourseset->sortorder = rand(1, 10);
            $multicourseset->save_set();
        }

        // Get all course sets.
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        // Reset sort order to 1,2,3,4.
        $sort_order = 1;
        foreach ($course_sets as $course_set) {
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['sortorder' => $sort_order]);
            $sort_order++;
        }

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();
        $program_courseset_ids = array_column($course_sets,'id');

        // Move up the course set.
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);
        $program_model->move_courseset_down($program_courseset_ids[0], 4);

        // Get all course sets.
        $course_sets_updated = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('sortorder')
            ->get()
            ->to_array();

        // Expected output. Because second course set is moved up.
        $expected_output = [
            [
                'id' => $program_courseset_ids[1],
                'sortorder' => 1
            ],
            [
                'id' => $program_courseset_ids[2],
                'sortorder' => 2
            ],
            [
                'id' => $program_courseset_ids[3],
                'sortorder' => 3
            ],
            [
                'id' => $program_courseset_ids[0],
                'sortorder' => 4
            ]
        ];

        $program_updated_courseset_ids = array_column($course_sets_updated,'id');

        $actual_output = [];
        foreach ($course_sets_updated as $course_set_updated) {
            $actual_output[] = ['id' => $course_set_updated['id'], 'sortorder' => $course_set_updated['sortorder']];
        }
        self::assertCount(4,$course_sets);
        self::assertEquals($program_courseset_ids[0], $program_updated_courseset_ids[3]);
        self::assertEquals($expected_output, $actual_output);
    }

    /**
     * Checks whether a given sort order is valid within the current program.
     *
     * @return void
     * @throws coding_exception
     */
    public function test_is_valid_sort_order(): void {
        // Create 4 course sets.
        for ($x = 1; $x <= 4; $x++) {
            $unique_id = 'courseset_'.$x;
            $multicourseset = new multi_course_set($this->program1->id, null, $unique_id);
            $coursedata = new stdClass();
            $coursedata->{$unique_id . 'courseid'} = $this->course1->id;
            $multicourseset->add_course($coursedata);
            $multicourseset->completiontype = course_set::COMPLETIONTYPE_ALL;
            // Add random sort order.
            $multicourseset->sortorder = rand(1, 10);
            $multicourseset->save_set();
        }

        // Get all course sets.
        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        // Reset sort order to 1,2,3,4.
        $sort_order = 1;
        foreach ($course_sets as $course_set) {
            program_courseset_entity::repository()
                ->where('id', $course_set['id'])
                ->update(['sortorder' => $sort_order]);
            $sort_order++;
        }

        $course_sets = program_courseset_entity::repository()
            ->where('programid', $this->program1->id)
            ->order_by('id')
            ->get()
            ->to_array();

        // Move up the course set.
        $program_entity = new program_entity($this->program1->id);
        $program_model = program_model::from_entity($program_entity);

        // Check whether sort order 1 is valid.
        $is_valid_sort_order = $program_model->is_valid_sortorder(2);
        self::assertTrue($is_valid_sort_order);

        // Check whether sort order 4 is valid.
        $is_valid_sort_order = $program_model->is_valid_sortorder(3);
        self::assertTrue($is_valid_sort_order);

        // Sort order 5 is not valid.
        $is_valid_sort_order = $program_model->is_valid_sortorder(5);
        self::assertFalse($is_valid_sort_order);
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->generator = null;
        $this->program_generator = null;
        $this->course1 = null;
        $this->course2 = null;
        $this->course3 = null;
        $this->program1 = null;

        parent::tearDown();
    }
}
