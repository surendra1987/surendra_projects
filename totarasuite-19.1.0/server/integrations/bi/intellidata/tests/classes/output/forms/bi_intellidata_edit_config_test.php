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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Scott Davies <scott.davies@totara.com>
 * @package bi_intellidata
 */


use bi_intellidata\helpers\DBManagerHelper;
use bi_intellidata\output\forms\bi_intellidata_edit_config;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\services\config_service;
use bi_intellidata\services\datatypes_service;
use core_phpunit\testcase;

/**
 * Unit tests for the bi_intellidata_edit_config form class.
 */
class bi_intellidata_bi_intellidata_edit_config_test extends testcase {

    /**
     * Helper method.
     * @return bi_intellidata_edit_config
     */
    public function get_test_edit_form(): bi_intellidata_edit_config {
        $test_datatype = 'users';
        $datatype_config = datatypes_service::get_datatype($test_datatype);
        $datatype_config['timemodifiedfields'] = config_service::get_available_timemodified_fields($test_datatype);
        $record = datatypeconfig::get_record(['datatype' => $test_datatype]);
        $export_log_repository = new export_log_repository();
        $export_log = $export_log_repository->get_datatype_export_log($test_datatype);

        $edit_form = new bi_intellidata_edit_config(null, [
            'data' => $record->to_record(),
            'config' => (object)$datatype_config,
            'exportlog' => $export_log
        ]);
        return $edit_form;
    }

    /**
     * @return void
     */
    public function test_get_install_xml_files(): void {
        // Operate.
        $result_xmldb_files = DBManagerHelper::get_install_xml_files(true);

        // Assert.
        self::assertNotEmpty($result_xmldb_files);
        self::assertTrue(stripos($result_xmldb_files[0], 'install.xml') !== false);
    }

    /**
     * @return void
     */
    public function test_get_field_for_table_from_xml_info(): void {
        // Set up.
        $test_xmldb_files = DBManagerHelper::get_install_xml_files(true);
        $datatype_table_to_edit = 'course_sections';

        // Operate.
        $result_table_comment_description = DBManagerHelper::get_field_for_table_from_xml_info($datatype_table_to_edit, $test_xmldb_files);

        // Assert.
        self::assertEquals('to define the sections for each course', $result_table_comment_description);
    }

    /**
     * @return void
     */
    public function test_get_datatype_schema_description(): void {
        // Set up.
        $edit_form = $this->get_test_edit_form();

        // Operate.
        $result_table_comment_description1 = $edit_form->get_datatype_schema_description('This_datatype_does_not_exist');

        $result_table_comment_description2 = $edit_form->get_datatype_schema_description('oauth2_tokens'); // restricted.

        $result_table_comment_description3 = $edit_form->get_datatype_schema_description('goal'); // valid.

        // Assert.
        self::assertEquals('', $result_table_comment_description1);
        self::assertEquals('', $result_table_comment_description2);
        self::assertEquals('Totara goals', $result_table_comment_description3);
    }

    /**
     * @return void
     */
    public function test_format_comment(): void {
        // Single quotes around table names should be replaced by angle quotes.
        $test_comment1 = "The rationale for the 'tag_correlation' table is performance. It works as a cache for a potentially heavy load query ";
        $test_comment1 .= " at the 'tag_instance' table. So, the 'tag_correlation' table stores redundant information derived from the 'tag_instance' table";

        // The awkward table description default should be replaced.
        $test_comment2 = 'Default comment for the table, please edit me';

        // Operate.
        $formatted_comment1 = DBManagerHelper::format_comment($test_comment1);
        $formatted_comment2 = DBManagerHelper::format_comment($test_comment2);

        // Assert.
        self::assertFalse(stripos($formatted_comment1, "'") > -1);
        self::assertTrue(stripos($formatted_comment1, "′") > -1); // angle quotes should there.

        self::assertNotEquals($test_comment2, $formatted_comment2);
    }
}
