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
use core\testing\generator as core_generator;
use perform_goal\formatter\overview\item as formatter;
use perform_goal\model\overview\item;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_webapi\phpunit\webapi_phpunit_helper;

/**
 * @group perform_goal
 */
class perform_goal_webapi_type_overview_item_test extends testcase {
    use webapi_phpunit_helper;

    private const TYPE = 'perform_goal_overview_item';

    /**
     * @covers ::resolve
     */
    public function test_invalid_input(): void {
        $this->setAdminUser();

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage(item::class);

        $this->resolve_graphql_type(self::TYPE, 'name', new \stdClass());
    }

    /**
     * @covers ::resolve
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
        return [
            'assignment date' => [
                'assignment_date_formatter',
                formatter::ASSIGNMENT_DATE,
                date_format::FORMAT_DATELONG
            ],
            'achievement date' => [
                'achievement_date_formatter',
                formatter::ACHIEVEMENT_DATE,
                date_format::FORMAT_DATESHORT
            ],
            'description' => [
                'description_formatter',
                formatter::DESC
            ],
            'goal id' => [
                'goal_id_formatter',
                formatter::GOAL_ID
            ],
            'goal url' => [
                'goal_url_formatter',
                formatter::URL
            ],
            'name' => [
                'name_formatter',
                formatter::NAME
            ],
            'raw id' => [
                'raw_id_formatter',
                formatter::RAW_ID
            ],
            'user id' => [
                'user_id_formatter',
                formatter::USER_ID
            ],
            'due' => [
                'due_formatter',
                formatter::DUE
            ],
            'last update' => [
                'last_update_formatter',
                formatter::LAST_UPDATE
            ]
        ];
    }

    /**
     * @dataProvider td_valid
     */
    public function test_valid(
        string $formatterMethod,
        string $field,
        ?string $format = null
    ): void {
        $args = $format ? ['format' => $format] : [];
        $item = $this->create_test_item();

        $this->assertEquals(
            $this->$formatterMethod($item, $format),
            $this->resolve_graphql_type(self::TYPE, $field, $item, $args),
            'wrong value'
        );
    }

    // Formatter methods

    /**
     * @param item $item
     * @param string $format
     * @return string
     */
    protected function assignment_date_formatter(item $item, string $format): string {
        return $this->format_date($item->{formatter::ASSIGNMENT_DATE}, $format);
    }

    /**
     * @param item $item
     * @param string $format
     * @return string
     */
    protected function achievement_date_formatter(item $item, string $format): string {
        return $this->format_date($item->{formatter::ACHIEVEMENT_DATE}, $format);
    }

    /**
     * @param item $item
     * @return string
     */
    protected function description_formatter(item $item): string {
        return format_text($item->{formatter::DESC}, FORMAT_HTML);
    }

    /**
     * @param item $item
     * @return int
     */
    protected function goal_id_formatter(item $item): int {
        return $item->{formatter::GOAL_ID};
    }

    /**
     * @param item $item
     * @return string|null
     */
    protected function goal_url_formatter(item $item): ?string {
        return $item->{formatter::URL};
    }

    /**
     * @param item $item
     * @return string
     */
    protected function name_formatter(item $item): string {
        return format_string($item->{formatter::NAME});
    }

    /**
     * @param item $item
     * @return int
     */
    protected function raw_id_formatter(item $item): int {
        return $item->{formatter::GOAL_ID};
    }

    /**
     * @param item $item
     * @return int
     */
    protected function user_id_formatter(item $item): int {
        return $item->{formatter::USER_ID};
    }

    /**
     * @param item $item
     * @return array
     */
    protected function due_formatter(item $item): array {
        return $item->{formatter::DUE};
    }

    /**
     * @param item $item
     * @return array
     */
    protected function last_update_formatter(item $item): array {
        return $item->{formatter::LAST_UPDATE};
    }

    /**
     * @param int|null $date
     * @param string $format
     * @return string
     */
    protected function format_date(?int $date, string $format): string {
        if (is_null($date)) {
            return '';
        }
        return userdate(
            $date,
            get_string(date_format::get_lang_string($format), 'langconfig')
        );
    }

    /**
     * Creates a test item.
     *
     * @return item the test item.
     */
    private function create_test_item(): item {
        $this->setAdminUser();

        $core_generator = core_generator::instance();
        $subject_id = $core_generator->create_user()->id;

        $cfg = [
            'owner_id' => $core_generator->create_user()->id,
            'user_id' => $subject_id,
            'target_type' => date::get_type(),
            'context' => context_user::instance($subject_id)
        ];

        $goal = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        return new item($goal);
    }
}
