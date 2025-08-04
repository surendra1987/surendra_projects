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

use core\format;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * Test the report_metadata type resolver without needing to call the get_report query
 */
class totara_reportbuilder_webapi_resolver_type_report_metadata_test extends \core_phpunit\testcase {
    use webapi_phpunit_helper;

    /**
     * Each string field in the report metadata will go through the same steps
     * to be formatted. Here we test the different formats to ensure that they are
     * parsed correctly.
     */
    public function test_resolver_string_field(): void {
        $this->setAdminUser();

        $title = 'Some html <a href="http://google.com">in the title</a>';
        $metadata = ['title' => $title, 'context' => context_system::instance(), 'report_id' => 1];

        // In PLAIN format, we want to remove any HTML tags
        $args = ['format' => format::FORMAT_PLAIN];
        $result = $this->resolve_graphql_type(
            'totara_reportbuilder_report_metadata',
            'title',
            $metadata,
            $args
        );
        self::assertSame('Some html in the title', $result);

        // In HTML format, we want to leave in any tags
        $args = ['format' => format::FORMAT_HTML];
        $result = $this->resolve_graphql_type(
            'totara_reportbuilder_report_metadata', 'title', $metadata, $args
        );
        self::assertSame($title, $result);

        // We don't do any parsing for RAW or other formats
        $args = ['format' => format::FORMAT_RAW];
        $result = $this->resolve_graphql_type(
            'totara_reportbuilder_report_metadata', 'title', $metadata, $args
        );
        self::assertSame($title, $result);
    }

    /**
     * The records per page field is the only integer field in the metadata,
     * so we don't need to do any formatting for it.
     */
    public function test_resolver_records_per_page(): void {
        $this->setAdminUser();
        $result = $this->resolve_graphql_type(
            'totara_reportbuilder_report_metadata',
            'records_per_page',
            ['records_per_page' => 100]
        );
        self::assertEquals(100, $result);
    }
}