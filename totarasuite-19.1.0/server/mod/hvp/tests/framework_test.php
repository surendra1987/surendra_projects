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

defined('MOODLE_INTERNAL') || die();

class mod_hvp_framework_test extends \core_phpunit\testcase {

    private $framework;

    public function setUp(): void {
        $this->framework = new \mod_hvp\framework();
    }

    protected function tearDown(): void {
        $this->framework = null;
        parent::tearDown();
    }

    public function test_instance() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $validator = \mod_hvp\framework::instance('validator');
        $storage = \mod_hvp\framework::instance('storage');
        $content_validator = \mod_hvp\framework::instance('contentvalidator');
        $editor = \mod_hvp\framework::instance('editor');

        $this->assertInstanceOf(H5PValidator::class, $validator);
        $this->assertInstanceOf(H5PStorage::class, $storage);
        $this->assertInstanceOf(H5PContentValidator::class, $content_validator);
    }

    /**
     * Test the behaviour of getPlatformInfo().
     * @return void
     */
    public function test_getPlatformInfo() {
        global $CFG;

        $framework = new \mod_hvp\framework();
        $platformInfo = $framework->getPlatformInfo();
        $expected = [
            'name' => 'Moodle',
            'version' => $CFG->version,
            'h5pVersion' => get_component_version('mod_hvp'),
        ];

        $this->assertEqualsCanonicalizing($expected, $platformInfo);
    }

    /**
     * Test the behaviour of fetchExternalData() when the URL is invalid.
     */
    public function test_fetchExternalData_url_invalid() {
        // Provide an invalid URL to an external file.
        $url = "someprotocol://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf";

        $framework = new \mod_hvp\framework();
        $data = $framework->fetchExternalData($url, null, true);

        // The response should be invalid.
        $this->assertEquals('Invalid URL specified', $data);
    }

    public function test_setLibraryTutorialUrl() {
        global $DB;

        $framework = new \mod_hvp\framework();

        /** @var mod_hvp_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        // Create several libraries records.
        $lib1 = $generator->create_library_record('Library1', 'Lib1', 1, 0, 1, '', null, 'http://tutorial1.org',
            'http://example.org');
        $lib2 = $generator->create_library_record('Library2', 'Lib2', 2, 0, 1, '', null, 'http://tutorial2.org');
        $lib3 = $generator->create_library_record('Library3', 'Lib3', 3, 0);

        // Check only lib1 tutorial URL is updated.
        $url = 'https://newtutorial.cat';
        $this->framework->setLibraryTutorialUrl($lib1->machine_name, $url);

        $libraries = $DB->get_records('hvp_libraries');
        $this->assertEquals($libraries[$lib1->id]->tutorial_url, $url);
        $this->assertNotEquals($libraries[$lib2->id]->tutorial_url, $url);

        // Check lib1 tutorial URL is set to null.
        $this->framework->setLibraryTutorialUrl($lib1->machine_name, null);

        $libraries = $DB->get_records('hvp_libraries');
        $this->assertCount(3, $libraries);
        $this->assertNull($libraries[$lib1->id]->tutorial_url);

        // Check no tutorial URL is set if library name doesn't exist.
        $this->framework->setLibraryTutorialUrl('Unexisting library', $url);

        $libraries = $DB->get_records('hvp_libraries');
        $this->assertCount(3, $libraries);
        $this->assertNull($libraries[$lib1->id]->tutorial_url);
        $this->assertEquals($libraries[$lib2->id]->tutorial_url, 'http://tutorial2.org');
        $this->assertNull($libraries[$lib3->id]->tutorial_url);

        // Check tutorial is set as expected when it was null.
        $this->framework->setLibraryTutorialUrl($lib3->machine_name, $url);

        $libraries = $DB->get_records('hvp_libraries');
        $this->assertEquals($libraries[$lib3->id]->tutorial_url, $url);
        $this->assertNull($libraries[$lib1->id]->tutorial_url);
        $this->assertEquals($libraries[$lib2->id]->tutorial_url, 'http://tutorial2.org');
    }


    /**
     * Test the behaviour of setErrorMessage().
     */
    public function test_setErrorMessage() {
        // Set an error message and an error code.
        $message = "Error message";
        $code = '404';

        // Set an error message.
        $this->framework->setErrorMessage($message, $code);

        // Get the error messages.
        $errormessages = $this->framework->getMessages('error');

        $expected = new \stdClass();
        $expected->code = 404;
        $expected->message = 'Error message';

        $this->assertEquals($expected, $errormessages[0]);
    }

    /**
     * Test the behaviour of setInfoMessage().
     */
    public function test_setInfoMessage() {
        $message = "Info message";

        // Set an info message.
        $this->framework->setInfoMessage($message);

        // Get the info messages.
        $infomessages = $this->framework->getMessages('info');

        $expected = 'Info message';

        $this->assertEquals($expected, $infomessages[0]);
    }

    /**
     * Test the behaviour of getMessages() when requesting the info messages.
     */
    public function test_getMessages_info() {
        // Set an info message.
        $this->framework->setInfoMessage("Info message");
        // Set an error message.
        $this->framework->setErrorMessage("Error message 1", 404);

        // Get the info messages.
        $infomessages = $this->framework->getMessages('info');

        $expected = 'Info message';

        // Make sure that only the info message has been returned.
        $this->assertCount(1, $infomessages);
        $this->assertEquals($expected, $infomessages[0]);

        $infomessages = $this->framework->getMessages('info');

        // Make sure the info messages have now been removed.
        $this->assertEmpty($infomessages);
    }

    /**
     * Test the behaviour of getMessages() when requesting the error messages.
     */
    public function test_getMessages_error() {
        // Set an info message.
        $this->framework->setInfoMessage("Info message");
        // Set an error message.
        $this->framework->setErrorMessage("Error message 1", 404);
        // Set another error message.
        $this->framework->setErrorMessage("Error message 2", 403);

        // Get the error messages.
        $errormessages = $this->framework->getMessages('error');

        // Make sure that only the error messages are being returned.
        $this->assertEquals(2, count($errormessages));

        $expected1 = (object) [
            'code' => 404,
            'message' => 'Error message 1'
        ];

        $expected2 = (object) [
            'code' => 403,
            'message' => 'Error message 2'
        ];

        $this->assertEquals($expected1, $errormessages[0]);
        $this->assertEquals($expected2, $errormessages[1]);

        $errormessages = $this->framework->getMessages('error');

        // Make sure the info messages have now been removed.
        $this->assertEmpty($errormessages);
    }

    /**
     * Test the behaviour of t() when translating existing string that does not require any arguments.
     */
    public function test_t_existing_string_no_args() {
        // Existing language string without passed arguments.
        $translation = $this->framework->t('No copyright information available for this content.');

        // Make sure the string translation has been returned.
        $this->assertEquals('No copyright information available for this content.', $translation);
    }

    /**
     * Test the behaviour of t() when translating existing string that does require parameters.
     */
    public function test_t_existing_string_args() {
        // Existing language string with passed arguments.
        $translation = $this->framework->t('Illegal option %option in %library',
            ['%option' => 'example', '%library' => 'Test library']);

        // Make sure the string translation has been returned.
        $this->assertEquals('Illegal option example in Test library', $translation);
    }

    /**
     * Test the behaviour of t() when translating non-existent string.
     */
    public function test_t_non_existent_string() {
        // Non-existing language string.
        $message = 'Random message %option';

        $translation = $this->framework->t($message);

        // As the string does not exist in the mapping array, make sure the passed message is returned.
        $this->assertEquals($message, $translation);
    }

    /**
     * Test the behaviour of getLibraryFileUrl() when requesting a file URL from an existing library and
     * the folder name is parsable.
     **/
    public function test_getLibraryFileUrl() {
        global $CFG;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        // Create a library record.
        $lib = $generator->create_library_record('Library', 'Lib', 1, 1);

        $expected = "{$CFG->wwwroot}/pluginfile.php/1/mod_hvp/libraries/Library-1.1/library.json";

        // Get the URL of a file from an existing library. The provided folder name is parsable.
        $actual = $this->framework->getLibraryFileUrl('Library-1.1', 'library.json');

        // Make sure the expected URL is returned.
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the behaviour of getLibraryFileUrl() when requesting a file URL from a library that has multiple
     * versions and the folder name is parsable.
     **/
    public function test_getLibraryFileUrl_library_has_multiple_versions() {
        global $CFG;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        // Create library records with a different minor version.
        $lib1 = $generator->create_library_record('Library', 'Lib', 1, 1);
        $lib2 = $generator->create_library_record('Library', 'Lib', 1, 3);

        $expected = "{$CFG->wwwroot}/pluginfile.php/1/mod_hvp/libraries/Library-1.3/library.json";

        // Get the URL of a file from an existing library (Library 1.3). The provided folder name is parsable.
        $actual = $this->framework->getLibraryFileUrl('Library-1.3', 'library.json');

        // Make sure the proper URL (from the requested library version) is returned.
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the behaviour of getLibraryFileUrl() when requesting a file URL from a library that has multiple
     * patch versions and the folder name is parsable.
     **/
    public function test_getLibraryFileUrl_library_has_multiple_patch_versions() {
        global $CFG;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        // Create library records with a different patch version.
        $lib1 = $generator->create_library_record('Library', 'Lib', 1, 1, 2);
        $lib2 = $generator->create_library_record('Library', 'Lib', 1, 1, 4);
        $lib3 = $generator->create_library_record('Library', 'Lib', 1, 1, 3);

        $expected = "{$CFG->wwwroot}/pluginfile.php/1/mod_hvp/libraries/Library-1.1/library.json";

        // Get the URL of a file from an existing library. The provided folder name is parsable.
        $actual = $this->framework->getLibraryFileUrl('Library-1.1', 'library.json');

        // Make sure the proper URL (from the latest library patch) is returned.
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the behaviour of getLibraryFileUrl() when requesting a file URL from a sub-folder
     * of an existing library and the folder name is parsable.
     **/
    public function test_getLibraryFileUrl_library_subfolder() {
        global $CFG;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        // Create a library record.
        $lib = $generator->create_library_record('Library', 'Lib', 1, 1);

        $expected = "{$CFG->wwwroot}/pluginfile.php/1/mod_hvp/libraries/Library-1.1/css/example.css";

        // Get the URL of a file from a sub-folder from an existing library. The provided folder name is parsable.
        $actual = $this->framework->getLibraryFileUrl('Library-1.1/css', 'example.css');

        // Make sure the proper URL is returned.
        $this->assertEquals($expected, $actual);
    }

    /**
     * Test the behaviour of loadAddons().
     */
    public function test_loadAddons() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a Library addon (1.1).
        $generator->create_library_record('Library', 'Lib', 1, 1, 2,
            '', '/regex1/');
        // Create a Library addon (1.3).
        $generator->create_library_record('Library', 'Lib', 1, 3, 2,
            '', '/regex2/');
        // Create a Library addon (1.2).
        $generator->create_library_record('Library', 'Lib', 1, 2, 2,
            '', '/regex3/');
        // Create a Library1 addon (1.2)
        $generator->create_library_record('Library1', 'Lib1', 1, 2, 2,
            '', '/regex11/');

        // Load the latest version of each addon.
        $addons = $this->framework->loadAddons();

        // The addons array should return 2 results (Library and Library1 addon).
        $this->assertCount(2, $addons);

        // Ensure the addons array is consistently ordered before asserting their contents.
        core_collator::asort_array_of_arrays_by_key($addons, 'machineName');
        [$addonone, $addontwo] = array_values($addons);

        // Make sure the version 1.3 is the latest 'Library' addon version.
        $this->assertEquals('Library', $addonone['machineName']);
        $this->assertEquals(1, $addonone['majorVersion']);
        $this->assertEquals(3, $addonone['minorVersion']);

        // Make sure the version 1.2 is the latest 'Library1' addon version.
        $this->assertEquals('Library1', $addontwo['machineName']);
        $this->assertEquals(1, $addontwo['majorVersion']);
        $this->assertEquals(2, $addontwo['minorVersion']);
    }

    /**
     * Test the behaviour of loadLibraries().
     */
    public function test_loadLibraries() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $generator->generate_h5p_data();

        // Load all libraries.
        $libraries = $this->framework->loadLibraries();

        // Make sure all libraries are returned.
        $this->assertNotEmpty($libraries);
        $this->assertCount(6, $libraries);
        $this->assertEquals('MainLibrary', $libraries['MainLibrary'][0]->machine_name);
        $this->assertEquals('1', $libraries['MainLibrary'][0]->major_version);
        $this->assertEquals('0', $libraries['MainLibrary'][0]->minor_version);
        $this->assertEquals('1', $libraries['MainLibrary'][0]->patch_version);
    }

    /**
     * Test the behaviour of test_getLibraryId() when requesting an existing machine name.
     */
    public function test_getLibraryId_existing_machine_name() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library.
        $lib = $generator->create_library_record('Library', 'Lib', 1, 1, 2);

        // Request the library ID of the library with machine name 'Library'.
        $libraryid = $this->framework->getLibraryId('Library');

        // Make sure the library ID is being returned.
        $this->assertNotFalse($libraryid);
        $this->assertEquals($lib->id, $libraryid);
    }

    /**
     * Test the behaviour of test_getLibraryId() when requesting a non-existent machine name.
     */
    public function test_getLibraryId_non_existent_machine_name() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library.
        $generator->create_library_record('Library', 'Lib', 1, 1, 2);

        // Request the library ID of the library with machinename => 'TestLibrary' (non-existent).
        $libraryid = $this->framework->getLibraryId('TestLibrary');

        // Make sure the library ID not being returned.
        $this->assertFalse($libraryid);
    }

    /**
     * Test the behaviour of test_getLibraryId() when requesting a non-existent major version.
     */
    public function test_getLibraryId_non_existent_major_version() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library.
        $generator->create_library_record('Library', 'Lib', 1, 1, 2);

        // Request the library ID of the library with machine name => 'Library', majorversion => 2 (non-existent).
        $libraryid = $this->framework->getLibraryId('Library', 2);

        // Make sure the library ID not being returned.
        $this->assertFalse($libraryid);
    }

    /**
     * Test the behaviour of test_getLibraryId() when requesting a non-existent minor version.
     */
    public function test_getLibraryId_non_existent_minor_version() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library.
        $generator->create_library_record('Library', 'Lib', 1, 1, 2);

        // Request the library ID of the library with machine name => 'Library',
        // majorversion => 1,  minorversion => 2 (non-existent).
        $libraryid = $this->framework->getLibraryId('Library', 1, 2);

        // Make sure the library ID not being returned.
        $this->assertFalse($libraryid);
    }

    /**
     * Test the behaviour of isPatchedLibrary().
     *
     * @dataProvider test_isPatchedLibrary_provider
     * @param array $libraryrecords Array containing data for the library creation
     * @param array $testlibrary Array containing the test library data
     * @param bool $expected The expectation whether the library is patched or not
     **/
    public function test_isPatchedLibrary(array $libraryrecords, array $testlibrary, bool $expected): void {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        foreach ($libraryrecords as $library) {
            call_user_func_array([$generator, 'create_library_record'], $library);
        }

        $this->assertEquals($expected, $this->framework->isPatchedLibrary($testlibrary));
    }

    /**
     * Data provider for test_isPatchedLibrary().
     *
     * @return array
     */
    public static function test_isPatchedLibrary_provider(): array {
        return [
            'Unpatched library. No different versioning' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 1,
                    'minorVersion' => 1,
                    'patchVersion' => 2
                ],
                false,
            ],
            'Major version identical; Minor version identical; Patch version newer' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 1,
                    'minorVersion' => 1,
                    'patchVersion' => 3
                ],
                true,
            ],
            'Major version identical; Minor version newer; Patch version newer' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 1,
                    'minorVersion' => 2,
                    'patchVersion' => 3
                ],
                false,
            ],
            'Major version identical; Minor version identical; Patch version older' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 1,
                    'minorVersion' => 1,
                    'patchVersion' => 1
                ],
                false,
            ],
            'Major version identical; Minor version newer; Patch version older' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 1,
                    'minorVersion' => 2,
                    'patchVersion' => 1
                ],
                false,
            ],
            'Major version newer; Minor version identical; Patch version older' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 2,
                    'minorVersion' => 1,
                    'patchVersion' => 1
                ],
                false,
            ],
            'Major version newer; Minor version identical; Patch version newer' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 2,
                    'minorVersion' => 1,
                    'patchVersion' => 3
                ],
                false,
            ],

            'Major version older; Minor version identical; Patch version older' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 0,
                    'minorVersion' => 1,
                    'patchVersion' => 1
                ],
                false,
            ],
            'Major version older; Minor version identical; Patch version newer' => [
                [
                    ['TestLibrary', 'Test', 1, 1, 2],
                ],
                [
                    'machineName' => 'TestLibrary',
                    'majorVersion' => 0,
                    'minorVersion' => 1,
                    'patchVersion' => 3
                ],
                false,
            ],
        ];
    }

    /**
     * Test the behaviour of isInDevMode().
     */
    public function test_isInDevMode() {
        $isdevmode = $this->framework->isInDevMode();

        $this->assertFalse($isdevmode);
    }

    /**
     * Test the behaviour of mayUpdateLibraries().
     */
    public function test_mayUpdateLibraries(): void {
        global $DB;


        // Create some users.
        $contextsys = \context_system::instance();
        $user = $this->getDataGenerator()->create_user();
        $admin = get_admin();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager'], '*', MUST_EXIST);
        $studentrole = $DB->get_record('role', ['shortname' => 'student'], '*', MUST_EXIST);
        $manager = $this->getDataGenerator()->create_user();
        role_assign($managerrole->id, $manager->id, $contextsys);

        // Create a course with a label and enrol the user.
        $course = $this->getDataGenerator()->create_course();
        $label = $this->getDataGenerator()->create_module('label', ['course' => $course->id]);
        list(, $labelcm) = get_course_and_cm_from_instance($label->id, 'label');
        $contextlabel = \context_module::instance($labelcm->id);
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');

        // Create the .h5p file.
        $path = __DIR__ . '/fixtures/h5ptest.zip';

        // Admin and manager should have permission to update libraries.
        $file = $this->create_fake_stored_file_from_path($path, $admin->id, $contextsys);

        $this->setAdminUser();
        $mayupdatelib = $this->framework->mayUpdateLibraries();
        $this->assertTrue($mayupdatelib);

        $file = $this->create_fake_stored_file_from_path($path, $manager->id, $contextsys);
        $this->setUser($manager);
        $mayupdatelib = $this->framework->mayUpdateLibraries();
        $this->assertTrue($mayupdatelib);

        // By default, normal user hasn't permission to update libraries (in both contexts, system and module label).
        $file = $this->create_fake_stored_file_from_path($path, $user->id, $contextsys);
        $this->setUser($user);
        $mayupdatelib = $this->framework->mayUpdateLibraries();
        $this->assertFalse($mayupdatelib);

        $file = $this->create_fake_stored_file_from_path($path, $user->id, $contextlabel);
        $this->setUser($user);
        $mayupdatelib = $this->framework->mayUpdateLibraries();
        $this->assertFalse($mayupdatelib);

        // If the current user (admin) can update libraries, the method should return true (even if the file userid hasn't the
        // required capabilility in the file context).
        $file = $this->create_fake_stored_file_from_path($path, $admin->id, $contextlabel);
        $this->setAdminUser();
        $mayupdatelib = $this->framework->mayUpdateLibraries();
        $this->assertTrue($mayupdatelib);
    }

    /**
     * Test the behaviour of saveLibraryData() when saving data for a new library.
     */
    public function test_saveLibraryData_new_library() {
        global $DB;


        $librarydata = array(
            'title' => 'Test',
            'machineName' => 'TestLibrary',
            'majorVersion' => '1',
            'minorVersion' => '0',
            'patchVersion' => '2',
            'runnable' => 1,
            'fullscreen' => 1,
            'preloadedJs' => array(
                array(
                    'path' => 'js/name.min.js'
                )
            ),
            'preloadedCss' => array(
                array(
                    'path' => 'css/name.css'
                )
            ),
            'dropLibraryCss' => array(
                array(
                    'machineName' => 'Name2'
                )
            ),
            'metadataSettings' => '',
        );

        // Create a new library.
        $this->framework->saveLibraryData($librarydata);

        $library = $DB->get_record('hvp_libraries', ['machine_name' => $librarydata['machineName']]);

        // Make sure the library data was properly saved.
        $this->assertNotEmpty($library);
        $this->assertNotEmpty($librarydata['libraryId']);
        $this->assertEquals($librarydata['title'], $library->title);
        $this->assertEquals($librarydata['machineName'], $library->machine_name);
        $this->assertEquals($librarydata['majorVersion'], $library->major_version);
        $this->assertEquals($librarydata['minorVersion'], $library->minor_version);
        $this->assertEquals($librarydata['patchVersion'], $library->patch_version);
        $this->assertEquals($librarydata['preloadedJs'][0]['path'], $library->preloaded_js);
        $this->assertEquals($librarydata['preloadedCss'][0]['path'], $library->preloaded_css);
        $this->assertEquals($librarydata['dropLibraryCss'][0]['machineName'], $library->drop_library_css);
    }

    /**
     * Test the behaviour of saveLibraryData() when saving (updating) data for an existing library.
     */
    public function test_saveLibraryData_existing_library() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library record.
        $library = $generator->create_library_record('TestLibrary', 'Test', 1, 0, 2);

        $librarydata = array(
            'libraryId' => $library->id,
            'title' => 'Test1',
            'machineName' => 'TestLibrary',
            'majorVersion' => '1',
            'minorVersion' => '2',
            'patchVersion' => '2',
            'runnable' => 1,
            'fullscreen' => 1,
            'preloadedJs' => array(
                array(
                    'path' => 'js/name.min.js'
                )
            ),
            'preloadedCss' => array(
                array(
                    'path' => 'css/name.css'
                )
            ),
            'dropLibraryCss' => array(
                array(
                    'machineName' => 'Name2'
                )
            ),
            'metadataSettings' => '',
        );

        // Update the library.
        $this->framework->saveLibraryData($librarydata, false);

        $library = $DB->get_record('hvp_libraries', ['machine_name' => $librarydata['machineName']]);

        // Make sure the library data was properly updated.
        $this->assertNotEmpty($library);
        $this->assertNotEmpty($librarydata['libraryId']);
        $this->assertEquals($librarydata['title'], $library->title);
        $this->assertEquals($librarydata['machineName'], $library->machine_name);
        $this->assertEquals($librarydata['majorVersion'], $library->major_version);
        $this->assertEquals($librarydata['minorVersion'], $library->minor_version);
        $this->assertEquals($librarydata['patchVersion'], $library->patch_version);
        $this->assertEquals($librarydata['preloadedJs'][0]['path'], $library->preloaded_js);
        $this->assertEquals($librarydata['preloadedCss'][0]['path'], $library->preloaded_css);
        $this->assertEquals($librarydata['dropLibraryCss'][0]['machineName'], $library->drop_library_css);
    }

    /**
     * Test the behaviour of insertContent().
     */
    public function test_insertContent() {
        global $DB;


        $content = array(
            'params' => json_encode(['param1' => 'Test']),
            'library' => array(
                'libraryId' => 1,
                'machineName' => 'H5PFlashcards',
                'majorVersion' => 1,
                'minorVersion' => 1,
            ),
            'disable' => 8,
            'metadata' => [
            ],
            'name' => 'test',
            'course' => 1,
            'intro' => '',
            'introformat' => 1,

        );

        // Insert h5p content.
        $contentid = $this->framework->insertContent($content);

        // Get the entered content from the db.
        $dbcontent = $DB->get_record('hvp', ['id' => $contentid]);

        // Make sure the h5p content was properly inserted.
        $this->assertNotEmpty($dbcontent);
        $this->assertEquals($content['params'], $dbcontent->json_content);
        $this->assertEquals($content['library']['libraryId'], $dbcontent->main_library_id);
    }

    /**
     * Test the behaviour of insertContent().
     */
    public function test_insertContent_latestlibrary() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');
        // Create a library record.
        $lib = $generator->create_library_record('TestLibrary', 'Test', 1, 1, 2);

        $content = array(
            'params' => json_encode(['param1' => 'Test']),
            'library' => array(
                'libraryId' => $lib->id,
                'machineName' => 'TestLibrary',
                'majorVersion' => 1,
                'minorVersion' => 1,
                'patchVersion' => 2,
            ),
            'disable' => 8,
            'metadata' => [
            ],
            'name' => 'test',
            'course' => 1,
            'intro' => '',
            'introformat' => 1,
        );

        // Insert h5p content.
        $contentid = $this->framework->insertContent($content);

        // Get the entered content from the db.
        $dbcontent = $DB->get_record('hvp', ['id' => $contentid]);

        // Make sure the h5p content was properly inserted.
        $this->assertNotEmpty($dbcontent);
        $this->assertEquals($content['params'], $dbcontent->json_content);
        $this->assertEquals($content['disable'], $dbcontent->disable);
        // As the libraryId was empty, the latest library has been used.
        $this->assertEquals($lib->id, $dbcontent->main_library_id);
    }

    /**
     * Test the behaviour of updateContent().
     */
    public function test_updateContent() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library record.
        $lib = $generator->create_library_record('TestLibrary', 'Test', 1, 1, 2);

        // Create an h5p content with 'TestLibrary' as it's main library.
        $contentid = $generator->create_h5p_record($lib->id);

        $content = array(
            'id' => $contentid,
            'params' => json_encode(['param2' => 'Test2']),
            'library' => array(
                'libraryId' => $lib->id,
                'machineName' => 'TestLibrary',
                'majorVersion' => 1,
                'minorVersion' => 1,
                'patchVersion' => 2,
            ),
            'disable' => 8,
            'metadata' => [],
            'name' => 'test',
            'course' => 1,
            'intro' => '',
            'introformat' => 1,
        );

        // Update the h5p content.
        $this->framework->updateContent($content);

        $h5pcontent = $DB->get_record('hvp', ['id' => $contentid]);

        // Make sure the h5p content was properly updated.
        $this->assertNotEmpty($h5pcontent);
        $this->assertEquals($content['params'], $h5pcontent->json_content);
        $this->assertEquals($content['library']['libraryId'], $h5pcontent->main_library_id);
        $this->assertEquals($content['disable'], $h5pcontent->disable);
    }

    /**
     * Test the behaviour of saveLibraryDependencies().
     */
    public function test_saveLibraryDependencies() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library 'Library'.
        $library = $generator->create_library_record('Library', 'Title');
        // Create a library 'DependencyLibrary1'.
        $dependency1 = $generator->create_library_record('DependencyLibrary1', 'DependencyTitle1');
        // Create a library 'DependencyLibrary2'.
        $dependency2 = $generator->create_library_record('DependencyLibrary2', 'DependencyTitle2');

        $dependencies = array(
            array(
                'machineName' => $dependency1->machine_name,
                'majorVersion' => $dependency1->major_version,
                'minorVersion' => $dependency1->minor_version
            ),
            array(
                'machineName' => $dependency2->machine_name,
                'majorVersion' => $dependency2->major_version,
                'minorVersion' => $dependency2->minor_version
            ),
        );

        // Set 'DependencyLibrary1' and 'DependencyLibrary2' as library dependencies of 'Library'.
        $this->framework->saveLibraryDependencies($library->id, $dependencies, 'preloaded');

        $libdependencies = $DB->get_records('hvp_libraries_libraries', ['library_id' => $library->id], 'id ASC');

        // Make sure the library dependencies for 'Library' are properly set.
        $this->assertEquals(2, count($libdependencies));
        $this->assertEquals($dependency1->id, reset($libdependencies)->required_library_id);
        $this->assertEquals($dependency2->id, end($libdependencies)->required_library_id);
    }

    /**
     * Test the behaviour of deleteContentData().
     */
    public function test_deleteContentData() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate some h5p related data.
        $data = $generator->generate_h5p_data();
        $h5pid = $data->h5pcontent->h5pid;

        $h5pcontent = $DB->get_record('hvp', ['id' => $h5pid]);
        // Make sure the particular h5p content exists in the DB.
        $this->assertNotEmpty($h5pcontent);

        // Get the h5p content libraries from the DB.
        $h5pcontentlibraries = $DB->get_records('hvp_contents_libraries', ['hvp_id' => $h5pid]);

        // Make sure the content libraries exists in the DB.
        $this->assertNotEmpty($h5pcontentlibraries);
        $this->assertCount(5, $h5pcontentlibraries);

        // Delete the h5p content and it's related data.
        $this->framework->deleteContentData($h5pid);

        $h5pcontent = $DB->get_record('hvp', ['id' => $h5pid]);
        $h5pcontentlibraries = $DB->get_record('hvp_contents_libraries', ['hvp_id' => $h5pid]);

        // The particular h5p content should no longer exist in the db.
        $this->assertEmpty($h5pcontent);
        // The particular content libraries should no longer exist in the db.
        $this->assertEmpty($h5pcontentlibraries);
    }

    /**
     * Test the behaviour of deleteLibraryUsage().
     */
    public function test_deleteLibraryUsage() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate some h5p related data.
        $data = $generator->generate_h5p_data();
        $h5pid = $data->h5pcontent->h5pid;

        // Get the h5p content libraries from the DB.
        $h5pcontentlibraries = $DB->get_records('hvp_contents_libraries', ['hvp_id' => $h5pid]);

        // The particular h5p content should have 5 content libraries.
        $this->assertNotEmpty($h5pcontentlibraries);
        $this->assertCount(5, $h5pcontentlibraries);

        // Delete the h5p content and it's related data.
        $this->framework->deleteLibraryUsage($h5pid);

        // Get the h5p content libraries from the DB.
        $h5pcontentlibraries = $DB->get_record('hvp_contents_libraries', ['hvp_id' => $h5pid]);

        // The particular h5p content libraries should no longer exist in the db.
        $this->assertEmpty($h5pcontentlibraries);
    }

    /**
     * Test the behaviour of test_saveLibraryUsage().
     */
    public function test_saveLibraryUsage() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create a library 'Library'.
        $library = $generator->create_library_record('Library', 'Title');
        // Create a library 'DependencyLibrary1'.
        $dependency1 = $generator->create_library_record('DependencyLibrary1', 'DependencyTitle1');
        // Create a library 'DependencyLibrary2'.
        $dependency2 = $generator->create_library_record('DependencyLibrary2', 'DependencyTitle2');
        // Create an h5p content with 'Library' as it's main library.
        $contentid = $generator->create_h5p_record($library->id);

        $dependencies = array(
            array(
                'library' => array(
                    'libraryId' => $dependency1->id,
                    'machineName' => $dependency1->machine_name,
                    'dropLibraryCss' => $dependency1->drop_library_css
                ),
                'type' => 'preloaded',
                'weight' => 1
            ),
            array(
                'library' => array(
                    'libraryId' => $dependency2->id,
                    'machineName' => $dependency2->machine_name,
                    'dropLibraryCss' => $dependency2->drop_library_css
                ),
                'type' => 'preloaded',
                'weight' => 2
            ),
        );

        // Save 'DependencyLibrary1' and 'DependencyLibrary2' as h5p content libraries.
        $this->framework->saveLibraryUsage($contentid, $dependencies);

        // Get the h5p content libraries from the DB.
        $libdependencies = $DB->get_records('hvp_contents_libraries', ['hvp_id' => $contentid], 'id ASC');

        // Make sure that 'DependencyLibrary1' and 'DependencyLibrary2' are properly set as h5p content libraries.
        $this->assertEquals(2, count($libdependencies));
        $this->assertEquals($dependency1->id, reset($libdependencies)->library_id);
        $this->assertEquals($dependency2->id, end($libdependencies)->library_id);
    }

    /**
     * Test the behaviour of getLibraryUsage() without skipping a particular h5p content.
     */
    public function test_getLibraryUsage_no_skip_content() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $generateddata = $generator->generate_h5p_data();
        // The Id of the library 'Library1'.
        $library1id = $generateddata->lib1->data->id;
        // The Id of the library 'Library2'.
        $library2id = $generateddata->lib2->data->id;
        // The Id of the library 'Library5'.
        $library5id = $generateddata->lib5->data->id;

        // Get the library usage for 'Library1' (do not skip content).
        $data = $this->framework->getLibraryUsage($library1id);

        $expected = array(
            'content' => 1,
            'libraries' => 1
        );

        // Make sure 'Library1' is used by 1 content and is a dependency to 1 library.
        $this->assertEquals($expected, $data);

        // Get the library usage for 'Library2' (do not skip content).
        $data = $this->framework->getLibraryUsage($library2id);

        $expected = array(
            'content' => 1,
            'libraries' => 2,
        );

        // Make sure 'Library2' is used by 1 content and is a dependency to 2 libraries.
        $this->assertEquals($expected, $data);

        // Get the library usage for 'Library5' (do not skip content).
        $data = $this->framework->getLibraryUsage($library5id);

        $expected = array(
            'content' => 0,
            'libraries' => 1,
        );

        // Make sure 'Library5' is not used by any content and is a dependency to 1 library.
        $this->assertEquals($expected, $data);
    }

    /**
     * Test the behaviour of getLibraryUsage() when skipping a particular content.
     */
    public function test_getLibraryUsage_skip_content() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $generateddata = $generator->generate_h5p_data();
        // The Id of the library 'Library1'.
        $library1id = $generateddata->lib1->data->id;

        // Get the library usage for 'Library1' (skip content).
        $data = $this->framework->getLibraryUsage($library1id, true);
        $expected = array(
            'content' => -1,
            'libraries' => 1,
        );

        // Make sure 'Library1' is a dependency to 1 library.
        $this->assertEquals($expected, $data);
    }

    /**
     * Test the behaviour of loadLibrary() when requesting an existing library.
     */
    public function test_loadLibrary_existing_library() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $generateddata = $generator->generate_h5p_data();
        // The library data of 'Library1'.
        $library1 = $generateddata->lib1->data;
        // The library data of 'Library5'.
        $library5 = $generateddata->lib5->data;

        // The preloaded dependencies.
        $preloadeddependencies = array();

        foreach ($generateddata->lib1->dependencies as $preloadeddependency) {
            $preloadeddependencies[] = array(
                'machineName' => $preloadeddependency->machine_name,
                'majorVersion' => $preloadeddependency->major_version,
                'minorVersion' => $preloadeddependency->minor_version
            );
        }

        // Create a dynamic dependency.
        $generator->create_library_dependency_record($library1->id, $library5->id, 'dynamic');

        $dynamicdependencies[] = array(
            'machineName' => $library5->machine_name,
            'majorVersion' => $library5->major_version,
            'minorVersion' => $library5->minor_version
        );

        // Load 'Library1' data.
        $data = $this->framework->loadLibrary($library1->machine_name, $library1->major_version,
            $library1->minor_version);

        $expected = array(
            'libraryId' => $library1->id,
            'title' => $library1->title,
            'machineName' => $library1->machine_name,
            'majorVersion' => $library1->major_version,
            'minorVersion' => $library1->minor_version,
            'patchVersion' => $library1->patch_version,
            'runnable' => $library1->runnable,
            'fullscreen' => $library1->fullscreen,
            'embedTypes' => $library1->embed_types,
            'preloadedJs' => $library1->preloaded_js,
            'preloadedCss' => $library1->preloaded_css,
            'dropLibraryCss' => $library1->drop_library_css,
            'semantics' => $library1->semantics,
            'restricted' => $library1->restricted,
            'hasIcon' => $library1->has_icon,
            'dynamicDependencies' => $dynamicdependencies,
            'preloadedDependencies' => $preloadeddependencies,
        );

        // Make sure the 'Library1' data is properly loaded.
        $this->assertEquals($expected, $data);
    }

    /**
     * Test the behaviour of loadLibrarySemantics().
     *
     * @dataProvider test_loadLibrarySemantics_provider
     * @param array $libraryrecords Array containing data for the library creation
     * @param array $testlibrary Array containing the test library data
     * @param string $expected The expected semantics value
     **/
    public function test_loadLibrarySemantics(array $libraryrecords, array $testlibrary, string $expected): void {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        foreach ($libraryrecords as $library) {
            call_user_func_array([$generator, 'create_library_record'], $library);
        }

        $this->assertEquals($expected, $this->framework->loadLibrarySemantics(
            $testlibrary['machinename'], $testlibrary['majorversion'], $testlibrary['minorversion']));
    }

    /**
     * Data provider for test_loadLibrarySemantics().
     *
     * @return array
     */
    public static function test_loadLibrarySemantics_provider(): array {

        $semantics = json_encode(
            [
                'type' => 'text',
                'name' => 'text',
                'label' => 'Plain text',
                'description' => 'Please add some text'
            ]
        );

        return [
            'Library with semantics' => [
                [
                    ['Library1', 'Lib1', 1, 1, 2, $semantics],
                ],
                [
                    'machinename' => 'Library1',
                    'majorversion' => 1,
                    'minorversion' => 1
                ],
                $semantics,
            ],
            'Library without semantics' => [
                [
                    ['Library2', 'Lib2', 1, 2, 2, ''],
                ],
                [
                    'machinename' => 'Library2',
                    'majorversion' => 1,
                    'minorversion' => 2
                ],
                '',
            ]
        ];
    }

    /**
     * Test the behaviour of alterLibrarySemantics().
     */
    public function test_alterLibrarySemantics() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        $semantics = json_encode(
            array(
                'type' => 'text',
                'name' => 'text',
                'label' => 'Plain text',
                'description' => 'Please add some text'
            )
        );

        // Create a library 'Library1' with semantics.
        $library1 = $generator->create_library_record('Library1', 'Lib1', 1, 1, 2, $semantics);

        $updatedsemantics = array(
            'type' => 'text',
            'name' => 'updated text',
            'label' => 'Updated text',
            'description' => 'Please add some text'
        );

        // Alter the semantics of 'Library1'.
        $this->framework->alterLibrarySemantics($updatedsemantics, 'Library1', 1, 1);

        // Get the semantics of 'Library1' from the DB.
        $currentsemantics = $DB->get_field('hvp_libraries', 'semantics', array('id' => $library1->id));

        // The semantics for Library1 shouldn't be updated.
        $this->assertEquals($semantics, $currentsemantics);
    }

    /**
     * Test the behaviour of deleteLibraryDependencies() when requesting to delete the
     * dependencies of an existing library.
     */
    public function test_deleteLibraryDependencies_existing_library() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();
        // The data of the library 'Library1'.
        $library1 = $data->lib1->data;

        // Get the dependencies of 'Library1'.
        $dependencies = $DB->get_records('hvp_libraries_libraries', ['library_id' => $library1->id]);
        // The 'Library1' should have 3 dependencies ('Library2', 'Library3', 'Library4').
        $this->assertCount(3, $dependencies);

        // Delete the dependencies of 'Library1'.
        $this->framework->deleteLibraryDependencies($library1->id);

        $dependencies = $DB->get_records('hvp_libraries_libraries', ['library_id' => $library1->id]);
        // The 'Library1' should have 0 dependencies.
        $this->assertCount(0, $dependencies);
    }

    /**
     * Test the behaviour of deleteLibraryDependencies() when requesting to delete the
     * dependencies of a non-existent library.
     */
    public function test_deleteLibraryDependencies_non_existent_library() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();
        // The data of the library 'Library1'.
        $library1 = $data->lib1->data;

        // Get the dependencies of 'Library1'.
        $dependencies = $DB->get_records('hvp_libraries_libraries', ['library_id' => $library1->id]);
        // The 'Library1' should have 3 dependencies ('Library2', 'Library3', 'Library4').
        $this->assertCount(3, $dependencies);

        // Delete the dependencies of a non-existent library.
        $this->framework->deleteLibraryDependencies(0);

        $dependencies = $DB->get_records('hvp_libraries_libraries', ['library_id' => $library1->id]);
        // The 'Library1' should have 3 dependencies.
        $this->assertCount(3, $dependencies);
    }

    /**
     * Test the behaviour of loadContentDependencies() when requesting content dependencies
     * without specifying the dependency type.
     */
    public function test_loadContentDependencies_no_type_defined() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();
        // The Id of the h5p content.
        $h5pid = $data->h5pcontent->h5pid;
        // The content dependencies.
        $dependencies = $data->h5pcontent->contentdependencies;

        // Add Library5 as a content dependency (dynamic dependency type).
        $library5 = $data->lib5->data;
        $generator->create_contents_libraries_record($h5pid, $library5->id, 'dynamic');

        // Get all content dependencies.
        $contentdependencies = $this->framework->loadContentDependencies($h5pid);

        $expected = array();
        foreach ($dependencies as $dependency) {
            $expected[$dependency->machine_name] = array(
                'id' => $dependency->id,
                'machineName' => $dependency->machine_name,
                'majorVersion' => $dependency->major_version,
                'minorVersion' => $dependency->minor_version,
                'patchVersion' => $dependency->patch_version,
                'preloadedCss' => $dependency->preloaded_css,
                'preloadedJs' => $dependency->preloaded_js,
                'dropCss' => '0',
                'dependencyType' => 'preloaded'
            );
        }

        $expected = array_merge($expected,
            array(
                'Library5' => array(
                    'id' => $library5->id,
                    'machineName' => $library5->machine_name,
                    'majorVersion' => $library5->major_version,
                    'minorVersion' => $library5->minor_version,
                    'patchVersion' => $library5->patch_version,
                    'preloadedCss' => $library5->preloaded_css,
                    'preloadedJs' => $library5->preloaded_js,
                    'dropCss' => '0',
                    'dependencyType' => 'dynamic'
                )
            )
        );

        // The loaded content dependencies should return 6 libraries.
        $this->assertCount(6, $contentdependencies);
        $this->assertEquals($expected, $contentdependencies);
    }

    /**
     * Test the behaviour of loadContentDependencies() when requesting content dependencies
     * with specifying the dependency type.
     */
    public function test_loadContentDependencies_type_defined() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();
        // The Id of the h5p content.
        $h5pid = $data->h5pcontent->h5pid;
        // The content dependencies.
        $dependencies = $data->h5pcontent->contentdependencies;

        // Add Library5 as a content dependency (dynamic dependency type).
        $library5 = $data->lib5->data;
        $generator->create_contents_libraries_record($h5pid, $library5->id, 'dynamic');

        // Load all content dependencies of dependency type 'preloaded'.
        $preloadeddependencies = $this->framework->loadContentDependencies($h5pid, 'preloaded');

        $expected = array();
        foreach ($dependencies as $dependency) {
            $expected[$dependency->machine_name] = array(
                'id' => $dependency->id,
                'machineName' => $dependency->machine_name,
                'majorVersion' => $dependency->major_version,
                'minorVersion' => $dependency->minor_version,
                'patchVersion' => $dependency->patch_version,
                'preloadedCss' => $dependency->preloaded_css,
                'preloadedJs' => $dependency->preloaded_js,
                'dropCss' => '0',
                'dependencyType' => 'preloaded'
            );
        }

        // The loaded content dependencies should return 5 libraries.
        $this->assertCount(5, $preloadeddependencies);
        $this->assertEquals($expected, $preloadeddependencies);

        // Load all content dependencies of dependency type 'dynamic'.
        $dynamicdependencies = $this->framework->loadContentDependencies($h5pid, 'dynamic');

        $expected = array(
            'Library5' => array(
                'id' => $library5->id,
                'machineName' => $library5->machine_name,
                'majorVersion' => $library5->major_version,
                'minorVersion' => $library5->minor_version,
                'patchVersion' => $library5->patch_version,
                'preloadedCss' => $library5->preloaded_css,
                'preloadedJs' => $library5->preloaded_js,
                'dropCss' => '0',
                'dependencyType' => 'dynamic'
            )
        );

        // The loaded content dependencies should now return 1 library.
        $this->assertCount(1, $dynamicdependencies);
        $this->assertEquals($expected, $dynamicdependencies);
    }

    /**
     * Test the behaviour of getOption().
     */
    public function test_getOption(): void {

        // Get value for display_option_download.
        $value = $this->framework->getOption(\H5PCore::DISPLAY_OPTION_DOWNLOAD);
        $expected = \H5PDisplayOptionBehaviour::ALWAYS_SHOW;
        $this->assertEquals($expected, $value);

        // Get value for display_option_embed using default value (it should be ignored).
        $value = $this->framework->getOption(\H5PCore::DISPLAY_OPTION_EMBED, \H5PDisplayOptionBehaviour::NEVER_SHOW);
        $expected = \H5PDisplayOptionBehaviour::ALWAYS_SHOW;
        $this->assertEquals($expected, $value);

        // Get value for unexisting setting without default.
        $value = $this->framework->getOption('unexistingsetting');
        $expected = false;
        $this->assertEquals($expected, $value);

        // Get value for unexisting setting with default.
        $value = $this->framework->getOption('unexistingsetting', 'defaultvalue');
        $expected = 'defaultvalue';
        $this->assertEquals($expected, $value);
    }

    /**
     * Test the behaviour of setOption().
     */
    public function test_setOption(): void {

        // Set value for 'newsetting' setting.
        $name = 'newsetting';
        $value = $this->framework->getOption($name);
        $this->assertEquals(false, $value);
        $newvalue = 'value1';
        $this->framework->setOption($name, $newvalue);
        $value = $this->framework->getOption($name);
        $this->assertEquals($newvalue, $value);

        // Set value for display_option_download and then get it again. Check it hasn't changed.
        $name = \H5PCore::DISPLAY_OPTION_DOWNLOAD;
        $newvalue = \H5PDisplayOptionBehaviour::NEVER_SHOW;
        $this->framework->setOption($name, $newvalue);
        $value = $this->framework->getOption($name);
        $expected = \H5PDisplayOptionBehaviour::NEVER_SHOW;
        $this->assertEquals($expected, $value);
    }

    /**
     * Test the behaviour of updateContentFields().
     */
    public function test_updateContentFields() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create 'Library1' library.
        $library1 = $generator->create_library_record('Library1', 'Lib1', 1, 1, 2);
        // Create 'Library2' library.
        $library2 = $generator->create_library_record('Library2', 'Lib2', 1, 1, 2);

        // Create an h5p content with 'Library1' as it's main library.
        $h5pid = $generator->create_h5p_record($library1->id, ['json_content' => 'iframe']);

        $updatedata = array(
            'json_content' => json_encode(['value' => 'test']),
            'main_library_id' => $library2->id
        );

        // Update h5p content fields.
        $this->framework->updateContentFields($h5pid, $updatedata);

        // Get the h5p content from the DB.
        $h5p = $DB->get_record('hvp', ['id' => $h5pid]);

        $expected = json_encode(['value' => 'test']);

        // Make sure the h5p content fields are properly updated.
        $this->assertEquals($expected, $h5p->json_content);
        $this->assertEquals($library2->id, $h5p->main_library_id);
    }

    /**
     * Test the behaviour of clearFilteredParameters().
     */
    public function test_clearFilteredParameters() {
        global $DB;


        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Create 3 libraries.
        $library1 = $generator->create_library_record('Library1', 'Lib1', 1, 1, 2);
        $library2 = $generator->create_library_record('Library2', 'Lib2', 1, 1, 2);
        $library3 = $generator->create_library_record('Library3', 'Lib3', 1, 1, 2);

        // Create h5p content with 'Library1' as a main library.
        $h5pcontentid1 = $generator->create_h5p_record($library1->id);
        // Create h5p content with 'Library1' as a main library.
        $h5pcontentid2 = $generator->create_h5p_record($library1->id);
        // Create h5p content with 'Library2' as a main library.
        $h5pcontentid3 = $generator->create_h5p_record($library2->id);
        // Create h5p content with 'Library3' as a main library.
        $h5pcontentid4 = $generator->create_h5p_record($library3->id);

        $h5pcontent1 = $DB->get_record('hvp', ['id' => $h5pcontentid1]);
        $h5pcontent2 = $DB->get_record('hvp', ['id' => $h5pcontentid2]);
        $h5pcontent3 = $DB->get_record('hvp', ['id' => $h5pcontentid3]);
        $h5pcontent4 = $DB->get_record('hvp', ['id' => $h5pcontentid4]);

        // The filtered parameters should be present in each h5p content.
        $this->assertNotEmpty($h5pcontent1->filtered);
        $this->assertNotEmpty($h5pcontent2->filtered);
        $this->assertNotEmpty($h5pcontent3->filtered);
        $this->assertNotEmpty($h5pcontent4->filtered);

        // Clear the filtered parameters for contents that have library1 and library3 as
        // their main library.
        $this->framework->clearFilteredParameters([$library1->id, $library3->id]);

        $h5pcontent1 = $DB->get_record('hvp', ['id' => $h5pcontentid1]);
        $h5pcontent2 = $DB->get_record('hvp', ['id' => $h5pcontentid2]);
        $h5pcontent3 = $DB->get_record('hvp', ['id' => $h5pcontentid3]);
        $h5pcontent4 = $DB->get_record('hvp', ['id' => $h5pcontentid4]);

        // The filtered parameters should be still present only for the content that has
        // library 2 as a main library.
        $this->assertEmpty($h5pcontent1->filtered);
        $this->assertEmpty($h5pcontent2->filtered);
        $this->assertNotEmpty($h5pcontent3->filtered);
        $this->assertEmpty($h5pcontent4->filtered);
    }

    /**
     * Test the behaviour of getNumContent().
     */
    public function test_getNumContent() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();

        // The 'MainLibrary' library data.
        $mainlibrary = $data->mainlib->data;

        // The 'Library1' library data.
        $library1 = $data->lib1->data;

        // Create new h5p content with MainLibrary as a main library.
        $generator->create_h5p_record($mainlibrary->id);

        // Get the number of h5p contents that are using 'MainLibrary' as their main library.
        $countmainlib = $this->framework->getNumContent($mainlibrary->id);

        // Get the number of h5p contents that are using 'Library1' as their main library.
        $countlib1 = $this->framework->getNumContent($library1->id);

        // Make sure that 2 contents are using MainLibrary as their main library.
        $this->assertEquals(2, $countmainlib);
        // Make sure that 0 contents are using Library1 as their main library.
        $this->assertEquals(0, $countlib1);
    }

    /**
     * Test the behaviour of getNumContent() when certain contents are being skipped.
     */
    public function test_getNumContent_skip_content() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();

        // The 'MainLibrary' library data.
        $mainlibrary = $data->mainlib->data;

        // Create new h5p content with MainLibrary as a main library.
        $h5pcontentid = $generator->create_h5p_record($mainlibrary->id);

        // Get the number of h5p contents that are using 'MainLibrary' as their main library.
        // Skip the newly created content $h5pcontentid.
        $countmainlib = $this->framework->getNumContent($mainlibrary->id, $h5pcontentid);

        // Make sure that 1 content is returned instead of 2 ($h5pcontentid being skipped).
        $this->assertEquals(1, $countmainlib);
    }

    /**
     * Test the behaviour of isContentSlugAvailable().
     */
    public function test_isContentSlugAvailable() {

        $slug = 'h5p-test-slug-1';

        // Currently this returns always true. The slug is generated as a unique value for
        // each h5p content and it is not stored in the h5p content table.
        $isslugavailable = $this->framework->isContentSlugAvailable($slug);

        $this->assertTrue($isslugavailable);
    }

    /**
     * Test that a record is stored for cached assets.
     */
    public function test_saveCachedAssets() {
        global $DB;


        $libraries = array(
            array(
                'machineName' => 'H5P.TestLib',
                'id' => 405,
            ),
            array(
                'FontAwesome' => 'FontAwesome',
                'id' => 406,
            ),
            array(
                'machineName' => 'H5P.SecondLib',
                'id' => 407,
            ),
        );

        $key = 'testhashkey';

        $this->framework->saveCachedAssets($key, $libraries);

        $records = $DB->get_records('hvp_libraries_cachedassets');

        $this->assertCount(3, $records);
    }

    /**
     * Test that the correct libraries are removed from the cached assets table
     */
    public function test_deleteCachedAssets() {
        global $DB;


        $libraries = array(
            array(
                'machineName' => 'H5P.TestLib',
                'id' => 405,
            ),
            array(
                'FontAwesome' => 'FontAwesome',
                'id' => 406,
            ),
            array(
                'machineName' => 'H5P.SecondLib',
                'id' => 407,
            ),
        );

        $key1 = 'testhashkey';
        $this->framework->saveCachedAssets($key1, $libraries);

        $libraries = array(
            array(
                'machineName' => 'H5P.DiffLib',
                'id' => 408,
            ),
            array(
                'FontAwesome' => 'FontAwesome',
                'id' => 406,
            ),
            array(
                'machineName' => 'H5P.ThirdLib',
                'id' => 409,
            ),
        );

        $key2 = 'secondhashkey';
        $this->framework->saveCachedAssets($key2, $libraries);

        $libraries = array(
            array(
                'machineName' => 'H5P.AnotherDiffLib',
                'id' => 410,
            ),
            array(
                'FontAwesome' => 'NotRelated',
                'id' => 411,
            ),
            array(
                'machineName' => 'H5P.ForthLib',
                'id' => 412,
            ),
        );

        $key3 = 'threeforthewin';
        $this->framework->saveCachedAssets($key3, $libraries);

        $records = $DB->get_records('hvp_libraries_cachedassets');
        $this->assertCount(9, $records);

        // Selecting one library id will result in all related library entries also being deleted.
        // Going to use the FontAwesome library id. The first two hashes should be returned.
        $hashes = $this->framework->deleteCachedAssets(406);
        $this->assertCount(2, $hashes);
        $index = array_search($key1, $hashes);
        $this->assertEquals($key1, $hashes[$index]);
        $index = array_search($key2, $hashes);
        $this->assertEquals($key2, $hashes[$index]);
        $index = array_search($key3, $hashes);
        $this->assertFalse($index);

        // Check that the records have been removed as well.
        $records = $DB->get_records('hvp_libraries_cachedassets');
        $this->assertCount(3, $records);
    }

    /**
     * Test the behaviour of getLibraryContentCount().
     */
    public function test_getLibraryContentCount() {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        // Generate h5p related data.
        $data = $generator->generate_h5p_data();

        // The 'MainLibrary' library data.
        $mainlibrary = $data->mainlib->data;

        // The 'Library2' library data.
        $library2 = $data->lib2->data;

        // Create new h5p content with Library2 as it's main library.
        $generator->create_h5p_record($library2->id);

        // Create new h5p content with MainLibrary as it's main library.
        $generator->create_h5p_record($mainlibrary->id);

        $countlibrarycontent = $this->framework->getLibraryContentCount();

        $expected = array(
            "{$mainlibrary->machine_name} {$mainlibrary->major_version}.{$mainlibrary->minor_version}" => 2,
            "{$library2->machine_name} {$library2->major_version}.{$library2->minor_version}" => 1,
        );

        // MainLibrary and Library1 are currently main libraries to the existing h5p contents.
        // Should return the number of cases where MainLibrary and Library1 are main libraries to an h5p content.
        $this->assertEquals($expected, $countlibrarycontent);
    }

    /**
     * Test the behaviour of test_libraryHasUpgrade().
     *
     * @dataProvider test_libraryHasUpgrade_provider
     * @param array $libraryrecords Array containing data for the library creation
     * @param array $testlibrary Array containing the test library data
     * @param bool $expected The expectation whether the library is patched or not
     **/
    public function test_libraryHasUpgrade(array $libraryrecords, array $testlibrary, bool $expected): void {

        $generator = $this->getDataGenerator()->get_plugin_generator('mod_hvp');

        foreach ($libraryrecords as $library) {
            call_user_func_array([$generator, 'create_library_record'], $library);
        }

        $this->assertEquals($expected, $this->framework->libraryHasUpgrade($testlibrary));
    }

    /**
     * Data provider for test_libraryHasUpgrade().
     *
     * @return array
     */
    public static function test_libraryHasUpgrade_provider(): array {
        return [
            'Lower major version; Identical lower version' => [
                [
                    ['Library', 'Lib', 2, 2],
                ],
                [
                    'machineName' => 'Library',
                    'majorVersion' => 1,
                    'minorVersion' => 2
                ],
                true,
            ],
            'Major version identical; Lower minor version' => [
                [
                    ['Library', 'Lib', 2, 2],
                ],
                [
                    'machineName' => 'Library',
                    'majorVersion' => 2,
                    'minorVersion' => 1
                ],
                true,
            ],
            'Major version identical; Minor version identical' => [
                [
                    ['Library', 'Lib', 2, 2],
                ],
                [
                    'machineName' => 'Library',
                    'majorVersion' => 2,
                    'minorVersion' => 2
                ],
                false,
            ],
            'Major version higher; Minor version identical' => [
                [
                    ['Library', 'Lib', 2, 2],
                ],
                [
                    'machineName' => 'Library',
                    'majorVersion' => 3,
                    'minorVersion' => 2
                ],
                false,
            ],
            'Major version identical; Minor version newer' => [
                [
                    ['Library', 'Lib', 2, 2],
                ],
                [
                    'machineName' => 'Library',
                    'majorVersion' => 2,
                    'minorVersion' => 4
                ],
                false,
            ]
        ];
    }

    protected function create_fake_stored_file_from_path(string $filepath, int $userid = 0,
        context $context = null): \stored_file {
        if (is_null($context)) {
            $context = context_system::instance();
        }
        $filerecord = [
            'contextid' => $context->id,
            'component' => 'mod_hvp',
            'filearea'  => 'unittest',
            'itemid'    => rand(),
            'filepath'  => '/',
            'filename'  => basename($filepath),
        ];
        if (!is_null($userid)) {
            $filerecord['userid'] = $userid;
        }

        $fs = get_file_storage();
        return $fs->create_file_from_pathname($filerecord, $filepath);
    }

}
