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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Matthias Bonk <matthias.bonk@totaralearning.com>
 * @package perform_goal
 */

use core\format;
use core_phpunit\testcase;
use perform_goal\entity\goal as goal_entity;
use perform_goal\formatter\goal as goal_formatter;
use perform_goal\testing\generator as goal_generator;

class perform_goal_goal_formatter_test extends testcase {

    public static function float_value_formatting_data_provider(): array {
        return [
            [1.000, '1'],
            [1.010, '1.01'],
            [1.00001, '1.00001'], // max 5 decimals
            [1.000009, '1.00001'], // rounds up
            [1.000004, '1'], // rounds down
            [99999, '99999'], // 5 digits max
        ];
    }

    /**
     * @dataProvider float_value_formatting_data_provider
     * @param $db_value
     * @param $expected_formatted_value
     * @return void
     */
    public function test_float_values($db_value, $expected_formatted_value): void {
        $user = self::getDataGenerator()->create_user();
        self::setUser($user);

        $goal = goal_generator::instance()->create_goal();

        /** @var goal_entity $goal_entity */
        $goal_entity = $goal->get_entity_copy();

        $goal_entity->current_value = $db_value;
        $goal_entity->save();

        $goal_formatter = new goal_formatter($goal->refresh(), $goal->context);

        self::assertSame($expected_formatted_value, $goal_formatter->format('current_value'));
    }

    public static function description_formatting_data_provider(): array {
        return [
            [null, null],
            ['', null],
            ['not looking like json', null],
            // empty json doc
            ['{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[]}]}', null],
            // Valid description
            ['{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"test description"}]}]}', '<p>test description</p>'],
            // XSS attempt
            [
                '{"type":"doc","content":[{"type":"paragraph","attrs":{},"content":[{"type":"text","text":"testemoji "},{"type":"emoji","attrs":{"shortcode":"1F60A<img src=\'x\' onerror=\'alert(123)\'>>"}}]}]}',
                '<p>testemoji <span>&#x1F60Aimgsrcxonerroralert123;</span></p>',
            ],
        ];
    }

    /**
     * @dataProvider description_formatting_data_provider
     * @param $db_value
     * @param $expected_formatted_value
     * @return void
     */
    public function test_description($db_value, $expected_formatted_value): void {
        $user = static::getDataGenerator()->create_user();
        static::setUser($user);

        $goal = goal_generator::instance()->create_goal();
        /** @var goal_entity $goal_entity */
        $goal_entity = $goal->get_entity_copy();

        $goal_entity->description = $db_value;
        $goal_entity->save();

        $goal_formatter = new goal_formatter($goal->refresh(), $goal->context);

        static::assertSame(
            $expected_formatted_value,
            $goal_formatter->format('description', format::FORMAT_HTML)
        );
    }
}