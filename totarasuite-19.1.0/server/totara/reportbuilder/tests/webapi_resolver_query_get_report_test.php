<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package totara_reportbuilder
 */

use container_course\course;
use core_phpunit\testcase;
use mod_facetoface\seminar_event;
use totara_reportbuilder\exception\reportbuilder_api_exception;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @coversDefaultClass \totara_reportbuilder\webapi\resolver\query\get_report
 * @group totara_reportbuilder
 */
class totara_reportbuilder_webapi_resolver_query_get_report_test extends testcase {
    use totara_reportbuilder\phpunit\report_testing;
    use webapi_phpunit_helper;

    private const QUERY = 'totara_reportbuilder_get_report';

    /**
     * @param string $source
     * @param string $fullname
     * @param int $records_per_page
     * @param string $description
     * @param string $abstract
     * @return mixed
     * @throws coding_exception
     */
    private function create_report(string $source, string $fullname, int $records_per_page = 40, string $description = '', string $abstract = ''): mixed {
        $report_generator = $this->getDataGenerator()->get_plugin_generator('totara_reportbuilder');
        return $report_generator->create_default_custom_report([
            'fullname' => $fullname,
            'shortname' => reportbuilder::create_shortname($fullname),
            'source' => $source,
            'recordsperpage' => $records_per_page,
            'description' => $description,
            'summary' => $abstract
        ]);
    }

    /**
     * @param int $report_id
     * @param int $user_id
     * @param array $options
     * @return mixed
     * @throws dml_exception
     */
    private function create_user_report_saved_search(int $report_id, int $user_id, array $options = []): mixed {
        // Note: we have created this function for this test instead of using the generator version because the generator
        // version was too restrictive for our use case.
        global $DB;

        // Default search - search for all users with the word 'user' in their fullname
        $search = $options['search'] ?? ['user-fullname' => ['operator' => 0, 'value' => 'user']];

        $saved = new stdClass();
        $saved->reportid = $report_id;
        $saved->userid = $user_id;
        $saved->name = $options['name'] ?? 'Saved ' . $report_id;
        $saved->search = serialize($search);
        $saved->ispublic = $options['ispublic'] ?? true;
        $saved->id = $DB->insert_record('report_builder_saved', $saved);

        return $DB->get_record('report_builder_saved', ['id' => $saved->id]);
    }

