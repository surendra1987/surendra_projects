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
use mod_hvp\file_storage;

require_once(__DIR__ . '/../autoloader.php');

class mod_hvp_file_storage_test extends testcase {
    protected ?file_storage $file_storage;

    public function test_saveLibrary() {
        $tempPath = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        [$lib, $files] = $generator->create_library($tempPath, 1, 'Library1', 1, 0);

        $libPath = H5PCore::libraryToFolderName($lib);
        // create an existing file to be removed

        // create an existing fileareea file
        $tmp_path = $this->file_storage->getTmpPath();
        $tmp_filename = 'removeme.json';

        // setup the file for test
        $context = context_system::instance();
        $fs = get_file_storage();
        $pre_exist_file = (object) [
            'contextid' => $context->id,
            'component' => 'mod_hvp',
            'filearea' => 'libraries',
            'itemid' => 0,
            'filepath' => DIRECTORY_SEPARATOR . $libPath . DIRECTORY_SEPARATOR,
            'filename' => $tmp_filename,
        ];
        // create the file
        /** @var stored_file $stored_file */
        $stored_file = $fs->create_file_from_string($pre_exist_file, 'testing');
        $this->assertEquals('testing', $stored_file->get_content());

        $this->file_storage->saveLibrary($lib);

        // check the pre existing files were removed and replaced with the new onces
        $file_exists = $fs->file_exists($pre_exist_file->contextid, $pre_exist_file->component, $pre_exist_file->filearea, $pre_exist_file->itemid, $pre_exist_file->filepath,
            $pre_exist_file->filename);
        $this->assertFalse($file_exists);
        // ensure the library files that should exist were copied across
        $library_file_created =
            $fs->file_exists($pre_exist_file->contextid, $pre_exist_file->component, $pre_exist_file->filearea, $pre_exist_file->itemid, $pre_exist_file->filepath, 'library.json');
        $this->assertTrue($library_file_created);
    }

    public function test_exportLibrary() {
        $tmp_path = $this->file_storage->getTmpPath();;
        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        [$lib, $files] = $generator->create_library($tmp_path, 1, $machinename, $majorversion,
            $minorversion);

        $this->file_storage->saveLibrary($lib);

        $destinationDirectory = $tmp_path . DIRECTORY_SEPARATOR . 'testdir';
        check_dir_exists($destinationDirectory);
        $this->file_storage->exportLibrary($lib, $destinationDirectory);

        $filepath = DIRECTORY_SEPARATOR . "{$machinename}-{$majorversion}.{$minorversion}" . DIRECTORY_SEPARATOR;
        $this->assertFileExists($destinationDirectory . $filepath . 'library.json');
    }

    public function test_saveExport(): void {
        global $COURSE;
        $tmp_path = $this->file_storage->getTmpPath();;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $filename = 'someexportedfile.h5p';
        $source = $tmp_path . '/' . 'testfile.h5p';
        check_dir_exists($tmp_path);
        $generator->create_file($source, 'testing');

        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;

        $this->file_storage->saveExport($source, $filename);

        $fs = get_file_storage();
        $context = context_course::instance($course->id);

        // Check out if the file is there.
        $file = $fs->get_file($context->id, 'mod_hvp',
            'exports', '0', '/', $filename);

        $this->assertEquals('exports', $file->get_filearea());
    }

    public function test_getExportFile(): void {
        global $COURSE;
        $tmp_path = $this->file_storage->getTmpPath();;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $filename = 'someexportedfile.h5p';
        $source = $tmp_path . '/' . 'testfile.h5p';
        check_dir_exists($tmp_path);
        $generator->create_file($source, 'testing');

        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;

        $this->file_storage->saveExport($source, $filename);

        $getExportFile = $this->getMethod('getExportFile');
        $file = $getExportFile->invokeArgs($this->file_storage, [$filename]);

        $this->assertEquals('exports', $file->get_filearea());
    }

