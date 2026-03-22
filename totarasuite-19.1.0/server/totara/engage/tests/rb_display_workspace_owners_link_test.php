<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package totara_engage
 */

use core\collection;
use core_phpunit\testcase;
use totara_engage\rb\display\workspace_owners_link;

/**
 * @group totara_reportbuilder
 * @group totara_engage
 */
class totara_engage_rb_display_workspace_owners_link_test extends testcase {
    /**
     * Data provider for multiple tests.
     */
    public static function td_format(): array {
        return [
            'html' => ['html'],
            'export' => ['']
        ];
    }

    /**
     * @dataProvider td_format
     */
    public function test_no_owners(
        string $format
    ): void {
        [$report, $row, $column] = self::create_testdata();

        self::assertEquals(
            get_string('unassigned', 'totara_engage'),
            workspace_owners_link::display('', $format, $row, $column, $report)
        );
    }

    /**
     * @dataProvider td_format
     */
    public function test_single_owner(
        string $format
    ): void {
        [$report, $row, $column] = self::create_testdata();

        $owner = self::getDataGenerator()->create_user();
        $owner_id = $owner->id;

        $expected = $format === 'html'
            ? self::user_link($owner)
            : fullname($owner);

        self::assertEquals(
            $expected,
            workspace_owners_link::display(
                "$owner_id", $format, $row, $column, $report
            )
        );
    }

    /**
     * @dataProvider td_format
     */
    public function test_multiple_owner(
        string $format
    ): void {
        [$report, $row, $column] = self::create_testdata();

        $generator = self::getDataGenerator();
        $owners = collection::new(range(0, 4))
            ->map(fn(int $i): stdClass => $generator->create_user());

        $is_html = $format === 'html';
        $expected = $owners->map(
            fn(stdClass $owner): string => $is_html
                ? self::user_link($owner)
                : fullname($owner)
        )->all();

        $actual = workspace_owners_link::display(
            implode(',', $owners->pluck('id')),
            $format,
            $row,
            $column,
            $report
        );

        self::assertEqualsCanonicalizing(
            $expected, explode($is_html ? '<br/>' : ',',  $actual)
        );
    }

    private static function create_testdata(): array {
        $report = reportbuilder::create_embedded(
            'engagedworkspace', new rb_config(), false
        );

        $column = new rb_column(
            'engagedworkspace', 'owners', 'owners', 'owners'
        );

        return [$report, new stdClass(), $column];
    }

    private static function user_link(stdClass $owner): string {
        return html_writer::link(
            user_get_profile_url($owner,  null),
            fullname($owner)
        );
    }
}