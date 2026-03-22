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
use totara_competency\models\perform_overview\item;
use totara_competency\models\perform_overview\item_last_update;
use totara_competency\formatter\perform_overview\item as formatter;
use totara_competency\user_groups;
use totara_competency\entity\competency_achievement;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_competency\testing\generator as competency_generator;

require_once(__DIR__.'/perform_overview_testcase.php');

/**
 * @group totara_competency
 * @group totara_competency_overview
 */
class totara_competency_webapi_resolver_type_perform_overview_item_test
extends totara_competency_perform_overview_testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'totara_competency_perform_overview_item';

    /**
     * @covers \totara_competency\webapi\resolver\type\perform_overview_item::resolve
     */
    public function test_invalid_input(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(item::class);

        $this->resolve_graphql_type(self::TYPE, 'id', new \stdClass());
    }

    /**
     * @covers \totara_competency\webapi\resolver\type\perform_overview_item::resolve
     */
    public function test_invalid_field(): void {
        $item = $this->create_test_item();
        $field = 'unknown';

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessageMatches("/$field/");
        $this->resolve_graphql_type(self::TYPE, $field, $item);
    }

    /**
     * Test data for test_valid
     */
    public static function td_valid(): array {
        $def_date_fmt = date_format::FORMAT_DATELONG;
        $alt_date_fmt = date_format::FORMAT_DATESHORT;
        $fmt_date = fn (int $date, string $fmt): string => userdate(
            $date, get_string(date_format::get_lang_string($fmt), 'langconfig')
        );

        $alt_txt_fmt = format::FORMAT_RAW;
        $fmt_plain = fn(string $text): string => format_string($text);
        $fmt_html = fn(string $text): string => format_text($text, FORMAT_HTML);

        // Unfortunately, PHPUnit runs _all data providers including others in
        // this and other test harnesses_ before even running a single test. If
        // this method created a 'real' item here, it creates database records
        // that would leak through to other testharnesses; It would likely mess
        // up other tests and it would be exceedingly difficult to trace where
        // those extra records can from. Hence the use of callables below that
        // allow this data provider to return the expected value based on an
        // item created by the targetted test method.
        return [
            '[no format]/1. achievement id' => [
                fn(item $item): int => $item->get_achievement_id(),
                formatter::ACHIEVEMENT_ID
            ],
            '[no format]/2. raw id' => [
                fn(item $item): int => $item->get_competency_id(),
                formatter::COMPETENCY_ID
            ],
            '[no format]/3. id' => [
                fn(item $item): int => $item->get_achievement_id(),
                formatter::UNIQUE_ID
            ],
            '[no format]/4. assignment ID' => [
                fn(item $item): int => $item->get_assignment_id(),
                formatter::ASSIGNMENT_ID
            ],
            '[no format]/5. assignment url' => [
                fn(item $item): string => $item->get_url(),
                formatter::ASSIGNMENT_URL
            ],
            '[no format]/6. user id' => [
                fn(item $item): int => $item->get_user_id(),
                formatter::USER_ID
            ],
            '[achievement date]/1. default format' => [
                fn(item $item): string => $fmt_date(
                    $item->get_achievement_date(), $def_date_fmt
                ),
                formatter::ACHIEVEMENT_DATE
            ],
            '[achievement date]/2. custom format' => [
                fn(item $item): string => $fmt_date(
                    $item->get_achievement_date(), $alt_date_fmt
                ),
                formatter::ACHIEVEMENT_DATE,
                $alt_date_fmt
            ],
            '[achievement level]/1. default format' => [
                fn(item $item): string => $fmt_plain(
                    $item->get_achievement_level()
                ),
                formatter::ACHIEVEMENT_LEVEL
            ],
            '[achievement level]/2. custom format' => [
                fn(item $item): string => $item->get_achievement_level(),
                formatter::ACHIEVEMENT_LEVEL,
                $alt_txt_fmt
            ],
            '[achievement name]/1. default format' => [
                fn(item $item): string => $fmt_plain(
                    $item->get_assignment_type()
                ),
                formatter::ASSIGNMENT_TYPE
            ],
            '[achievement name]/2. custom format' => [
                fn(item $item): string => $item->get_assignment_type(),
                formatter::ASSIGNMENT_TYPE,
                $alt_txt_fmt
            ],
            '[assignment date]/1. default format' => [
                fn(item $item): string => $fmt_date(
                    $item->get_assignment_date(), $def_date_fmt
                ),
                formatter::ASSIGNMENT_DATE
            ],
            '[assignment date]/2. custom format' => [
                fn(item $item): string => $fmt_date(
                    $item->get_assignment_date(), $alt_date_fmt
                ),
                formatter::ASSIGNMENT_DATE,
                $alt_date_fmt
            ],
            '[competency name]/1. default format' => [
                fn(item $item): string => $fmt_plain(
                    $item->get_competency_name()
                ),
                formatter::COMPETENCY_NAME
            ],
            '[competency name]/2. custom format' => [
                fn(item $item): string => $item->get_competency_name(),
                formatter::COMPETENCY_NAME,
                $alt_txt_fmt
            ],
            '[competency desc]/1. default format' => [
                fn(item $item): string => $fmt_html(
                    $item->get_competency_description()
                ),
                formatter::COMPETENCY_DESC
            ],
            '[competency desc]/2. custom format' => [
                fn(item $item): string => $item->get_competency_description(),
                formatter::COMPETENCY_DESC,
                $alt_txt_fmt
            ],
            'last update' => [
                fn(item $item): item_last_update => $item->get_last_update(),
                formatter::LAST_UPDATE
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(
        callable $expected, string $field, ?string $format=null
    ): void {
        $args = $format ? ['format' => $format] : [];
        $item = $this->create_test_item();

        $this->assertEquals(
            $expected($item),
            $this->resolve_graphql_type(self::TYPE, $field, $item, $args),
            'wrong value'
        );
    }

    /**
     * Check that the type resolver when it uses a formatter on a nullable string field won't throw an error, but return a
     * replacement instead (empty string).
     * @return void
     */
    public function test_type_formatter_with_nullable_string_value(): void {
        // Set up - create a competency with description of null, a competency_achievement, and a
        // perform_overview_item containing them both.
        $scale = totara_hierarchy\entity\scale::repository()->one();
        $generator = competency_generator::instance();
        $competency_fw = $generator->create_framework($scale);
        $test_competency = $generator->create_competency(null, $competency_fw->id, [
            'fullname' => 'test competency 1',
        ]);
        $test_competency->description = null;
        $test_competency->save();

        $user = $this->getDataGenerator()->create_user();
        $assignment = $this->generator()->assignment_generator()->create_user_assignment($test_competency->id, $user->id);

        $achievement = (new competency_achievement([
            'user_id' => $user->id,
            'assignment_id' => $assignment->id,
            'competency_id' => $test_competency->id,
            'scale_value_id' => 1,
            'proficient' => 1,
            'status' => 0,
            'time_created' => 0,
            'time_status' => 0,
            'time_proficient' => 0,
            'time_scale_value' => 0,
            'last_aggregated' => 0,
        ]))->save();
        $perform_overview_item = new item($achievement);

        $formatter = new formatter($perform_overview_item, context_system::instance());

        // Operate - check that the null value we're trying to format returns without an error.
        $formatted_value = $formatter->format('description', 'HTML');

        // Assert.
        $this->assertNull($perform_overview_item->get_competency_description());
        $this->assertEquals('', $formatted_value);
    }

    /**
     * Creates a test item.
     *
     * @return item the test item.
     */
    private function create_test_item(): item {
        $rating_assigned = 'Assigned (AS)';
        $rating_progressed = 'Progressing (PR)';
        $rating_fully_achieved = 'Complete (FA)';

        $raw_scale = [
            [$rating_assigned, false],
            [$rating_progressed, false],
            [$rating_fully_achieved, true]
        ];

        $days_ago = 23;
        $raw_achievements = (object) [
            'comp_name' => 'test_competency',
            'user_group' => [user_groups::USER, 'test_user_1'],
            'times' => [
                [$days_ago + 15, $rating_assigned],
                [$days_ago + 10, $rating_progressed],
                [$days_ago - 1, $rating_fully_achieved]
            ]
        ];

        return $this->create_test_data([$raw_achievements], $raw_scale, time())
            ->achievements
            ->first()
            ->transform_to(item::class)
            ->first();
    }
}