    protected static function getMethod($name) {
        $class = new ReflectionClass(file_storage::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    public function test_deleteExport(): void {
        global $COURSE;
        $tmp_path = $this->file_storage->getTmpPath();;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $filename = 'someexportedfile.h5p';
        $source = $tmp_path . '/' . 'testfile.h5p';
        check_dir_exists($tmp_path);
        $generator->create_file($source, 'testing');

        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;

        $this->file_storage->saveExport($source, $filename);
        $this->file_storage->deleteExport($filename);
        $getExportFile = $this->getMethod('getExportFile');
        $file = $getExportFile->invokeArgs($this->file_storage, [$filename]);
        $this->assertFalse($file);
    }

    public function test_hasExport(): void {
        global $COURSE;
        $tmp_path = $this->file_storage->getTmpPath();;
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $filename = 'someexportedfile.h5p';
        $source = $tmp_path . '/' . 'testfile.h5p';
        check_dir_exists($tmp_path);
        $generator->create_file($source, 'testing');

        $course = $this->getDataGenerator()->create_course();
        $COURSE = $course;

        $this->file_storage->saveExport($source, $filename);
        $has_export = $this->file_storage->hasExport($filename);
        $this->assertTrue($has_export);
        $this->file_storage->deleteExport($filename);
        $has_export = $this->file_storage->hasExport($filename);
        $this->assertFalse($has_export);
    }

    public function test_cacheAssets(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $files = ['scripts' => [], 'styles' => []];

        $basedirectory = $tmp_path . '/' . 'TestLib-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        // Now run the API call.
        $this->file_storage->saveLibrary($lib);

        // Second library.
        $basedirectory = $tmp_path . '/' . 'SuperTest-2.4';

        $library_id++;
        $machinename = 'SuperTest';
        $majorversion = 2;
        $minorversion = 4;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        $this->file_storage->saveLibrary($lib);

        $this->assertCount(2, $files['scripts']);
        $this->assertCount(2, $files['styles']);

        $key = 'testhashkey';

        $this->file_storage->cacheAssets($files, $key);
        $this->assertCount(1, $files['scripts']);
        $this->assertCount(1, $files['styles']);

        $expectedfile = '/' . 'cachedassets' . '/' . $key . '.js';
        $this->assertEquals($expectedfile, $files['scripts'][0]->path);
        $expectedfile = '/' . 'cachedassets' . '/' . $key . '.css';
        $this->assertEquals($expectedfile, $files['styles'][0]->path);
    }

    public function test_getCachedAssets(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $files = ['scripts' => [], 'styles' => []];

        $basedirectory = $tmp_path . '/' . 'test-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        // Now run the API call.
        $this->file_storage->saveLibrary($lib);

        // Second library.
        $basedirectory = $tmp_path . '/' . 'supertest-2.4';

        $library_id++;
        $machinename = 'SuperTest';
        $majorversion = 2;
        $minorversion = 4;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        $this->file_storage->saveLibrary($lib);

        $this->assertCount(2, $files['scripts']);
        $this->assertCount(2, $files['styles']);

        $key = 'testhashkey';

        $this->file_storage->cacheAssets($files, $key);

        $testarray = $this->file_storage->getCachedAssets($key);
        $this->assertCount(1, $testarray['scripts']);
        $this->assertCount(1, $testarray['styles']);
        $expectedfile = '/' . 'cachedassets' . '/' . $key . '.js';
        $this->assertEquals($expectedfile, $testarray['scripts'][0]->path);
        $expectedfile = '/' . 'cachedassets' . '/' . $key . '.css';
        $this->assertEquals($expectedfile, $testarray['styles'][0]->path);
    }

    public function test_deleteCachedAssets(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $files = ['scripts' => [], 'styles' => []];
        $basedirectory = $tmp_path . '/' . 'test-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        // Now run the API call.
        $this->file_storage->saveLibrary($lib);

        // Second library.
        $basedirectory = $tmp_path . '/' . 'supertest-2.4';

        $library_id++;
        $machinename = 'SuperTest';
        $majorversion = 2;
        $minorversion = 4;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        $this->file_storage->saveLibrary($lib);

        $this->assertCount(2, $files['scripts']);
        $this->assertCount(2, $files['styles']);

        $key = 'testhashkey';

        $this->file_storage->cacheAssets($files, $key);

        $testarray = $this->file_storage->getCachedAssets($key);
        $this->assertCount(1, $testarray['scripts']);
        $this->assertCount(1, $testarray['styles']);

        // Time to delete.
        $this->file_storage->deleteCachedAssets([$key]);
        $testarray = $this->file_storage->getCachedAssets($key);
        $this->assertNull($testarray);
    }

    public function test_getContent(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $files = ['scripts' => [], 'styles' => []];
        $basedirectory = $tmp_path . '/' . 'test-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        array_push($files['scripts'], ...$libfiles['scripts']);
        array_push($files['styles'], ...$libfiles['styles']);

        // Now run the API call.
        $this->file_storage->saveLibrary($lib);

        $content = $this->file_storage->getContent($files['scripts'][0]->path);
        // The file content is created based on the file system path (\core_h5p_generator::create_file).
        $expectedcontent = hash("md5", $basedirectory . '/' . 'scripts' . '/' . 'testlib.min.js');

        $this->assertEquals($expectedcontent, $content);
    }

    public function test_getContentFile(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $files = ['scripts' => [], 'styles' => []];
        $basedirectory = $tmp_path . '/' . 'test-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);

        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);
        $cm_context = context_module::instance($cm->id);

        $fs = get_file_storage();
        $filerecord = (object) [
            'contextid' => $cm_context->id,
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => $hvp->id,
            'filepath' => $tmp_path . DIRECTORY_SEPARATOR,
            'filename' => 'content.txt',
        ];
        /** @var stored_file $stored_file */
        $stored_file = $fs->create_file_from_string($filerecord, 'content_file_contents');
        $contentFileId = $this->file_storage->getContentFile($stored_file->get_filepath() . $stored_file->get_filename(), $hvp->id);
        $this->assertNotFalse($contentFileId);

        $fs = get_file_storage();
        $content_file = $fs->get_file_by_id($contentFileId);
        $content_file_name = $content_file->get_filename();
        $this->assertEquals('content.txt', $content_file_name);
        $this->assertEquals('content_file_contents', $content_file->get_content());
    }

    public function test_removeContentFile(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $files = ['scripts' => [], 'styles' => []];
        $basedirectory = $tmp_path . '/' . 'test-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);

        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);
        $cm_context = context_module::instance($cm->id);

        $fs = get_file_storage();
        $filerecord = (object) [
            'contextid' => $cm_context->id,
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => $hvp->id,
            'filepath' => $tmp_path . DIRECTORY_SEPARATOR,
            'filename' => 'content.txt',
        ];
        /** @var stored_file $stored_file */
        $stored_file = $fs->create_file_from_string($filerecord, 'content_file_contents');
        $contentFileId = $this->file_storage->getContentFile($stored_file->get_filepath() . $stored_file->get_filename(), $hvp->id);
        $this->assertNotFalse($contentFileId);

        $fs = get_file_storage();
        $content_file = $fs->get_file_by_id($contentFileId);
        $content_file_name = $content_file->get_filename();
        $this->assertEquals('content.txt', $content_file_name);
        $this->assertEquals('content_file_contents', $content_file->get_content());

        $this->file_storage->removeContentFile($stored_file->get_filepath() . $stored_file->get_filename(), $hvp->id);
        $content_file = $fs->get_file_by_id($contentFileId);
        $this->assertFalse($content_file);
    }

