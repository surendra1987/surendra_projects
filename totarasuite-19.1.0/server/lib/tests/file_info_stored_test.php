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
 * @package core_files
 */


use core_phpunit\testcase;

/**
 * Unit tests for file_info_stored
 *
 * @package  core_files
 */
class core_file_info_stored_test extends testcase {

    /** @var stdClass */
    protected $course1;
    /** @var stdClass */
    protected $course2;
    /** @var stdClass */
    protected $course1filerecord;
    /** @var stdClass */
    protected $teacher;
    /** @var stdClass */
    protected $teacherrole;

    /**
     * Set up
     */
    public function setUp(): void {
        global $DB;
        $this->setAdminUser();

        $this->getDataGenerator()->create_category(); // Empty category.
        $this->course1 = $this->getDataGenerator()->create_course(); // Empty course.
        $this->course2 = $this->getDataGenerator()->create_course();

        // Add a file to course1 summary.
        $coursecontext1 = \context_course::instance($this->course1->id);
        $this->course1filerecord = array(
            'contextid' => $coursecontext1->id,
            'component' => 'course',
            'filearea' => 'summary',
            'itemid' => '0',
            'filepath' => '/',
            'filename' => 'summaryfile.jpg'
        );
        $fs = get_file_storage();
        $fs->create_file_from_string($this->course1filerecord, 'IMG');

        $this->teacher = $this->getDataGenerator()->create_user();
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));

        // Make sure we're testing what should be the default capabilities.
        assign_capability('moodle/restore:viewautomatedfilearea', CAP_ALLOW, $this->teacherrole->id, $coursecontext1);

        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course1->id, $this->teacherrole->id);
        $this->getDataGenerator()->enrol_user($this->teacher->id, $this->course2->id, $this->teacherrole->id);

        $this->setUser($this->teacher);
    }

    /**
     * Tear Down
     */
    protected function tearDown(): void {
        $this->course1 = null;
        $this->course2 = null;
        $this->course1filerecord = null;
        $this->teacher = null;
        $this->teacherrole = null;
        parent::tearDown();
    }

    /**
     * Test "Server files" from the course context (course1)
     */
    public function test_file_info_context_course_1() {

        $browser = get_file_browser();
        $fileinfo = $browser->get_file_info(context_course::instance($this->course1->id));
        // Fileinfo element has only one non-empty child - "Course summary" file area.
        $this->assertNotEmpty($fileinfo->count_non_empty_children());
        $nonemptychildren = $fileinfo->get_non_empty_children();
        $this->assertEquals(1, count($nonemptychildren));
        $child = reset($nonemptychildren);
        $this->assertTrue($child instanceof file_info_stored);
        $this->assertEquals(['filename' => '.'] + $this->course1filerecord, $child->get_params());
        // Filearea "Course summary" has a child that is the actual image file.
        $this->assertEquals($this->course1filerecord, $child->get_non_empty_children()[0]->get_params());

        // There are seven course-level file areas available to teachers with default caps and no modules in this course.
        $allchildren = $fileinfo->get_children();
        $this->assertEquals(7, count($allchildren));
        $modulechildren = array_filter($allchildren, function ($a) {
            return $a instanceof file_info_context_module;
        });
        $this->assertEquals(0, count($modulechildren));

        // Admin can see seven course-level file areas.
        $this->setAdminUser();
        $fileinfo = $browser->get_file_info(\context_course::instance($this->course1->id));
        $this->assertEquals(7, count($fileinfo->get_children()));
    }

    /**
     * Test get_cached_context_file_counts()
     */

    public function test_get_cached_context_file_counts() {
        global $CFG;

        $browser = get_file_browser();
        $context = context_course::instance($this->course2->id);
        $urlbase = $CFG->wwwroot . '/pluginfile.php';
        $storedfile = $this->create_stored_file($context);
        $fileinfostored = new file_info_stored($browser, $context, $storedfile, $urlbase, get_string('areacourseintro', 'repository'), false, true, true, false);

        $method = (new ReflectionClass($fileinfostored))->getMethod('get_cached_context_file_counts');
        $method->setAccessible(true);

        // Call the function to be tested.
        $result = $method->invokeArgs($fileinfostored, [$context->id]);

        // Assert that the result is correct.
        $expected = ["user:draft:42:/" => [".png" => 1]];
        $this->assertEquals($expected, $result);
    }

    /**
     * Test make_context_file_counts_cache_file_path_key()
     */
    public function test_make_context_file_counts_cache_file_path_key() {
        global $CFG;

        $browser = get_file_browser();
        $context = context_course::instance($this->course2->id);
        $urlbase = $CFG->wwwroot . '/pluginfile.php';
        $storedfile = $this->create_stored_file($context);
        $fileinfostored = new file_info_stored($browser, $context, $storedfile, $urlbase, get_string('areacourseintro', 'repository'), false, true, true, false);

        $method = (new ReflectionClass($fileinfostored))->getMethod('make_context_file_counts_cache_filepath_key');
        $method->setAccessible(true);

        // Call the function to be tested.
        $key = $method->invokeArgs($fileinfostored, ['user', 'draft', 42, '/']);

        // Assert that the result is correct.
        $this->assertEquals('user:draft:42:/', $key);
    }

    /**
     * @param context_course $context
     * @return stored_file
     */
    private function create_stored_file(context_course $context): stored_file {
        global $CFG;
        require_once("{$CFG->dirroot}/lib/filelib.php");

        $fs = get_file_storage();

        $record = new stdClass();
        $record->contextid = $context->id;
        $record->itemid = 42;
        $record->filepath = '/';
        $record->filename = 'me.png';
        $record->component = 'user';
        $record->filearea = 'draft';
        $record->mimetype = 'image/png';

        $file_content = file_get_contents("{$CFG->dirroot}/lib/tests/fixtures/image_test.png");

        return $fs->create_file_from_string($record, $file_content);
    }
}
