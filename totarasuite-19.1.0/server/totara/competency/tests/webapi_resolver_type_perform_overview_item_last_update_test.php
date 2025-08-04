<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
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
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package totara_competency
 * @category test
 */

use core\format;
use core\date_format;
use totara_competency\models\perform_overview\item_last_update;
use totara_competency\formatter\perform_overview\item_last_update as formatter;
use totara_webapi\phpunit\webapi_phpunit_helper;

require_once(__DIR__.'/totara_competency_testcase.php');

/**
 * @group totara_competency
 * @group totara_competency_overview
 */
class totara_competency_webapi_resolver_type_perform_overview_item_last_update_test
extends totara_competency_testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'totara_competency_perform_overview_item_last_update';

    /**
     * Test data for test_invalid
     */
    public static function td_invalid(): array {
        $src = new item_last_update(time(), 'testing');

        return [
            '1. wrong target class' => [new stdClass(), 'id', item_last_update::class],
            '2. unknown field' => [$src, 'unknown_field', 'unknown_field' ]
        ];
    }

    /**
     * @dataProvider td_invalid
     */
    public function test_invalid($src, string $field, string $error): void {
        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage($error);
        $this->resolve_graphql_type(self::TYPE, $field, $src);
    }

    /**
     * Test data for test_valid
     */
    public static function td_valid(): array {
        $src = new item_last_update(time(), "<b>Test description</b>");

        $def_date_fmt = date_format::FORMAT_DATELONG;
        $alt_date_fmt = date_format::FORMAT_DATESHORT;
        $fmt_date = fn(int $date, string $fmt): string => userdate(
            $date, get_string(date_format::get_lang_string($fmt), 'langconfig')
        );

        $alt_txt_fmt = format::FORMAT_RAW;
        $fmt_plain = fn(string $text): string => format_string($text);

        return [
            '[date]/1. default format' => [
                $fmt_date($src->get_date(), $def_date_fmt),
                $src,
                formatter::DATE, $def_date_fmt
            ],
            '[date]/2. custom format' => [
                $fmt_date($src->get_date(), $alt_date_fmt),
                $src,
                formatter::DATE, $alt_date_fmt
            ],
            '[desc]/1. default format' => [
                $fmt_plain($src->get_description()), $src, formatter::DESC
            ],
            '[desc]/2. custom format' => [
                $src->get_description(), $src, formatter::DESC, $alt_txt_fmt
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(
        $expected,
        item_last_update $src,
        string $field,
        ?string $format=null
    ): void {
        $args = $format ? ['format' => $format] : [];

        $this->assertEquals(
            $expected,
            $this->resolve_graphql_type(self::TYPE, $field, $src, $args),
            'wrong value'
        );
    }
}