    public function test_readFileTree(): void {
        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);
        $cm_context = context_module::instance($cm->id);
        $options = array(
            'contextid' => $cm_context->id,
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => $hvp->id,
            'filepath' => '/',
        );

        $tmp_path = $this->file_storage->getTmpPath();
        /** @var \mod_hvp\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        $library_id = 1;
        $basedirectory = $tmp_path . '/' . 'test-1.0';

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;
        [$lib, $libfiles] = $generator->create_library($basedirectory, $library_id, $machinename, $majorversion,
            $minorversion);
        $method = $this->getMethod('readFileTree');
        $method->invokeArgs(null, [$tmp_path, $options]);

        // check that the files were copied across.
        $fs = get_file_storage();
        $files = $fs->get_area_files($cm_context->id, 'mod_hvp', 'content', $hvp->id);
        $expected_files = ['library.json', 'testlib.min.js', 'testlib.min.css'];
        $found_files = [];
        foreach ($files as $file) {
            if ($file->get_filename() !== '.') {
                $found_files[] = $file->get_filename();
            }
        }
        $this->assertEqualsCanonicalizing($expected_files, $found_files);
    }

    public function test_exportFileTree(): void {
        global $CFG;

        $export_tree_path = $CFG->tempdir . DIRECTORY_SEPARATOR . 'export_file_tree_test';
        $tmp_path = $this->file_storage->getTmpPath();

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;

        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);
        $cm_context = context_module::instance($cm->id);

        $fs = get_file_storage();
        $filerecord = (object) [
            'contextid' => $cm_context->id,
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => $hvp->id,
            'filepath' => $tmp_path . DIRECTORY_SEPARATOR,
            'filename' => 'content.txt',
        ];
        $test_file_content = 'content_file_contents';
        $fs->create_file_from_string($filerecord, $test_file_content);

        $method = $this->getMethod('exportFileTree');
        $method->invokeArgs(null, [$export_tree_path, $cm_context->id, 'content', $tmp_path . DIRECTORY_SEPARATOR, $hvp->id]);

        // check that the files have been copied across.
        $files = scandir($export_tree_path);
        $exported_file_content = file_get_contents($export_tree_path . DIRECTORY_SEPARATOR . 'content.txt');
        $this->assertSame($test_file_content, $exported_file_content);
    }

    public function test_deleteFileTree(): void {
        $tmp_path = $this->file_storage->getTmpPath();
        check_dir_exists($tmp_path);
        $tmp_filename = 'library.json';

        // setup the file for test
        $context = context_system::instance();
        $fs = get_file_storage();
        $filerecord = (object) [
            'contextid' => $context->id,
            'component' => 'mod_hvp',
            'filearea' => 'libraries',
            'itemid' => 0,
            'filepath' => $tmp_path . DIRECTORY_SEPARATOR,
            'filename' => $tmp_filename,
        ];
        // create the file
        /** @var stored_file $stored_file */
        $stored_file = $fs->create_file_from_string($filerecord, 'testing');
        $this->assertEquals('testing', $stored_file->get_content());
        $file_exists = $fs->file_exists($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename);
        $this->assertTrue($file_exists);

