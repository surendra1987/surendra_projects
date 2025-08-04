<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author  Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use core\date_format;
use core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_overview_last_update_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_overview_last_update';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('array');

        $this->resolve_graphql_type(self::TYPE, 'name', new \stdClass());
    }

    /**
     * @covers ::resolve
     */
    public function test_invalid_field(): void {
        $last_update = ['date' => time(), 'description' => 'testing123'];
        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessageMatches("/$field/");
        $this->resolve_graphql_type(self::TYPE, $field, $last_update);
    }

    /**
     * Test data for test_valid
     */
    public static function td_valid(): array {
        $alt_date_fmt = date_format::FORMAT_DATESHORT;
        $fmt_date = fn (?int $date, string $fmt): string => is_null($date)
            ? ''
            : userdate(
                $date,
                get_string(date_format::get_lang_string($fmt), 'langconfig')
            );

        $last_update = ['date' => time(), 'description' => 'testing123'];

        return [
            'date' => [
                $last_update,
                $fmt_date($last_update['date'], $alt_date_fmt),
                'date',
                $alt_date_fmt
            ],
            'description' => [
                $last_update,
                format_text($last_update['description'], FORMAT_HTML),
                'description'
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(
        array $last_update, $expected, string $field, ?string $format=null
    ): void {
        $args = $format ? ['format' => $format] : [];

        $this->assertEquals(
            $expected,
            $this->resolve_graphql_type(self::TYPE, $field, $last_update, $args),
            'wrong value'
        );
    }
}