    private function resolve_formatting_query(string $report_id, string $column, string $formatter, string $target_format): array {
        return $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'custom_formats' => [[
                        'column' => $column,
                        'formatter' => $formatter,
                        'target_format' => $target_format
                    ]]
                ]
            ]
        );
    }

    public function test_webapi_resolver_query_get_report_not_logged_in(): void {
        $report_id = $this->create_report('user', 'Test Report 1');

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Course or activity not accessible. (You are not logged in)');

        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id
                ]
            ]
        );
    }

    public function test_webapi_resolver_query_get_report(): void {
        $generator = $this->getDataGenerator();

        $user = $generator->create_user();
        $this->setUser($user);

        // Add courses
        $course_1_record = $generator->create_course();
        $course_1 = course::from_record($course_1_record);

        $course_2_record = $generator->create_course();
        $course_2 = course::from_record($course_2_record);

        $course_3_record = $generator->create_course();
        $course_3 = course::from_record($course_3_record);

        $report_id = $this->create_report('courses', 'Test Report 2');
        $report = reportbuilder::create($report_id);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id
                ]
            ]
        );

        $rows = $result['rows'];
        $this->assertCount(3, $rows);
        // Default ordering - by id
        $this->assertEquals($course_1->fullname, $rows[0]->data[0]);
        $this->assertEquals($course_2->fullname, $rows[1]->data[0]);
        $this->assertEquals($course_3->fullname, $rows[2]->data[0]);
    }

    /**
     * We expect an exception if the report does not exist
     */
    public function test_webapi_resolver_query_get_report_where_no_match(): void {
        $this->setAdminUser();
        $dummyId = -1;

        $this->expectException(reportbuilder_api_exception::class);
        $this->expectExceptionMessage("Cannot find report with ID {$dummyId}");

        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $dummyId
                ]
            ]
        );
    }

    public function test_webapi_resolver_query_get_report_with_saved_search(): void {
        global $USER;
        $generator = $this->getDataGenerator();

        $this->setAdminUser();

        // Add users
        $user_2 = $generator->create_user(['firstname' => 'jimothy', 'lastname' => 'Grublenumpf']);
        $user_3 = $generator->create_user(['firstname' => 'salmontha', 'lastname' => 'Grublenumpf']);

        $report_id = $this->create_report('user', "Test Report 4");
        $report = reportbuilder::create($report_id);

        $this->add_column($report, 'user', 'lastname', null, null, null, 0);

        $search = $this->create_user_report_saved_search($report_id, $USER->id, ['search' => ['user-fullname' => ['operator' => 0, 'value' => 'Grublenumpf']]]);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'saved_search_id' => $search->id
                ]
            ]
        );

        $rows = $result['rows'];
        $this->assertCount(2, $rows);
        $this->assertEquals($user_2->lastname, $rows[0]->data[3]);
        $this->assertEquals($user_3->lastname, $rows[1]->data[3]);
    }

    /**
     * When the provided saved search ID does not belong to the provided report, throw an exception.
     */
    public function test_expect_exception_when_search_does_not_belong_to_report(): void {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');
        $user_report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Users report'),
            'source' => 'user'
        ]);
        $search = $this->create_user_report_saved_search(
            $user_report_id,
            $user->id,
            [
                'search' => [
                    'user-fullname' => [
                        'operator' => 0,
                        'value' => 'username',
                    ]
                ],
            ]
        );

        $course_report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Courses report'),
            'source' => 'courses'
        ]);

        $this->expectException(reportbuilder_api_exception::class);
        $this->expectExceptionMessage("Provided saved search ID ({$search->id}) does not belong to report {$course_report_id}");
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $course_report_id,
                    'saved_search_id' => $search->id
                ]
            ]
        );
    }


    /**
     * When the provided saved search ID is not public and does not belong to the API user, throw an exception.
     */
    public function test_expect_exception_when_search_is_not_public(): void {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');
        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'user'
        ]);
        $search = $this->create_user_report_saved_search(
            $report_id,
            $user->id,
            [
                'search' => [
                    'user-fullname' => [
                        'operator' => 0,
                        'value' => 'username',
                    ]
                ],
                'ispublic' => false
            ]
        );

        $this->expectException(reportbuilder_api_exception::class);
        $this->expectExceptionMessage("Provided saved search ({$search->id}) is not a shared search and does not belong to this user.");
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'saved_search_id' => $search->id
                ]
            ]
        );
    }

    /**
     * When the provided saved search ID is not public, but it does belong to the API user,
     * allow filtering.
     */
    public function test_expect_exception_when_private_search_belongs_to_api_user(): void {
        $this->expectNotToPerformAssertions();
        global $USER;
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');
        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'user'
        ]);
        $search = $this->create_user_report_saved_search(
            $report_id,
            $USER->id,
            [
                'search' => [
                    'user-fullname' => [
                        'operator' => 0,
                        'value' => 'username',
                    ]
                ],
                'ispublic' => false
            ]
        );

        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'saved_search_id' => $search->id
                ]
            ]
        );
    }


    /**
     * When the provided saved search ID does not exist on the report, throw an exception.
     */
    public function test_expect_exception_when_search_does_not_exist(): void {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');
        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'courses'
        ]);
        // Make up a saved search ID
        $sid = 145;

        $this->expectException(reportbuilder_api_exception::class);
        $this->expectExceptionMessage("Cannot find saved search with ID {$sid}");
        $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'saved_search_id' => $sid
                ]
            ]
        );
    }

    public function test_webapi_resolver_query_get_report_with_page(): void {
        $generator = $this->getDataGenerator();

        $this->setAdminUser();

        // Add courses
        $course_1_record = $generator->create_course();
        $course_1 = course::from_record($course_1_record);

        $course_2_record = $generator->create_course();
        $course_2 = course::from_record($course_2_record);

        $course_3_record = $generator->create_course();
        $course_3 = course::from_record($course_3_record);

        $report_id = $this->create_report('courses', 'Test Report 5', 2);
        $report = reportbuilder::create($report_id);

        $this->add_column($report, 'course', 'id', null, null, null, 0);
        $this->add_column($report, 'course', 'startdate', null, null, null, 0);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'page' => 1
                ]
            ]
        );

        // Check we receive only the first two courses
        $rows = $result['rows'];
        $this->assertCount(2, $rows);
        // Default ordering - by id
        $this->assertEquals($course_1->fullname, $rows[0]->data[0]);
        $this->assertEquals($course_2->fullname, $rows[1]->data[0]);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'page' => 2
                ]
            ]
        );

        // Check we receive only the third course
        $rows = $result['rows'];
        $this->assertCount(1, $rows);
        $this->assertEquals($course_3->fullname, $rows[0]->data[0]);
    }

    /**
     * Ensure that the provided sort column is parsed and reorders the report as expected
     */
    public function test_query_get_report_with_sort_column(): void {
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course_1_record = $generator->create_course();
        // Give each course a different start date so we can sort it later
        $course_1 = course::from_record($course_1_record);
        $course_1->update((object)['startdate' => time() - 10000]); // This course started first

        $course_2_record = $generator->create_course();
        $course_2 = course::from_record($course_2_record);
        $course_2->update((object)['startdate' => time() - 5000]); // This course started last

        $course_3_record = $generator->create_course();
        $course_3 = course::from_record($course_3_record);
        $course_3->update((object)['startdate' => time() - 7500]);


        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');
        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'courses'
        ]);
        $report = reportbuilder::create($report_id);
        $this->add_column($report, 'course', 'id', null, null, null, 0);
        $this->add_column($report, 'course', 'startdate', null, null, null, 0);

        // Just get the report with default sort order, base.id
        $result = $this->resolve_graphql_query(
            self::QUERY,
            ['input' => ['report_id' => $report_id]]
        );

        $rows = $result['rows'];
        $this->assertCount(3, $rows);
        // Check the order is as expected - ordered by ID
        $this->assertEquals($course_1->fullname, $rows[0]->data[0]);
        $this->assertEquals($course_2->fullname, $rows[1]->data[0]);
        $this->assertEquals($course_3->fullname, $rows[2]->data[0]);

        // Provide a desired sort order
        $result = $this->resolve_graphql_query(
            self::QUERY,
            ['input' => [
                'report_id' => $report_id,
                'sort_by' => [
                    'column' => 'course_startdate'
                ]
            ]]
        );

        $rows = $result['rows'];
        $this->assertCount(3, $rows);
        // Check the order matches the order we provided
        $this->assertEquals($course_2->startdate, $rows[0]->data[2]);
        $this->assertEquals($course_3->startdate, $rows[1]->data[2]);
        $this->assertEquals($course_1->startdate, $rows[2]->data[2]);
    }

    /**
     * We want to test that some columns are excluded according to whether they are hidden or are display columns.
     */
    public function test_query_get_report_with_excluded_columns(): void {
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');
        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'courses'
        ]);
        $report = reportbuilder::create($report_id);
        // This column has an empty heading, so it is not a display column
        $this->add_column($report, 'course', 'enddate', null, null, '', 0);
        // This column is hidden, so we do not expect to see it in the report
        $this->add_column($report, 'course', 'startdate', null, null, null, 1);
        // This column should be visible so that we can tell that the filtering has been applied correctly
        $this->add_column($report, 'course', 'coursetype', null, null, null, 0);

        $course_record = $generator->create_course();
        $course = course::from_record($course_record);
        // Even though start and end date will be excluded, we want to give them data so we can tell that they are being filtered properly
        $course->update((object)[
            'startdate' => time(),
            'enddate' => time() + 10000,
            'coursetype' => TOTARA_COURSE_TYPE_FACETOFACE
        ]);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            ['input' => [
                'report_id' => $report_id,
            ]]
        );

        $columns = $result['columns'];
        $this->assertCount(2, $columns);
        $this->assertEquals('Course Name', $columns[0]->label);
        $this->assertEquals('Course Type', $columns[1]->label);

        // We also need to be sure that the rows are excluded as expected
        $data = $result['rows'][0]->data;
        $this->assertCount(2, $data);
        $this->assertEquals($course->fullname, $data[0]);
        // The array_intersect_key method removes specific keys from the array,
        // so even though we only have two values in the array, the second key is 2 because we removed 1
        $this->assertEquals('Seminar', $data[2]);
    }

    /**
     * When we provide a column that is not part of the report we expect an exception
     */
    public function test_sort_column_invalid_option_no_column(): void {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');

        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'courses'
        ]);

        $sort_column = 'course_startdate';
        $this->expectException(reportbuilder_api_exception::class);
        $this->expectExceptionMessage('Invalid sort column: ' . $sort_column);
        $this->resolve_graphql_query(
            self::QUERY,
            ['input' => [
                'report_id' => $report_id,
                'sort_by' => [
                    'column' => $sort_column
                ]
            ]]
        );
    }

    /**
     * If a user provides an incorrect format - or attempts to use a custom heading to inject
     * potentially malicious code into the query - we throw an exception
     */
    public function test_sort_column_invalid_option_incorrect_input_format(): void {
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $rb_generator = $generator->get_plugin_generator('totara_reportbuilder');

        $report_id = $rb_generator->create_default_custom_report([
            'fullname' => 'Test report',
            'shortname' => \reportbuilder::create_shortname('Test report'),
            'source' => 'courses'
        ]);
        $report = reportbuilder::create($report_id);
        $malicious_column = 'id; SQL Injection here';
        $this->add_column(
            $report,
            'course',
            'startdate',
            null,
            null,
            $malicious_column,
            0
        );

        $this->expectException(reportbuilder_api_exception::class);
        $this->expectExceptionMessage('Invalid sort column: ' . $malicious_column);
        $this->resolve_graphql_query(
            self::QUERY,
            ['input' => [
                'report_id' => $report_id,
                'sort_by' => [
                    'column' => $malicious_column
                ]
            ]]
        );
    }

    public function test_webapi_resolver_query_get_report_metadata(): void {
        $generator = $this->getDataGenerator();

        $user = $generator->create_user();
        $this->setUser($user);

        $title = 'Test Report';
        $description = 'a nice, concise description';
        $abstract = 'the summary of the report';
        $records_per_page = 20;
        $report_id = $this->create_report('courses', $title, $records_per_page, $description, $abstract);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id
                ]
            ]
        );

        $metadata = $result['report_metadata'];
        $this->assertEquals($title, $metadata['title']);
        $this->assertEquals($abstract, $metadata['abstract']);
        $this->assertEquals($description, $metadata['description']);
        $this->assertEquals($records_per_page, $metadata['records_per_page']);
    }

    /**
     * Check that the pagination information is calculated correctly for a report with data.
     */
    public function test_query_get_report_pagination_contains_correct_data(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);

        $records_per_page = 4;
        $i = 0;
        // Choose a number not divisible by the number of records per page so that we have
        // a remainder page with some values.
        while ($i < 17) {
            $generator->create_course();
            $i++;
        }

        $report_id = $this->create_report('courses', 'Test Report', $records_per_page);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'page' => 2,
                    'display_report_pagination' => true
                ]
            ]
        );

        $pagination = $result['report_pagination'];

        $this->assertEquals(3, $pagination['next_cursor']);
        $this->assertEquals(5, $pagination['total_pages']);

        // Check that if you request a page after the last page, the cursor returns 'null'
        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'page' => 12,
                    'display_report_pagination' => true
                ]
            ]
        );
        $pagination = $result['report_pagination'];

        $this->assertNull($pagination['next_cursor']);
    }

    /**
     * Check that the pagination information is empty for a report without data.
     */
    public function test_query_get_report_pagination_when_null(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);

        $report_id = $this->create_report('courses', 'test report');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'display_report_pagination' => true
                ]
            ]
        );

        $pagination = $result['report_pagination'];

        $this->assertNull($pagination['next_cursor']);
        $this->assertEquals(0, $pagination['total_pages']);
    }

    /**
     * When the display_report_pagination bool is false or not set, we do not
     * want to do the expensive calculations, so we return null values
     */
    public function test_query_get_report_pagination_when_display_report_pagination_is_false(): void {
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $this->setUser($user);

        $report_id = $this->create_report('courses', 'test report');

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'display_report_pagination' => false
                ]
            ]
        );

        $pagination = $result['report_pagination'];

        $this->assertNull($pagination['next_cursor']);
        $this->assertNull($pagination['total_pages']);
    }

    /**
     * When the user provides multiple custom formats, check that each column is formatted correctly
     */
    public function test_get_report_with_multiple_formatting_options(): void {
        $generator = $this->getDataGenerator();

        $user = $generator->create_user();
        $this->setUser($user);

        $start_date_epoch = 1745782465;
        // NB: This is not quite iso8601 format, but it is what our date formatter uses
        $start_date_iso8601 = '2025-04-28T03:34:25+0800';

        $textarea_html_input = 'A text <a href="https://localhost">field</a> removes links <p>but keeps whitespace</p>';
        $textarea_html_formatted = 'A text field removes links<br />
but keeps whitespace<br />
';

        $string_plain_input = 'A string <br />should have tags stripped';
        $string_plain_formatted = 'A string should have tags stripped';

        $course_record_fields = (object)[
            'startdate' => $start_date_epoch,
            'summary' => $textarea_html_input,
            'idnumber' => $string_plain_input,
        ];

        $course_record = $generator->create_course();
        $course = course::from_record($course_record);
        $course->update($course_record_fields);

        $report_id = $this->create_report('courses', 'Test Report 2');
        $report = reportbuilder::create($report_id);
        $this->add_column($report, 'course', 'startdate', null, null, null, 0);
        $this->add_column($report, 'course', 'summary', null, null, null, 0);
        $this->add_column($report, 'course', 'idnumber', null, null, null, 0);

        $result = $this->resolve_graphql_query(
            self::QUERY,
            [
                'input' => [
                    'report_id' => $report_id,
                    'custom_formats' => [
                        [
                            'column' => 'course_startdate',
                            'target_format' => 'iso8601',
                            'formatter' => 'DATE'
                        ],
                        [
                            'column' => 'course_summary',
                            'target_format' => 'html',
                            'formatter' => 'TEXTAREA'
                        ],
                        [
                            'column' => 'course_idnumber',
                            'target_format' => 'plain',
                            'formatter' => 'STRING'
                        ],
                    ]
                ]
            ]
        );

        $data = $result['rows'][0]->data;
        $this->assertEquals($start_date_iso8601, $data[1]);
        $this->assertEquals($textarea_html_formatted, $data[2]);
        $this->assertEquals($string_plain_formatted, $data[3]);
    }

    /**
     * Check that when an incorrect format and formatter are provided together,
     * we throw an exception to inform the user.
     */
    public function test_get_report_expect_error_for_bad_date_formatter(): void {
        self::setAdminUser();
        $generator = $this->getDataGenerator();
        $course_1_record = $generator->create_course();
        $course_1 = course::from_record($course_1_record);
        $course_1->update((object)['startdate' => time()]);

        $report_id = $this->create_report('courses', 'Test Report 2');
        $report = reportbuilder::create($report_id);
        $this->add_column($report, 'course', 'startdate', null, null, null, 0);

        // Using an invalid target format for the formatter will give an error.
        self::expectException(reportbuilder_api_exception::class);
        self::expectExceptionMessage("Invalid format (ISO8601) given for the formatter of the type TEXT. Please check that you have the correct formatter for the type of data you want to modify.");
        $this->resolve_formatting_query($report_id, 'course_startdate', 'TEXT', 'iso8601');
    }

    /**
     * Check that when a valid but unsupported format is provided,
     * we throw an exception to inform the user.
     */
    public function test_get_report_expect_error_for_invalid_text_format(): void {
        self::setAdminUser();
        // We need a record to check in order for the formatter to assess the format type we give to it
        $generator = $this->getDataGenerator();
        $course_1_record = $generator->create_course();
        $course_1 = course::from_record($course_1_record);
        $course_1->update((object)['summary' => 'abcde']);

        $report_id = $this->create_report('courses', 'Test Report 2');
        $report = reportbuilder::create($report_id);
        $this->add_column($report, 'course', 'summary', null, null, null, 0);

        // FORMAT_JSON_EDITOR is a valid text format, but it is unsupported by our formatters.
        self::expectException(reportbuilder_api_exception::class);
        self::expectExceptionMessage("Invalid format (JSON_EDITOR) given for the formatter of the type TEXT. Please check that you have the correct formatter for the type of data you want to modify.");
        $this->resolve_formatting_query($report_id, 'course_summary', 'TEXT', 'json_editor');
    }

    public function test_get_report_expect_error_for_invalid_formatter(): void {
        self::setAdminUser();
        $report_id = $this->create_report('courses', 'Test Report 2');
        self::expectException(reportbuilder_api_exception::class);
        self::expectExceptionMessage("Invalid custom format formatter provided: INVALID FORMATTER");
        $this->resolve_formatting_query($report_id, '', 'INVALID FORMATTER', '');
    }

    public function test_get_report_expect_error_when_no_target_format_is_provided(): void {
        self::setAdminUser();
        $report_id = $this->create_report('courses', 'Test Report 2');
        $report = reportbuilder::create($report_id);
        $this->add_column($report, 'course', 'summary', null, null, null, 0);

        self::expectException(reportbuilder_api_exception::class);
        self::expectExceptionMessage("No target_format has been provided for the formatter TEXT");
        $this->resolve_formatting_query($report_id, 'course_summary', 'TEXT', '');
    }

    public function test_get_report_expect_error_when_no_column_is_provided(): void {
        self::setAdminUser();
        $report_id = $this->create_report('courses', 'Test Report 2');
        self::expectException(reportbuilder_api_exception::class);
        self::expectExceptionMessage("No column has been provided for the formatter TEXT");
        $this->resolve_formatting_query($report_id, '', 'TEXT', 'plain');
    }

    public function test_get_report_expect_error_for_invalid_column(): void {
        self::setAdminUser();
        $report_id = $this->create_report('courses', 'Test Report 2');
        self::expectException(reportbuilder_api_exception::class);
        self::expectExceptionMessage("Unable to resolve a column with the name \"bad column\" on the report");
        $this->resolve_formatting_query($report_id, 'bad column', 'TEXT', 'plain');
    }

    /**
     * Non-base report sources may provide their own display functions.
     * If the display functions convert the date to a string, we need to reconvert it
     * to a date in order to provide custom formatting.
     */
    public function test_get_report_get_date_after_string_formatting(): void {
        self::setAdminUser();
        $seminar_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $course = $this->getDataGenerator()->create_course();
        $seminar = $seminar_generator->create_instance(array('course' => $course->id));
        $event_date = new stdClass();
        $event_date->timestart = 1745782465;
        $event_date->timefinish = $event_date->timestart + (DAYSECS * 2);
        $event_date->sessiontimezone = 'Pacific/Auckland';
        $seminar_generator->add_session(
            array('facetoface' => $seminar->id, 'sessiondates' => array($event_date))
        );

        $report_id = $this->create_report('facetoface_events', 'Test Report 2');
        $result = $this->resolve_formatting_query(
            $report_id, 'session_eventstartdate', 'DATE', 'iso8601'
        );
        $data = $result['rows'][0]->data;
        // This is the column we wanted to format
        $this->assertEquals('2025-04-28T03:34:00+0800', $data[3]);
        // This is the default format for seminar event date fields - unaffected by our formatting
        $this->assertEquals('30 April 2025, 7:34 AM Pacific/Auckland', $data[4]);
    }
}
