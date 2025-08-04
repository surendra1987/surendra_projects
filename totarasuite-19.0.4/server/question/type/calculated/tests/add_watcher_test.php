<?php
/**
 * This file is part of Totara Learn
 *
 * This file is part of Totara Learn
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as activateed by
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
 * @package qtype_calculated
 * @category test
 */

use core_phpunit\testcase;
use core_question\hook\question_can_manage_type;
use qtype_calculated\watcher\add;

global $CFG;
require_once $CFG->dirroot . '/question/engine/tests/helpers.php';

class qtype_calculated_add_watcher_test extends testcase {
    public static function td_watcher_calculated(): array {
        return [
            'calculated/with capability' => ['calculated', true],
            'calculated/without capability' => ['calculated', false]
        ];
    }

    public static function td_watcher_calculatedmulti(): array {
        return [
            'calculatedmulti/with capability' => ['calculatedmulti', true],
            'calculatedmulti/without capability' => ['calculatedmulti', false]
        ];
    }

    public static function td_watcher_calculatedsimple(): array {
        return [
            'calculatedsimple/with capability' => ['calculatedsimple', true],
            'calculatedsimple/without capability' => ['calculatedsimple', false]
        ];
    }

    public static function td_watcher_non_calculated(): array {
        return [
            'non_calculated/with capability' => ['numerical', true, 'pi'],
            'non_calculated/without capability' => ['numerical', true, 'pi']
        ];
    }

    /**
     * @dataProvider td_watcher_calculated
     * @dataProvider td_watcher_calculatedmulti
     * @dataProvider td_watcher_calculatedsimple
     * @dataProvider td_watcher_non_calculated
     */
    public function test_watcher(
        string $question_type,
        bool $capability_allowed
    ): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();

        $context = context_system::instance();
        if ($capability_allowed) {
            $role_id = $generator->create_role();
            assign_capability(
                'moodle/question:managecalculated', CAP_ALLOW, $role_id, $context
            );
            role_assign($role_id, $user->id, $context);
        }

        $this->setUser($user);

        // Instantiate the question here instead of using a closure.
        $question = test_question_maker::make_question($question_type);

        $hook = new question_can_manage_type($question->qtype, $context);
        add::evaluate($hook);
        self::assertEquals($capability_allowed, $hook->is_allowed());
    }

}
