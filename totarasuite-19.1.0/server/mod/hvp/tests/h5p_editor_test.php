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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package mod_hvp
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../autoloader.php');

class mod_hvp_h5p_editor_test extends \core_phpunit\testcase {

    /**
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function test_merge_local_libs_missing_patch_version_in_folder(): void {
        $editor = \mod_hvp\framework::instance('editor');
        $this->assertInstanceOf(H5peditor::class, $editor);

        /** @var \mod_hvp\testing\generator $h5p_generator */
        $h5p_generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $test_library = $h5p_generator->create_library_record(
            'test',
            'testing',
            1,
            0,
            1,
            '',
            null,
            '',
            'http://example.com',
            true
        );
        $test_library->has_icon = 1;

        $data = [$test_library];
        $course = $this->getDataGenerator()->create_course();
        $course_context = context_course::instance($course->id);
        $context_id = $course_context->id;
        $_POST['contextId'] = $context_id;
        $cached_libraries = [];
        $passed = false;

        try {
            $editor->mergeLocalLibsIntoCachedLibs($data, $cached_libraries);
            $passed = true;
        } catch (\Exception $ex) {
            $this->fail($ex->getMessage());
        }
        $this->assertTrue($passed);
    }
}