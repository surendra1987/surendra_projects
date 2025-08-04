<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Database module external functions tests
 *
 * @package    mod_data
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.9
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Database module external functions tests
 *
 * @package    mod_data
 * @category   external
 * @copyright  2015 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.9
 */
class mod_data_externallib_test extends externallib_advanced_testcase {

    /**
     * Generate a basic database activity setup for the purposes of testing in this testcase.
     *
     * @return stdClass
     */
    private static function get_basic_setup(): \stdClass {
        global $DB;
        self::setAdminUser();

        $generator = self::getDataGenerator();
        $return = new \stdClass();

        // Setup test data.
        $course = new stdClass();
        $course->groupmode = SEPARATEGROUPS;
        $course->groupmodeforce = true;
        $return->course = $generator->create_course($course);
        $return->database = $generator->create_module('data', ['course' => $return->course->id]);
        $return->context = context_module::instance($return->database->cmid);
        $return->cm = get_coursemodule_from_instance('data', $return->database->id);

        // Create users.
        $return->student1 = $generator->create_user(['firstname' => 'Olivia', 'lastname' => 'Smith']);
        $return->student2 = $generator->create_user(['firstname' => 'Ezra', 'lastname' => 'Johnson']);
        $return->student3 = $generator->create_user(['firstname' => 'Amelia', 'lastname' => 'Williams']);
        $return->teacher = $generator->create_user(['firstname' => 'Asher', 'lastname' => 'Jones']);

        // Users enrolments.
        $return->studentrole = $DB->get_record('role', ['shortname' => 'student']);
        $return->teacherrole = $DB->get_record('role', ['shortname' => 'editingteacher']);
        $generator->enrol_user($return->student1->id, $return->course->id, $return->studentrole->id, 'manual');
        $generator->enrol_user($return->student2->id, $return->course->id, $return->studentrole->id, 'manual');
        $generator->enrol_user($return->student3->id, $return->course->id, $return->studentrole->id, 'manual');
        $generator->enrol_user($return->teacher->id, $return->course->id, $return->teacherrole->id, 'manual');

        $return->group1 = $generator->create_group(['courseid' => $return->course->id]);
        $return->group2 = $generator->create_group(['courseid' => $return->course->id]);
        groups_add_member($return->group1, $return->student1);
        groups_add_member($return->group1, $return->student2);
        groups_add_member($return->group2, $return->student3);

        return $return;
    }

