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
require_once(__DIR__.'/fixtures/testable_antivirus.php');

class mod_hvp_file_storage_antivirus_test extends \core_phpunit\testcase {

    protected $tempfolder;
    protected $temp_ok_file;
    protected $temp_found_file;

    protected function setUp(): void {
        global $CFG;

        $CFG->antiviruses = 'testable_hvp';

        // create tempfiles
        $this->tempfolder = make_request_directory(false);
        $this->temp_ok_file = $this->tempfolder . '/OK';
        $this->temp_found_file = $this->tempfolder . '/FOUND';
        touch($this->temp_ok_file);
        touch($this->temp_found_file);
    }

    protected function tearDown(): void {
        @unlink($this->temp_ok_file);
        @unlink($this->temp_found_file);
        @rmdir($this->tempfolder);
        $this->tempfolder = null;
        $this->temp_ok_file = null;
        $this->temp_found_file = null;
        parent::tearDown();
    }

    /**
     * Test H5P storage scans zips and passes when file doesn't contain a virus
     *
     * @return void
     */
    public function test_savefilefromzip_scan_passes(): void {
        $failed = false;
        $this->assertFileExists($this->temp_ok_file);
        try {
            $file_storage = new \mod_hvp\file_storage();

            $file_storage->saveFileFromZip($this->tempfolder, 'OK', 'test data');
        } catch (\core\antivirus\scanner_exception $ex) {
            $failed = true;
            $this->fail('Exception scanner_exception is not expected in clean file scanning.');
        }
        $this->assertFalse($failed);
        $this->assertFileExists($this->temp_ok_file);
    }

    /**
     * Test H5P storage scans zips and removes if file contains a virus
     *
     * @return void
     */
    public function test_savefilefromzip_scan_fails(): void {
        $virusFound = false;
        $this->assertFileExists($this->temp_found_file);
        try {
            $file_storage = new \mod_hvp\file_storage();
            $file_storage->saveFileFromZip($this->tempfolder, 'FOUND', 'test data');
        } catch (\core\antivirus\scanner_exception $ex) {
            $virusFound = true;
        }
        $this->assertTrue($virusFound);
        $this->assertFileDoesNotExist($this->temp_found_file);
    }

    /**
     * Test H5P storage scans files for viurses from exports - these
     * have been uploaded through the editor - should pass if no virus is found
     *
     * @return void
     */
    public function test_saveexport_scan_passes(): void {
        $virusFound = false;
        $this->assertFileExists($this->temp_ok_file);
        try {
            $file_storage = new \mod_hvp\file_storage();
            $file_storage->saveExport($this->temp_ok_file, 'OK');
        } catch (\core\antivirus\scanner_exception $ex) {
            $virusFound = true;
        }
        $this->assertFalse($virusFound);
        $this->assertFileExists($this->temp_ok_file);
    }

    /**
     * Test H5P storage scans files for viurses from exports - these
     * have been uploaded through the editor - should fail and delete the file
     * if a virus is found
     *
     * @return void
     */
    public function test_saveexport_scan_fails(): void {
        $virusFound = false;
        $this->assertFileExists($this->temp_found_file);
        try {
            $file_storage = new \mod_hvp\file_storage();
            $file_storage->saveExport($this->temp_found_file, 'FOUND');
        } catch (\core\antivirus\scanner_exception $ex) {
            $virusFound = true;
        }
        $this->assertTrue($virusFound);
        $this->assertFileDoesNotExist($this->temp_found_file);
    }

    /**
     * Test H5P storage scans multiple files and deletes the virus when found
     *
     * @return void
     * @throws ReflectionException
     */
    public function test_readfiletree_deletes_virus_files(): void {
        $file_storage = new \mod_hvp\file_storage();
        $readFileTree = self::get_method('readFileTree');
        $options = [
            'filename' => 'OK',
            'contextid' => 1,
            'component' => 'user',
            'filearea' => 'tmp',
            'itemid' => 0,
            'filepath' => $this->tempfolder.'/',
        ];
        $virusFound = false;
        try {
            $readFileTree->invokeArgs($file_storage, [$this->tempfolder, $options]);
        } catch (\core\antivirus\scanner_exception $ex) {
            $virusFound = true;
        }
        // the FOUND file exists in the same DIR which should throw the exception still
        $this->assertTrue($virusFound);
        $this->assertFileExists($this->temp_ok_file);
        $this->assertFileDoesNotExist($this->temp_found_file);
    }

    /**
     * Reflection for testing readFileTree
     *
     * @param string $name
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    protected static function get_method(string $name): ReflectionMethod {
        $class = new ReflectionClass(\mod_hvp\file_storage::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }
}