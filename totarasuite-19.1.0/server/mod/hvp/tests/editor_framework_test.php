<?php
/**
 * This file is part of Totara Perform
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

use core_phpunit\testcase;
use mod_hvp\editor_framework;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../autoloader.php');

class mod_hvp_editor_framework_test extends testcase {
    protected ?editor_framework $editor_framework;

    public function test_getLanguage(): void {
        global $DB;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $lib1 = $generator->create_library_record('Library1', 'Lib1', 1, 0, 1, '', null, 'https://www.example.com');
        $languages_data = [
            'library_id' => $lib1->id,
            'language_code' => 'et',
            'language_json' => '{
              "semantics":[
                {
                  "entity":"paneel"
                }
              ]
            }'
        ];
        $DB->insert_record('hvp_libraries_languages', $languages_data);
        $found = $this->editor_framework->getLanguage('Library1', 1, 0, 'et');
        $this->assertNotFalse($found);
        $decoded_lang = json_decode($found);
        $this->assertEquals($decoded_lang->semantics[0]->entity, 'paneel');

        $found = $this->editor_framework->getLanguage('Library1', 1, 0, 'es');
        $this->assertFalse($found);

        $found = $this->editor_framework->getLanguage('Library1', 2, 0, 'et');
        $this->assertFalse($found);
    }

    public function test_keepFile(): void {
        global $DB;
        $test_file_id = 99;
        $temp_file_id = 98;
        $DB->insert_record_raw('hvp_tmpfiles', ['id' => $test_file_id], false, false, true);
        $DB->insert_record_raw('hvp_tmpfiles', ['id' => $temp_file_id], false, false, true);
        $this->editor_framework->keepFile($test_file_id);
        $record_exists = $DB->get_record('hvp_tmpfiles', ['id' => $test_file_id]);
        $this->assertFalse($record_exists);

        $record = $DB->get_record('hvp_tmpfiles', ['id' => $temp_file_id]);
        $this->assertEquals($temp_file_id, $record->id);
    }

    public function test_getLibraries(): void {
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        $system_context = \totara_core\extended_context::make_system();
        $_POST = array_merge($_POST, ['contextId' => $system_context->get_context_id()]);

        // test with no known libraries
        $libraries = $this->editor_framework->getLibraries();
        $this->assertEmpty($libraries);

        // test with known libraries - no filtering
        $lib1 = $generator->create_library_record('Library1', 'Lib1', 1, 0, 1, '', null, 'https://www.example.com/lib1');
        $lib2 = $generator->create_library_record('Library2', 'Lib2', 1, 2, 1, '', null, 'https://www.example.com/lib2');

        $libraries = $this->editor_framework->getLibraries();
        $this->assertCount(2, $libraries);

        // test unknown libraries
        $libraries = $this->editor_framework->getLibraries([
            (object) [
                'name' => 'LibraryUnknown',
                'majorVersion' => 1,
                'minorVersion' => 0,
            ]
        ]);
        $this->assertEmpty($libraries);

        // test known libraries - with filtering
        $libraries = $this->editor_framework->getLibraries([
            (object) [
                'name' => 'Library1',
                'majorVersion' => 1,
                'minorVersion' => 0
            ]
        ]);
        $this->assertCount(1, $libraries);
        $lib1 = reset($libraries);
        $this->assertEquals('https://www.example.com/lib1', $lib1->tutorialUrl);
        $this->assertEquals('Lib1', $lib1->title);
    }

    public function test_getLibraries_with_differing_versions(): void {
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        $system_context = \totara_core\extended_context::make_system();
        $_POST = array_merge($_POST, ['contextId' => $system_context->get_context_id()]);

        // test with known libraries - no filtering
        $lib1_old = $generator->create_library_record('Library1', 'Lib1', 1, 1, 1, '', null, 'https://www.example.com/lib1');
        $lib1 = $generator->create_library_record('Library1', 'Lib1', 1, 55, 1, '', null, 'https://www.example.com/lib1');
        $lib2_old = $generator->create_library_record('Library2', 'Lib2', 1, 55, 1, '', null, 'https://www.example.com/lib2');
        $lib2 = $generator->create_library_record('Library2', 'Lib2', 2, 1, 1, '', null, 'https://www.example.com/lib2');
        $lib3 = $generator->create_library_record('Library3', 'Lib3', 1, 1, 1, '', null, 'https://www.example.com/lib3');

        $libraries = $this->editor_framework->getLibraries();
        $this->assertCount(5, $libraries);
        $old_libraries = [];
        $new_libraries = [];
        foreach ($libraries as $library) {
            if (!property_exists($library, 'isOld')) {
                $new_libraries[$library->title] = $library;
                continue;
            }
            $old_libraries[$library->title] = $library;
        }

        $this->assertCount(3, $new_libraries);
        $this->assertCount(2, $old_libraries);

        // check the older versions are correct
        $old_lib1 = $old_libraries['Lib1'];
        $old_lib2 = $old_libraries['Lib2'];
        $this->assertSame(1, $old_lib1->majorVersion);
        $this->assertSame(1, $old_lib1->minorVersion);
        $this->assertSame(1, $old_lib2->majorVersion);
        $this->assertSame(55, $old_lib2->minorVersion);

        // check the newer versions are correct
        $new_lib1 = $new_libraries['Lib1'];
        $new_lib2 = $new_libraries['Lib2'];
        $this->assertSame(1, $new_lib1->majorVersion);
        $this->assertSame(55, $new_lib1->minorVersion);
        $this->assertSame(2, $new_lib2->majorVersion);
        $this->assertSame(1, $new_lib2->minorVersion);
    }

    public function test_getLibraries_with_restricted_libraries(): void {
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        $system_context = context_system::instance();
        $_POST = array_merge($_POST, ['contextId' => $system_context->id]);

        $lib1 = $generator->create_library_record('Library1', 'Lib1', 1, 0, 1, '', null, 'https://www.example.com/lib1', null, true, 1, 1);
        $lib2 = $generator->create_library_record('Library2', 'Lib2', 1, 2, 1, '', null, 'https://www.example.com/lib2');

        // test user without capability - mod/hvp:userestrictedlibraries
        $unauthorized_user = $this->getDataGenerator()->create_user();
        $this->setUser($unauthorized_user);
        $libraries = $this->editor_framework->getLibraries();
        $restricted_libraries = [];
        $unrestricted_libraries = [];
        foreach ($libraries as $library) {
            if ($library->restricted) {
                $restricted_libraries[$library->title] = $library;
                continue;
            }
            $unrestricted_libraries[$library->title] = $library;
        }
        $this->assertCount(1, $restricted_libraries);
        $this->assertCount(1, $unrestricted_libraries);
        $this->assertTrue($restricted_libraries['Lib1']->restricted);

        // test user with capability - mod/hvp:userestrictedlibraries
        $authorised_user = $this->getDataGenerator()->create_user();
        $authorised_role = $this->getDataGenerator()->create_role();
        assign_capability('mod/hvp:userestrictedlibraries', CAP_ALLOW, $authorised_role, $system_context->id, true);
        $this->setUser($authorised_user);
        $this->getDataGenerator()->role_assign($authorised_role, $authorised_user->id);
        $libraries = $this->editor_framework->getLibraries();
        foreach ($libraries as $library) {
            $this->assertFalse($library->restricted);
        }
    }

    public function test_saveFileTemporarily(): void {
        global $CFG;
        $folder = make_temp_directory('hvp_tests');
        $original_filepath = $folder . DIRECTORY_SEPARATOR . "test_file.txt";
        file_put_contents($original_filepath, "test 1");

        $file_data = $this->editor_framework::saveFileTemporarily("test 1");
        $original_file_remains = file_exists($original_filepath);
        $this->assertTrue($original_file_remains);

        $this->assertStringContainsString($CFG->tempdir . DIRECTORY_SEPARATOR, $file_data->dir);
        $this->assertStringEndsWith('.h5p', $file_data->fileName);

        /** @var \mod_hvp\framework $instance */
        $instance = \mod_hvp\framework::instance('interface');
        $path = $instance->getUploadedH5pPath();
        $hvp_content = file_get_contents($path);
        $this->assertEquals("test 1", $hvp_content);
    }

    public function test_markFileForCleanup(): void {
        global $DB;
        $test_file_id = 99;
        $file_exists = $DB->record_exists('hvp_tmpfiles', ['id' => $test_file_id]);
        $this->assertFalse($file_exists);

        // test with content id
        $this->editor_framework::markFileForCleanup($test_file_id, 1);
        $file_exists = $DB->record_exists('hvp_tmpfiles', ['id' => $test_file_id]);
        $this->assertFalse($file_exists);

        // test without content id
        $this->editor_framework::markFileForCleanup($test_file_id);
        $file_exists = $DB->record_exists('hvp_tmpfiles', ['id' => $test_file_id]);
        $this->assertTrue($file_exists);
    }

    public function test_removeTemporarilySavedFiles(): void {
        $folder = make_temp_directory('hvp_tests');
        $original_filepath = $folder . DIRECTORY_SEPARATOR . "test_file.txt";
        $second_filepath = $folder . DIRECTORY_SEPARATOR . "test_2_file.txt";
        file_put_contents($original_filepath, "test 1");
        file_put_contents($second_filepath, "test 2");
        $this->editor_framework::removeTemporarilySavedFiles($original_filepath);
        $test_file_exists = file_exists($original_filepath);
        $second_file_exists = file_exists($second_filepath);
        $this->assertFalse($test_file_exists);
        $this->assertTrue($second_file_exists);

        $this->editor_framework::removeTemporarilySavedFiles($folder . DIRECTORY_SEPARATOR);
        $second_file_exists = file_exists($second_filepath);
        $this->assertFalse($second_file_exists);
        $folder_exists = check_dir_exists($folder);
        $this->assertTrue($folder_exists);
    }

    public function test_getAvailableLanguages(): void {
        global $DB;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $lib1 = $generator->create_library_record('Library1', 'Lib1', 1, 0, 1, '', null, 'https://www.example.com/lib1');
        $lib1_new = $generator->create_library_record('Library1', 'Lib1', 1, 55, 1, '', null, 'https://www.example.com/lib2');
        $lib2 = $generator->create_library_record('Library2', 'Lib2', 1, 0, 1, '', null, 'https://www.example.com/lib2');
        $languages_data = [
            'library_id' => $lib1->id,
            'language_code' => 'et',
            'language_json' => '{
              "semantics":[
                {
                  "entity":"paneel"
                }
              ]
            }'
        ];
        // test library with other known languages
        $DB->insert_record('hvp_libraries_languages', $languages_data);
        $found = $this->editor_framework->getAvailableLanguages('Library1', 1, 0);
        $this->assertEqualsCanonicalizing(['en', 'et'], $found);

        // test library without any other known languages
        $found = $this->editor_framework->getAvailableLanguages('Library2', 1, 0);
        $this->assertEqualsCanonicalizing(['en'], $found);

        // test with library versions
        $found = $this->editor_framework->getAvailableLanguages('Library1', 55, 0);
        $this->assertEqualsCanonicalizing(['en'], $found);

        // test with unknown library
        $found = $this->editor_framework->getAvailableLanguages('UnknownLibrary', 55, 0);
        $this->assertEqualsCanonicalizing(['en'], $found);
    }

    public function setUp(): void {
        $this->editor_framework = new editor_framework();
    }

    protected function tearDown(): void {
        $this->editor_framework = null;
        parent::tearDown();
    }
}