    /**
     * Test get databases by courses
     */
    public function test_mod_data_get_databases_by_courses() {
        global $DB;

        $generator = self::getDataGenerator();

        // Create users.
        $student = $generator->create_user();
        $teacher = $generator->create_user();

        // Set to the student user.
        self::setUser($student);

        // Create courses to add the modules.
        $course1 = $generator->create_course();
        $course2 = $generator->create_course();

        // First database.
        $record = new stdClass();
        $record->introformat = FORMAT_HTML;
        $record->course = $course1->id;
        $database1 = $generator->create_module('data', $record);

        // Second database.
        $record = new stdClass();
        $record->introformat = FORMAT_HTML;
        $record->course = $course2->id;
        $database2 = $generator->create_module('data', $record);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));

        // Users enrolments.
        $generator->enrol_user($student->id, $course1->id, $studentrole->id, 'manual');
        $generator->enrol_user($teacher->id, $course1->id, $teacherrole->id, 'manual');

        // Execute real Moodle enrolment as we'll call unenrol() method on the instance later.
        $enrol = enrol_get_plugin('manual');
        $enrolinstances = enrol_get_instances($course2->id, true);
        foreach ($enrolinstances as $courseenrolinstance) {
            if ($courseenrolinstance->enrol == "manual") {
                $instance2 = $courseenrolinstance;
                break;
            }
        }
        $enrol->enrol_user($instance2, $student->id, $studentrole->id);

        // Create what we expect to be returned when querying the two courses.
        // First for the student user.
        $expectedfields = array('id', 'coursemodule', 'course', 'name', 'comments', 'timeavailablefrom',
                            'timeavailableto', 'timeviewfrom', 'timeviewto', 'requiredentries', 'requiredentriestoview',
                            'intro', 'introformat', 'introfiles', 'maxentries', 'rssarticles', 'singletemplate', 'listtemplate',
                            'listtemplateheader', 'listtemplatefooter', 'addtemplate', 'rsstemplate', 'rsstitletemplate',
                            'csstemplate', 'jstemplate', 'asearchtemplate', 'approval', 'defaultsort', 'defaultsortdir', 'manageapproved');

        // Add expected coursemodule.
        $database1->coursemodule = $database1->cmid;
        $database1->introfiles = [];
        $database2->coursemodule = $database2->cmid;
        $database2->introfiles = [];

        $expected1 = array();
        $expected2 = array();
        foreach ($expectedfields as $field) {
            if ($field == 'approval' or $field == 'manageapproved') {
                $database1->{$field} = (bool) $database1->{$field};
                $database2->{$field} = (bool) $database2->{$field};
            }
            $expected1[$field] = $database1->{$field};
            $expected2[$field] = $database2->{$field};
        }
        $expected1['comments'] = (bool) $expected1['comments'];
        $expected2['comments'] = (bool) $expected2['comments'];

        $expecteddatabases = array();
        $expecteddatabases[] = $expected2;
        $expecteddatabases[] = $expected1;

        // Call the external function passing course ids.
        $result = mod_data_external::get_databases_by_courses(array($course2->id, $course1->id));
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        self::assertEquals($expecteddatabases, $result['databases']);

        // Call the external function without passing course id.
        $result = mod_data_external::get_databases_by_courses();
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        self::assertEquals($expecteddatabases, $result['databases']);

        // Unenrol user from second course and alter expected databases.
        $enrol->unenrol_user($instance2, $student->id);
        array_shift($expecteddatabases);

        // Call the external function without passing course id.
        $result = mod_data_external::get_databases_by_courses();
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        self::assertEquals($expecteddatabases, $result['databases']);

        // Call for the second course we unenrolled the user from, expected warning.
        $result = mod_data_external::get_databases_by_courses(array($course2->id));
        self::assertCount(1, $result['warnings']);
        self::assertEquals('1', $result['warnings'][0]['warningcode']);
        self::assertEquals($course2->id, $result['warnings'][0]['itemid']);

        // Now, try as a teacher for getting all the additional fields.
        self::setUser($teacher);

        $additionalfields = array('scale', 'assessed', 'assesstimestart', 'assesstimefinish', 'editany', 'notification', 'timemodified');

        foreach ($additionalfields as $field) {
            if ($field == 'editany') {
                $database1->{$field} = (bool) $database1->{$field};
            }
            $expecteddatabases[0][$field] = $database1->{$field};
        }
        $result = mod_data_external::get_databases_by_courses();
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        self::assertEquals($expecteddatabases, $result['databases']);

        // Admin should get all the information.
        self::setAdminUser();

        $result = mod_data_external::get_databases_by_courses(array($course1->id));
        $result = external_api::clean_returnvalue(mod_data_external::get_databases_by_courses_returns(), $result);
        self::assertEquals($expecteddatabases, $result['databases']);
    }

    /**
     * Test view_database invalid id.
     */
    public function test_view_database_invalid_id() {

        // Test invalid instance id.
        self::expectException('moodle_exception');
        mod_data_external::view_database(0);
    }

    /**
     * Test view_database not enrolled user.
     */
    public function test_view_database_not_enrolled_user() {
        $generator = self::getDataGenerator();
        $usernotenrolled = $generator->create_user();
        self::setUser($usernotenrolled);

        self::expectException('moodle_exception');
        mod_data_external::view_database(0);
    }

    /**
     * Test view_database no capabilities.
     */
    public function test_view_database_no_capabilities() {
        $data = self::get_basic_setup();
        // Test user with no capabilities.
        // We need a explicit prohibit since this capability is allowed for students by default.
        assign_capability('mod/data:view', CAP_PROHIBIT, $data->studentrole->id, $data->context->id);
        accesslib_clear_all_caches_for_unit_testing();
        self::setUser($data->student1);

        self::expectException('moodle_exception');
        self::expectExceptionMessage('Course or activity not accessible. (Activity is hidden)');
        mod_data_external::view_database($data->database->id);
    }

    /**
     * Test view_database.
     */
    public function test_view_database() {

        $data = self::get_basic_setup();

        // Test user with full capabilities.
        self::setUser($data->student1);

        // Trigger and capture the event.
        $sink = self::redirectEvents();

        $result = mod_data_external::view_database($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::view_database_returns(), $result);

        $events = $sink->get_events();
        self::assertCount(1, $events);
        $event = array_shift($events);

        // Checking that the event contains the expected values.
        self::assertInstanceOf('\mod_data\event\course_module_viewed', $event);
        self::assertEquals($data->context, $event->get_context());
        $moodledata = new \moodle_url('/mod/data/view.php', array('id' => $data->cm->id));
        self::assertEquals($moodledata, $event->get_url());
        self::assertEventContextNotUsed($event);
        self::assertNotEmpty($event->get_name());
    }

    /**
     * Test get_data_access_information for student.
     */
    public function test_get_data_access_information_student() {
        global $DB;

        $data = self::get_basic_setup();
        // Modify the database to add access restrictions.
        $data->database->timeavailablefrom = time() + DAYSECS;
        $data->database->requiredentries = 2;
        $data->database->requiredentriestoview = 2;
        $DB->update_record('data', $data->database);

        // Test user with full capabilities.
        self::setUser($data->student1);

        $result = mod_data_external::get_data_access_information($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_data_access_information_returns(), $result);

        self::assertEquals($data->group1->id, $result['groupid']);

        self::assertFalse($result['canmanageentries']);
        self::assertFalse($result['canapprove']);
        self::assertTrue($result['canaddentry']);  // It return true because it doen't check time restrictions.
        self::assertFalse($result['timeavailable']);
        self::assertFalse($result['inreadonlyperiod']);
        self::assertEquals(0, $result['numentries']);
        self::assertEquals($data->database->requiredentries, $result['entrieslefttoadd']);
        self::assertEquals($data->database->requiredentriestoview, $result['entrieslefttoview']);
    }

    /**
     * Test get_data_access_information for teacher.
     */
    public function test_get_data_access_information_teacher() {
        global $DB;
        $data = self::get_basic_setup();
        
        // Modify the database to add access restrictions.
        $data->database->timeavailablefrom = time() + DAYSECS;
        $data->database->requiredentries = 2;
        $data->database->requiredentriestoview = 2;
        $DB->update_record('data', $data->database);

        // Test user with full capabilities.
        self::setUser($data->teacher);

        $result = mod_data_external::get_data_access_information($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_data_access_information_returns(), $result);

        self::assertEquals(0, $result['groupid']);

        self::assertTrue($result['canmanageentries']);
        self::assertTrue($result['canapprove']);
        self::assertTrue($result['canaddentry']);  // It return true because it doen't check time restrictions.
        self::assertTrue($result['timeavailable']);
        self::assertFalse($result['inreadonlyperiod']);
        self::assertEquals(0, $result['numentries']);
        self::assertEquals(0, $result['entrieslefttoadd']);
        self::assertEquals(0, $result['entrieslefttoview']);
    }

    /**
     * Test get_data_access_information with groups.
     */
    public function test_get_data_access_information_groups() {
        global $DB;

        $data = self::get_basic_setup();

        $DB->set_field('course', 'groupmode', VISIBLEGROUPS, ['id' => $data->course->id]);

        // Check I can see my group.
        self::setUser($data->student1);

        $result = mod_data_external::get_data_access_information($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_data_access_information_returns(), $result);

        self::assertEquals($data->group1->id, $result['groupid']); // My group is correctly found.
        self::assertFalse($result['canmanageentries']);
        self::assertFalse($result['canapprove']);
        self::assertTrue($result['canaddentry']);  // I can entries in my groups.
        self::assertTrue($result['timeavailable']);
        self::assertFalse($result['inreadonlyperiod']);
        self::assertEquals(0, $result['numentries']);
        self::assertEquals(0, $result['entrieslefttoadd']);
        self::assertEquals(0, $result['entrieslefttoview']);

        // Check the other course group in visible groups mode.
        $result = mod_data_external::get_data_access_information($data->database->id, $data->group2->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_data_access_information_returns(), $result);

        self::assertEquals($data->group2->id, $result['groupid']); // The group is correctly found.
        self::assertFalse($result['canmanageentries']);
        self::assertFalse($result['canapprove']);
        self::assertFalse($result['canaddentry']);  // I cannot add entries in other groups.
        self::assertTrue($result['timeavailable']);
        self::assertFalse($result['inreadonlyperiod']);
        self::assertEquals(0, $result['numentries']);
        self::assertEquals(0, $result['entrieslefttoadd']);
        self::assertEquals(0, $result['entrieslefttoview']);
    }

    /**
     * Helper method to populate the database with some entries.
     *
     * @param \stdClass $basic_data The object returned from self::get_basic_setup()
     * @return array the entry ids created
     */
    private static function populate_database_with_entries(\stdClass $basic_data) {
        global $DB;
        $generator = self::getDataGenerator();

        // Force approval.
        $DB->set_field('data', 'approval', 1, array('id' => $basic_data->database->id));
        $generator = $generator->get_plugin_generator('mod_data');
        $fieldtypes = [
            'checkbox' => ['opt1', 'opt2', 'opt3', 'opt4'],
            'date' => '01-01-2037',
            'menu' => 'menu1',
            'multimenu' => ['multimenu1', 'multimenu2', 'multimenu3', 'multimenu4'],
            'number' => '12345',
            'radiobutton' =>  'radioopt1',
            'text' => 'text for testing',
            'textarea' => '<p>text area testing<br /></p>',
            'url' => ['example.url', 'sampleurl']
        ];

        $count = 1;
        // Creating test Fields with default parameter values.
        foreach (array_keys($fieldtypes) as $fieldtype) {
            $fieldname = 'field-' . $count;
            $record = new StdClass();
            $record->name = $fieldname;
            $record->type = $fieldtype;
            $record->required = 1;

            $generator->create_field($record, $basic_data->database);
            $count++;
        }
        // Get all the fields created.
        $fields = $DB->get_records('data_fields', array('dataid' => $basic_data->database->id), 'id');

        // Populate with contents, creating a new entry.
        $fieldcontents = array();
        foreach ($fields as $fieldrecord) {
            $fieldcontents[$fieldrecord->id] = $fieldtypes[$fieldrecord->type];
        }

        self::setUser($basic_data->student1);
        $entry11 = $generator->create_entry($basic_data->database, $fieldcontents, $basic_data->group1->id);
        self::setUser($basic_data->student2);
        $entry12 = $generator->create_entry($basic_data->database, $fieldcontents, $basic_data->group1->id);
        $entry13 = $generator->create_entry($basic_data->database, $fieldcontents, $basic_data->group1->id);
        // Entry not in group.
        $entry14 = $generator->create_entry($basic_data->database, $fieldcontents, 0);

        self::setUser($basic_data->student3);
        $entry21 = $generator->create_entry($basic_data->database, $fieldcontents, $basic_data->group2->id);

        // Approve all except $entry13.
        $DB->set_field('data_records', 'approved', 1, ['id' => $entry11]);
        $DB->set_field('data_records', 'approved', 1, ['id' => $entry12]);
        $DB->set_field('data_records', 'approved', 1, ['id' => $entry14]);
        $DB->set_field('data_records', 'approved', 1, ['id' => $entry21]);

        return [$entry11, $entry12, $entry13, $entry14, $entry21];
    }

    /**
     * Test get_entries
     */
    public function test_get_entries() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        // First of all, expect to see only my group entries (not other users in other groups ones).
        // We may expect entries without group also.
        self::setUser($data->student1);
        $result = mod_data_external::get_entries($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(3, $result['entries']);
        self::assertEquals(3, $result['totalcount']);
        self::assertEquals($entry11, $result['entries'][0]['id']);
        self::assertEquals($data->student1->id, $result['entries'][0]['userid']);
        self::assertEquals($data->group1->id, $result['entries'][0]['groupid']);
        self::assertEquals($data->database->id, $result['entries'][0]['dataid']);
        self::assertEquals($entry12, $result['entries'][1]['id']);
        self::assertEquals($data->student2->id, $result['entries'][1]['userid']);
        self::assertEquals($data->group1->id, $result['entries'][1]['groupid']);
        self::assertEquals($data->database->id, $result['entries'][1]['dataid']);
        self::assertEquals($entry14, $result['entries'][2]['id']);
        self::assertEquals($data->student2->id, $result['entries'][2]['userid']);
        self::assertEquals(0, $result['entries'][2]['groupid']);
        self::assertEquals($data->database->id, $result['entries'][2]['dataid']);
        
        // Other user in same group.
        self::setUser($data->student2);
        $result = mod_data_external::get_entries($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(4, $result['entries']);  // I can see my entry not approved yet.
        self::assertEquals(4, $result['totalcount']);

        // Now try with the user in the second group that must see only two entries (his group entry and the one without group).
        self::setUser($data->student3);
        $result = mod_data_external::get_entries($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(2, $result['entries']);
        self::assertEquals(2, $result['totalcount']);
        self::assertEquals($entry14, $result['entries'][0]['id']);
        self::assertEquals($data->student2->id, $result['entries'][0]['userid']);
        self::assertEquals(0, $result['entries'][0]['groupid']);
        self::assertEquals($data->database->id, $result['entries'][0]['dataid']);
        self::assertEquals($entry21, $result['entries'][1]['id']);
        self::assertEquals($data->student3->id, $result['entries'][1]['userid']);
        self::assertEquals($data->group2->id, $result['entries'][1]['groupid']);
        self::assertEquals($data->database->id, $result['entries'][1]['dataid']);

        // Now, as teacher we should see all (we have permissions to view all groups).
        self::setUser($data->teacher);
        $result = mod_data_external::get_entries($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(5, $result['entries']);  // I can see the not approved one.
        self::assertEquals(5, $result['totalcount']);

        $entries = $DB->get_records('data_records', array('dataid' => $data->database->id), 'id');
        self::assertCount(5, $entries);
        $count = 0;
        foreach ($entries as $entry) {
            self::assertEquals($entry->id, $result['entries'][$count]['id']);
            $count++;
        }

        // Basic test passing the parameter (instead having to calculate it).
        self::setUser($data->student1);
        $result = mod_data_external::get_entries($data->database->id, $data->group1->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(3, $result['entries']);
        self::assertEquals(3, $result['totalcount']);

        // Test ordering (reverse).
        self::setUser($data->student1);
        $result = mod_data_external::get_entries($data->database->id, $data->group1->id, false, null, 'DESC');
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(3, $result['entries']);
        self::assertEquals(3, $result['totalcount']);
        self::assertEquals($entry14, $result['entries'][0]['id']);

        // Test pagination.
        self::setUser($data->student1);
        $result = mod_data_external::get_entries($data->database->id, $data->group1->id, false, null, null, 0, 1);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(1, $result['entries']);
        self::assertEquals(3, $result['totalcount']);
        self::assertEquals($entry11, $result['entries'][0]['id']);

        $result = mod_data_external::get_entries($data->database->id, $data->group1->id, false, null, null, 1, 1);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(1, $result['entries']);
        self::assertEquals(3, $result['totalcount']);
        self::assertEquals($entry12, $result['entries'][0]['id']);

        // Now test the return contents.
        data_generate_default_template($data->database, 'listtemplate', 0, false, true); // Generate a default list template.
        $result = mod_data_external::get_entries($data->database->id, $data->group1->id, true, null, null, 0, 2);
        $result = external_api::clean_returnvalue(mod_data_external::get_entries_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(2, $result['entries']);
        self::assertEquals(3, $result['totalcount']);
        self::assertCount(9, $result['entries'][0]['contents']);
        self::assertCount(9, $result['entries'][1]['contents']);
        // Search for some content.
        self::assertStringContainsString('opt1', $result['listviewcontents']);
        self::assertStringContainsString('January', $result['listviewcontents']);
        self::assertStringContainsString('menu1', $result['listviewcontents']);
        self::assertStringContainsString('text for testing', $result['listviewcontents']);
        self::assertStringContainsString('sampleurl', $result['listviewcontents']);
    }

    /**
     * Test get_entry_visible_groups.
     */
    public function test_get_entry_visible_groups() {
        global $DB;

        $data = self::get_basic_setup();
        $DB->set_field('course', 'groupmode', VISIBLEGROUPS, ['id' => $data->course->id]);
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        // Check I can see my approved group entries.
        self::setUser($data->student1);
        $result = mod_data_external::get_entry($entry11);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertEquals($entry11, $result['entry']['id']);
        self::assertTrue($result['entry']['approved']);
        self::assertTrue($result['entry']['canmanageentry']); // Is mine, I can manage it.

        // Entry from other group.
        $result = mod_data_external::get_entry($entry21);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertEquals($entry21, $result['entry']['id']);
    }

    /**
     * Test get_entry_separated_groups.
     */
    public function test_get_entry_separated_groups() {
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        // Check I can see my approved group entries.
        self::setUser($data->student1);
        $result = mod_data_external::get_entry($entry11);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertEquals($entry11, $result['entry']['id']);
        self::assertTrue($result['entry']['approved']);
        self::assertTrue($result['entry']['canmanageentry']); // Is mine, I can manage it.

        // Retrieve contents.
        data_generate_default_template($data->database, 'singletemplate', 0, false, true);
        $result = mod_data_external::get_entry($entry11, true);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertCount(9, $result['entry']['contents']);
        self::assertStringContainsString('opt1', $result['entryviewcontents']);
        self::assertStringContainsString('January', $result['entryviewcontents']);
        self::assertStringContainsString('menu1', $result['entryviewcontents']);
        self::assertStringContainsString('text for testing', $result['entryviewcontents']);
        self::assertStringContainsString('sampleurl', $result['entryviewcontents']);

        // This is in my group but I'm not the author.
        $result = mod_data_external::get_entry($entry12);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertEquals($entry12, $result['entry']['id']);
        self::assertTrue($result['entry']['approved']);
        self::assertFalse($result['entry']['canmanageentry']); // Not mine.

        self::setUser($data->student3);
        $result = mod_data_external::get_entry($entry21);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertCount(0, $result['warnings']);
        self::assertEquals($entry21, $result['entry']['id']);
        self::assertTrue($result['entry']['approved']);
        self::assertTrue($result['entry']['canmanageentry']); // Is mine, I can manage it.

        // As teacher I should be able to see all the entries.
        self::setUser($data->teacher);
        $result = mod_data_external::get_entry($entry11);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertEquals($entry11, $result['entry']['id']);

        $result = mod_data_external::get_entry($entry12);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertEquals($entry12, $result['entry']['id']);
        // This is the not approved one.
        $result = mod_data_external::get_entry($entry13);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertEquals($entry13, $result['entry']['id']);

        $result = mod_data_external::get_entry($entry21);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertEquals($entry21, $result['entry']['id']);

        // Now, try to get an entry not approved yet.
        self::setUser($data->student1);
        self::expectException('moodle_exception');
        $result = mod_data_external::get_entry($entry13);
    }

    /**
     * Test get_entry from other group in separated groups.
     */
    public function test_get_entry_other_group_separated_groups() {
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        // We should not be able to view other gropu entries (in separated groups).
        self::setUser($data->student1);
        self::expectException('moodle_exception');
        $result = mod_data_external::get_entry($entry21);
    }

    /**
     * Test get_fields.
     */
    public function test_get_fields() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        $result = mod_data_external::get_fields($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_fields_returns(), $result);

        // Basically compare we retrieve all the fields and the correct values.
        $fields = $DB->get_records('data_fields', array('dataid' => $data->database->id), 'id');
        foreach ($result['fields'] as $field) {
            self::assertEquals($field, (array) $fields[$field['id']]);
        }
    }

    /**
     * Test get_fields_database_without_fields.
     */
    public function test_get_fields_database_without_fields() {

        $data = self::get_basic_setup();
        self::setUser($data->student1);
        $result = mod_data_external::get_fields($data->database->id);
        $result = external_api::clean_returnvalue(mod_data_external::get_fields_returns(), $result);

        self::assertEmpty($result['fields']);
    }

    /**
     * Test search_entries.
     */
    public function test_search_entries() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        // Empty search, it should return all the visible entries.
        $result = mod_data_external::search_entries($data->database->id, 0, false);
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(3, $result['entries']);
        self::assertEquals(3, $result['totalcount']);

        // Search for something that does not exists.
        $result = mod_data_external::search_entries($data->database->id, 0, false, 'abc');
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(0, $result['entries']);
        self::assertEquals(0, $result['totalcount']);

        // Search by text matching all the entries.
        $result = mod_data_external::search_entries($data->database->id, 0, false, 'text');
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(3, $result['entries']);
        self::assertEquals(3, $result['totalcount']);

        // Now as the other student I should receive my not approved entry. Apply ordering here.
        self::setUser($data->student2);
        $result = mod_data_external::search_entries($data->database->id, 0, false, 'text', [], DATA_APPROVED, 'ASC');
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(4, $result['entries']);
        self::assertEquals(4, $result['totalcount']);
        // The not approved one should be the first.
        self::assertEquals($entry13, $result['entries'][0]['id']);

        // Now as the other group student.
        self::setUser($data->student3);
        $result = mod_data_external::search_entries($data->database->id, 0, false, 'text');
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(2, $result['entries']);
        self::assertEquals(2, $result['totalcount']);
        self::assertEquals($data->student2->id, $result['entries'][0]['userid']);
        self::assertEquals($data->student3->id, $result['entries'][1]['userid']);

        // Same normal text search as teacher.
        self::setUser($data->teacher);
        $result = mod_data_external::search_entries($data->database->id, 0, false, 'text');
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(5, $result['entries']);  // I can see all groups and non approved.
        self::assertEquals(5, $result['totalcount']);

        // Pagination.
        self::setUser($data->teacher);
        $result = mod_data_external::search_entries($data->database->id, 0, false, 'text', [], DATA_TIMEADDED, 'ASC', 0, 2);
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(2, $result['entries']);  // Only 2 per page.
        self::assertEquals(5, $result['totalcount']);

        // Now advanced search or not dinamic fields (user firstname for example).
        self::setUser($data->student1);
        $advsearch = [
            ['name' => 'fn', 'value' => json_encode($data->student2->firstname)]
        ];
        $result = mod_data_external::search_entries($data->database->id, 0, false, '', $advsearch);
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(2, $result['entries']);
        self::assertEquals(2, $result['totalcount']);
        self::assertEquals($data->student2->id, $result['entries'][0]['userid']);  // I only found mine!

        // Advanced search for fields.
        $field = $DB->get_record('data_fields', array('type' => 'url'));
        $advsearch = [
            ['name' => 'f_' . $field->id , 'value' => 'sampleurl']
        ];
        $result = mod_data_external::search_entries($data->database->id, 0, false, '', $advsearch);
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(3, $result['entries']);  // Found two entries matching this.
        self::assertEquals(3, $result['totalcount']);

        // Combined search.
        $field2 = $DB->get_record('data_fields', array('type' => 'number'));
        $advsearch = [
            ['name' => 'f_' . $field->id , 'value' => 'sampleurl'],
            ['name' => 'f_' . $field2->id , 'value' => '12345'],
            ['name' => 'ln', 'value' => json_encode($data->student2->lastname)]
        ];
        $result = mod_data_external::search_entries($data->database->id, 0, false, '', $advsearch);
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(2, $result['entries']);  // Only one matching everything.
        self::assertEquals(2, $result['totalcount']);

        // Combined search (no results).
        $field2 = $DB->get_record('data_fields', array('type' => 'number'));
        $advsearch = [
            ['name' => 'f_' . $field->id , 'value' => 'sampleurl'],
            ['name' => 'f_' . $field2->id , 'value' => '98780333'], // Non existent number.
        ];
        $result = mod_data_external::search_entries($data->database->id, 0, false, '', $advsearch);
        $result = external_api::clean_returnvalue(mod_data_external::search_entries_returns(), $result);
        self::assertCount(0, $result['entries']);  // Only one matching everything.
        self::assertEquals(0, $result['totalcount']);
    }

    /**
     * Test approve_entry.
     */
    public function test_approve_entry() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->teacher);
        self::assertEquals(0, $DB->get_field('data_records', 'approved', array('id' => $entry13)));
        $result = mod_data_external::approve_entry($entry13);
        $result = external_api::clean_returnvalue(mod_data_external::approve_entry_returns(), $result);
        self::assertEquals(1, $DB->get_field('data_records', 'approved', array('id' => $entry13)));
    }

    /**
     * Test unapprove_entry.
     */
    public function test_unapprove_entry() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->teacher);
        self::assertEquals(1, $DB->get_field('data_records', 'approved', array('id' => $entry11)));
        $result = mod_data_external::approve_entry($entry11, false);
        $result = external_api::clean_returnvalue(mod_data_external::approve_entry_returns(), $result);
        self::assertEquals(0, $DB->get_field('data_records', 'approved', array('id' => $entry11)));
    }

    /**
     * Test approve_entry missing permissions.
     */
    public function test_approve_entry_missing_permissions() {
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        self::expectException('moodle_exception');
        mod_data_external::approve_entry($entry13);
    }

    /**
     * Test delete_entry as teacher. Check I can delete any entry.
     */
    public function test_delete_entry_as_teacher() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->teacher);
        $result = mod_data_external::delete_entry($entry11);
        $result = external_api::clean_returnvalue(mod_data_external::delete_entry_returns(), $result);
        self::assertEquals(0, $DB->count_records('data_records', array('id' => $entry11)));

        // Entry in other group.
        $result = mod_data_external::delete_entry($entry21);
        $result = external_api::clean_returnvalue(mod_data_external::delete_entry_returns(), $result);
        self::assertEquals(0, $DB->count_records('data_records', array('id' => $entry21)));
    }

    /**
     * Test delete_entry as student. Check I can delete my own entries.
     */
    public function test_delete_entry_as_student() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        $result = mod_data_external::delete_entry($entry11);
        $result = external_api::clean_returnvalue(mod_data_external::delete_entry_returns(), $result);
        self::assertEquals(0, $DB->count_records('data_records', array('id' => $entry11)));
    }

    /**
     * Test delete_entry as student in read only mode period. Check I cannot delete my own entries in that period.
     */
    public function test_delete_entry_as_student_in_read_only_period() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);
        // Set a time period.
        $data->database->timeviewfrom = time() - HOURSECS;
        $data->database->timeviewto = time() + HOURSECS;
        $DB->update_record('data', $data->database);

        self::setUser($data->student1);
        self::expectException('moodle_exception');
        mod_data_external::delete_entry($entry11);
    }

    /**
     * Test delete_entry with an user missing permissions.
     */
    public function test_delete_entry_missing_permissions() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        self::expectException('moodle_exception');
        mod_data_external::delete_entry($entry21);
    }

    /**
     * Test add_entry invalid group.
     */
    public function test_add_entry_invalid_group() {
        $data = self::get_basic_setup();
        self::setUser($data->student1);
        self::expectExceptionMessage(get_string('noaccess', 'data'));
        self::expectException('moodle_exception');
        mod_data_external::add_entry($data->database->id, $data->group2->id, []);
    }

    /**
     * Test add_entry.
     */
    public function test_add_entry() {
        global $DB;
        $data = self::get_basic_setup();
        // First create the record structure and add some entries.
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        $newentrydata = [];
        $fields = $DB->get_records('data_fields', array('dataid' => $data->database->id), 'id');
        // Prepare the new entry data.
        foreach ($fields as $field) {
            $subfield = $value = '';

            switch ($field->type) {
                case 'checkbox':
                    $value = ['opt1', 'opt2'];
                    break;
                case 'date':
                    // Add two extra.
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'day',
                        'value' => json_encode('5')
                    ];
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'month',
                        'value' => json_encode('1')
                    ];
                    $subfield = 'year';
                    $value = '1981';
                    break;
                case 'menu':
                    $value = 'menu1';
                    break;
                case 'multimenu':
                    $value = ['multimenu1', 'multimenu4'];
                    break;
                case 'number':
                    $value = 6;
                    break;
                case 'radiobutton':
                    $value = 'radioopt1';
                    break;
                case 'text':
                    $value = 'some text';
                    break;
                case 'textarea':
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'content1',
                        'value' => json_encode(FORMAT_MOODLE)
                    ];
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'itemid',
                        'value' => json_encode(0)
                    ];
                    $value = 'more text';
                    break;
                case 'url':
                    $value = 'https://moodle.org';
                    $subfield = 0;
                    break;
            }

            $newentrydata[] = [
                'fieldid' => $field->id,
                'subfield' => $subfield,
                'value' => json_encode($value)
            ];
        }
        $result = mod_data_external::add_entry($data->database->id, 0, $newentrydata);
        $result = external_api::clean_returnvalue(mod_data_external::add_entry_returns(), $result);

        $newentryid = $result['newentryid'];
        $result = mod_data_external::get_entry($newentryid, true);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertEquals($data->student1->id, $result['entry']['userid']);
        self::assertCount(9, $result['entry']['contents']);
        foreach ($result['entry']['contents'] as $content) {
            $field = $fields[$content['fieldid']];
            // Stored content same that the one retrieved by WS.
            $dbcontent = $DB->get_record('data_content', array('fieldid' => $field->id, 'recordid' => $newentryid));
            self::assertEquals($dbcontent->content, $content['content']);

            // Now double check everything stored is correct.
            if ($field->type == 'checkbox') {
                self::assertEquals('opt1##opt2', $content['content']);
                continue;
            }
            if ($field->type == 'date') {
                self::assertEquals(347500800, $content['content']); // Date in gregorian format.
                continue;
            }
            if ($field->type == 'menu') {
                self::assertEquals('menu1', $content['content']);
                continue;
            }
            if ($field->type == 'multimenu') {
                self::assertEquals('multimenu1##multimenu4', $content['content']);
                continue;
            }
            if ($field->type == 'number') {
                self::assertEquals(6, $content['content']);
                continue;
            }
            if ($field->type == 'radiobutton') {
                self::assertEquals('radioopt1', $content['content']);
                continue;
            }
            if ($field->type == 'text') {
                self::assertEquals('some text', $content['content']);
                continue;
            }
            if ($field->type == 'textarea') {
                self::assertEquals('more text', $content['content']);
                self::assertEquals(FORMAT_MOODLE, $content['content1']);
                continue;
            }
            if ($field->type == 'url') {
                self::assertEquals('https://moodle.org', $content['content']);
                continue;
            }
            self::assertEquals('multimenu1##multimenu4', $content['content']);
        }

        // Now, try to add another entry but removing some required data.
        unset($newentrydata[0]);
        $result = mod_data_external::add_entry($data->database->id, 0, $newentrydata);
        $result = external_api::clean_returnvalue(mod_data_external::add_entry_returns(), $result);
        self::assertEquals(0, $result['newentryid']);
        self::assertCount(0, $result['generalnotifications']);
        self::assertCount(1, $result['fieldnotifications']);
        self::assertEquals('field-1', $result['fieldnotifications'][0]['fieldname']);
        self::assertEquals(get_string('errormustsupplyvalue', 'data'), $result['fieldnotifications'][0]['notification']);
    }

    /**
     * Test add_entry empty_form.
     */
    public function test_add_entry_empty_form() {
        $data = self::get_basic_setup();
        $result = mod_data_external::add_entry($data->database->id, 0, []);
        $result = external_api::clean_returnvalue(mod_data_external::add_entry_returns(), $result);
        self::assertEquals(0, $result['newentryid']);
        self::assertCount(1, $result['generalnotifications']);
        self::assertCount(0, $result['fieldnotifications']);
        self::assertEquals(get_string('emptyaddform', 'data'), $result['generalnotifications'][0]);
    }

    /**
     * Test add_entry read_only_period.
     */
    public function test_add_entry_read_only_period() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);
        // Set a time period.
        $data->database->timeviewfrom = time() - HOURSECS;
        $data->database->timeviewto = time() + HOURSECS;
        $DB->update_record('data', $data->database);

        self::setUser($data->student1);
        self::expectExceptionMessage(get_string('noaccess', 'data'));
        self::expectException('moodle_exception');
        mod_data_external::add_entry($data->database->id, 0, []);
    }

    /**
     * Test add_entry max_num_entries.
     */
    public function test_add_entry_max_num_entries() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);
        // Set a time period.
        $data->database->maxentries = 1;
        $DB->update_record('data', $data->database);

        self::setUser($data->student1);
        self::expectExceptionMessage(get_string('noaccess', 'data'));
        self::expectException('moodle_exception');
        mod_data_external::add_entry($data->database->id, 0, []);
    }

    /**
     * Test update_entry.
     */
    public function test_update_entry() {
        global $DB;
        // First create the record structure and add some entries.
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        $newentrydata = [];
        $fields = $DB->get_records('data_fields', array('dataid' => $data->database->id), 'id');
        // Prepare the new entry data.
        foreach ($fields as $field) {
            $subfield = $value = '';

            switch ($field->type) {
                case 'checkbox':
                    $value = ['opt1', 'opt2'];
                    break;
                case 'date':
                    // Add two extra.
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'day',
                        'value' => json_encode('5')
                    ];
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'month',
                        'value' => json_encode('1')
                    ];
                    $subfield = 'year';
                    $value = '1981';
                    break;
                case 'menu':
                    $value = 'menu1';
                    break;
                case 'multimenu':
                    $value = ['multimenu1', 'multimenu4'];
                    break;
                case 'number':
                    $value = 6;
                    break;
                case 'radiobutton':
                    $value = 'radioopt2';
                    break;
                case 'text':
                    $value = 'some text';
                    break;
                case 'textarea':
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'content1',
                        'value' => json_encode(FORMAT_MOODLE)
                    ];
                    $newentrydata[] = [
                        'fieldid' => $field->id,
                        'subfield' => 'itemid',
                        'value' => json_encode(0)
                    ];
                    $value = 'more text';
                    break;
                case 'url':
                    $value = 'https://moodle.org';
                    $subfield = 0;
                    break;
            }

            $newentrydata[] = [
                'fieldid' => $field->id,
                'subfield' => $subfield,
                'value' => json_encode($value)
            ];
        }
        $result = mod_data_external::update_entry($entry11, $newentrydata);
        $result = external_api::clean_returnvalue(mod_data_external::update_entry_returns(), $result);
        self::assertTrue($result['updated']);
        self::assertCount(0, $result['generalnotifications']);
        self::assertCount(0, $result['fieldnotifications']);

        $result = mod_data_external::get_entry($entry11, true);
        $result = external_api::clean_returnvalue(mod_data_external::get_entry_returns(), $result);
        self::assertEquals($data->student1->id, $result['entry']['userid']);
        self::assertCount(9, $result['entry']['contents']);
        foreach ($result['entry']['contents'] as $content) {
            $field = $fields[$content['fieldid']];
            // Stored content same that the one retrieved by WS.
            $dbcontent = $DB->get_record('data_content', array('fieldid' => $field->id, 'recordid' => $entry11));
            self::assertEquals($dbcontent->content, $content['content']);

            // Now double check everything stored is correct.
            if ($field->type == 'checkbox') {
                self::assertEquals('opt1##opt2', $content['content']);
                continue;
            }
            if ($field->type == 'date') {
                self::assertEquals(347500800, $content['content']); // Date in gregorian format.
                continue;
            }
            if ($field->type == 'menu') {
                self::assertEquals('menu1', $content['content']);
                continue;
            }
            if ($field->type == 'multimenu') {
                self::assertEquals('multimenu1##multimenu4', $content['content']);
                continue;
            }
            if ($field->type == 'number') {
                self::assertEquals(6, $content['content']);
                continue;
            }
            if ($field->type == 'radiobutton') {
                self::assertEquals('radioopt2', $content['content']);
                continue;
            }
            if ($field->type == 'text') {
                self::assertEquals('some text', $content['content']);
                continue;
            }
            if ($field->type == 'textarea') {
                self::assertEquals('more text', $content['content']);
                self::assertEquals(FORMAT_MOODLE, $content['content1']);
                continue;
            }
            if ($field->type == 'url') {
                self::assertEquals('https://moodle.org', $content['content']);
                continue;
            }
            self::assertEquals('multimenu1##multimenu4', $content['content']);
        }

        // Now, try to update the entry but removing some required data.
        unset($newentrydata[0]);
        $result = mod_data_external::update_entry($entry11, $newentrydata);
        $result = external_api::clean_returnvalue(mod_data_external::update_entry_returns(), $result);
        self::assertFalse($result['updated']);
        self::assertCount(0, $result['generalnotifications']);
        self::assertCount(1, $result['fieldnotifications']);
        self::assertEquals('field-1', $result['fieldnotifications'][0]['fieldname']);
        self::assertEquals(get_string('errormustsupplyvalue', 'data'), $result['fieldnotifications'][0]['notification']);
    }

    /**
     * Test update_entry sending empty data.
     */
    public function test_update_entry_empty_data() {
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);

        self::setUser($data->student1);
        $result = mod_data_external::update_entry($entry11, []);
        $result = external_api::clean_returnvalue(mod_data_external::update_entry_returns(), $result);
        self::assertFalse($result['updated']);
        self::assertCount(1, $result['generalnotifications']);
        self::assertCount(9, $result['fieldnotifications']);
        self::assertEquals(get_string('emptyaddform', 'data'), $result['generalnotifications'][0]);
    }

    /**
     * Test update_entry in read only period.
     */
    public function test_update_entry_read_only_period() {
        global $DB;
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);
        // Set a time period.
        $data->database->timeviewfrom = time() - HOURSECS;
        $data->database->timeviewto = time() + HOURSECS;
        $DB->update_record('data', $data->database);

        self::setUser($data->student1);
        self::expectExceptionMessage(get_string('noaccess', 'data'));
        self::expectException('moodle_exception');
        mod_data_external::update_entry($entry11, []);
    }

    /**
     * Test update_entry other_user.
     */
    public function test_update_entry_other_user() {
        // Try to update other user entry.
        $data = self::get_basic_setup();
        list($entry11, $entry12, $entry13, $entry14, $entry21) = self::populate_database_with_entries($data);
        self::setUser($data->student2);
        self::expectExceptionMessage(get_string('noaccess', 'data'));
        self::expectException('moodle_exception');
        mod_data_external::update_entry($entry11, []);
    }
}