        // ensure the file is deleted for a specific file
        $deleteFileTree = self::getMethod('deleteFileTree');
        $deleteFileTree->invokeArgs($this->file_storage, [$filerecord->contextid, $filerecord->filearea, $filerecord->filepath, $filerecord->itemid]);
        $file_exists = $fs->file_exists($filerecord->contextid, $filerecord->component, $filerecord->filearea, $filerecord->itemid, $filerecord->filepath, $filerecord->filename);
        $this->assertFalse($file_exists);
    }

    public function test_getFile(): void {
        global $CFG;

        $export_tree_path = $CFG->tempdir . DIRECTORY_SEPARATOR . 'export_file_tree_test';
        $tmp_path = $this->file_storage->getTmpPath();

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;

        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);
        $cm_context = context_module::instance($cm->id);

        $fs = get_file_storage();
        $filerecord = (object) [
            'contextid' => $cm_context->id,
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => $hvp->id,
            'filepath' => $tmp_path . DIRECTORY_SEPARATOR,
            'filename' => 'content.txt',
        ];
        $test_file_content = 'content_file_contents';
        $stored_file = $fs->create_file_from_string($filerecord, $test_file_content);

        $method = $this->getMethod('getFile');
        /** @var stored_file $file */
        $file = $method->invokeArgs($this->file_storage, ['content', $hvp->id, $stored_file->get_filepath() . $stored_file->get_filename()]);
        $this->assertEquals($test_file_content, $file->get_content());
    }

    public function test_fileExists(): void {
        global $CFG;

        $tmp_path = $this->file_storage->getTmpPath();

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;

        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);
        $cm = get_coursemodule_from_instance('hvp', $hvp->id);
        $cm_context = context_module::instance($cm->id);

        $fs = get_file_storage();
        $filerecord = (object) [
            'contextid' => $cm_context->id,
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => 0,
            'filepath' => $tmp_path . DIRECTORY_SEPARATOR,
            'filename' => 'content.txt',
        ];
        $test_file_content = 'content_file_contents';
        $stored_file = $fs->create_file_from_string($filerecord, $test_file_content);
        $found = $this->file_storage::fileExists($cm_context->id, 'content', $stored_file->get_filepath(), $stored_file->get_filename());
        $this->assertTrue($found);
        $this->file_storage->deleteContent(['coursemodule' => $cm->id, 'id' => 0]);
        $found = $this->file_storage::fileExists($cm_context->id, 'content', $stored_file->get_filepath(), $stored_file->get_filename());
        $this->assertFalse($found);
    }

    public function test_hasWriteAccess(): void {
        global $CFG;
        $CFG->dataroot = $CFG->dataroot . DIRECTORY_SEPARATOR . 'does_not_exists' . uniqid('unknown');
        // check with a non-existing dataroot
        $access = $this->file_storage->hasWriteAccess();
        $this->assertFalse($access);
        $this->assertDebuggingCalled("Path is not a directory {$CFG->dataroot}");
    }

    public function test_moveContentDirectory(): void {
        global $DB;

        // Create temp folder.
        $tempfolder = make_request_directory(false);

        // Create H5P content folder.
        $h5pcontentfolder = $tempfolder . '/fakeH5Pcontent';
        $contentfolder = $h5pcontentfolder . '/content';
        if (!check_dir_exists($contentfolder, true, true)) {
            throw new moodle_exception('error_creating_temp_dir', 'error', $contentfolder);
        }

        // Add content.json file.
        touch($contentfolder . 'content.json');

        // Create several folders and files inside content folder.
        $filesexpected = array();
        $numfolders = random_int(2, 5);
        for ($numfolder = 1; $numfolder < $numfolders; $numfolder++) {
            $foldername = '/folder' . $numfolder;
            $newfolder = $contentfolder . $foldername;
            if (!check_dir_exists($newfolder, true, true)) {
                throw new moodle_exception('error_creating_temp_dir', 'error', $newfolder);
            }
            $numfiles = random_int(2, 5);
            for ($numfile = 1; $numfile < $numfiles; $numfile++) {
                $filename = '/file' . $numfile . '.ext';
                touch($newfolder . $filename);
                $filesexpected[] = $foldername . $filename;
            }
        }

        $machinename = 'TestLib';
        $majorversion = 1;
        $minorversion = 0;

        $course = $this->getDataGenerator()->create_course();
        $record = new stdClass();
        $record->course = $course->id;
        $record->name = 'test_hvp_act1';
        $record->h5paction = 'test';
        $record->h5plibrary = "{$machinename}-{$majorversion}.{$minorversion}";
        $record->metadata = (object) ['title' => 'test activity'];
        $record->params = 'test';
        $record->disable = false;
        $hvp = $this->getDataGenerator()->create_module('hvp', $record);

        $this->file_storage->moveContentDirectory($h5pcontentfolder, $hvp->id);

        // Get database records.
        $sql = "SELECT concat(filepath, filename)
                  FROM {files}
                 WHERE filearea = :filearea AND itemid = :itemid AND component = :component AND filename != '.'";
        $params = [
            'component' => 'mod_hvp',
            'filearea' => 'content',
            'itemid' => $hvp->id
        ];
        $filesdb = $DB->get_fieldset_sql($sql, $params);
        sort($filesdb);

        // Check that created files match with database records.
        $this->assertEquals($filesexpected, $filesdb);
    }

    public function test_getUpgradeScript(): void {
        // Upload an upgrade file.
        $machinename = 'TestLib';
        $majorversion = 3;
        $minorversion = 1;
        $filepath = '/' . "{$machinename}-{$majorversion}.{$minorversion}" . '/';
        $fs = get_file_storage();
        $filerecord = [
            'contextid' => \context_system::instance()->id,
            'component' => 'mod_hvp',
            'filearea' => 'libraries',
            'itemid' => 0,
            'filepath' => $filepath,
            'filename' => 'upgrades.js'
        ];
        $filestorage = new file_storage();
        $fs->create_file_from_string($filerecord, 'test string info');
        $expectedfilepath = '/' . 'libraries' . $filepath . 'upgrades.js';
        $this->assertEquals($expectedfilepath, $filestorage->getUpgradeScript($machinename, $majorversion, $minorversion));
        $this->assertNull($filestorage->getUpgradeScript($machinename, $majorversion, 7));
    }

    public function test_saveFileFromZip() {
        // Create temp folder.
        $tempfolder = make_request_directory(false);

        $ziparchive = new zip_archive();
        $path = __DIR__ . '/fixtures/h5ptest.zip';
        $result = $ziparchive->open($path, file_archive::OPEN);

        $files = $ziparchive->list_files();
        foreach ($files as $file) {
            if (!$file->is_directory) {
                $stream = $ziparchive->get_stream($file->index);
                $items = explode('/', $file->pathname);
                array_shift($items);
                $path = implode('/', $items);
                $this->file_storage->saveFileFromZip($tempfolder, $path, $stream);
                $filestocheck[] = $path;
            }
        }
        $ziparchive->close();

        foreach ($filestocheck as $filetocheck) {
            $pathtocheck = $tempfolder . '/' . $filetocheck;
            $this->assertFileExists($pathtocheck);
        }
    }

    public function setUp(): void {
        $this->file_storage = new file_storage();
    }

    protected function tearDown(): void {
        $this->file_storage = null;
        parent::tearDown();
    }
}