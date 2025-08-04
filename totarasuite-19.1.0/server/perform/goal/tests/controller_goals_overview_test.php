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
 * This program is distributed in the hope that it will be use`ful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

use core\date_format;
use core\entity\user;
use core\format;
use core\orm\query\builder;
use core\testing\generator as core_generator;
use core\webapi\formatter\field\date_field_formatter;
use core\webapi\formatter\field\text_field_formatter;
use perform_goal\controllers\goals_overview;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal;
use perform_goal\model\status\status_helper;
use perform_goal\model\target_type\date;
use perform_goal\testing\generator as generator;
use perform_goal\testing\goal_generator_config;

require_once(__DIR__.'/perform_goal_testcase.php');

/**
 * @group perform_goal
 */
class perform_goal_controller_goals_overview_test extends perform_goal_testcase {

    public function test_context(): void {
        $user = new user($this->getDataGenerator()->create_user()->id);
        $this->setUser($user);
        self::assertSame(context_user::instance($user->id), (new goals_overview())->get_context());
    }

    public function test_action_as_admin(): void {
        $this->setAdminUser();
        $tui_view = (new goals_overview())->action();
        $this->assertEquals('Your goals', $tui_view->get_title());
    }

    public function test_action_as_random_user(): void {
        $user = new user($this->getDataGenerator()->create_user()->id);
        $this->setUser($user);
        $tui_view = (new goals_overview())->action();
        $this->assertEquals('Your goals', $tui_view->get_title());
    }

    public function test_action_as_guest_user(): void {
        $this->setGuestUser();

        try {
            (new goals_overview())->action();
            $this->fail("Permission check should have failed but didn't");
        } catch (moodle_exception $exception) {
            $this->assertEquals('Access denied', $exception->getMessage());
        }
    }

    public function test_action_as_user_without_capability(): void {
        $user = new user($this->getDataGenerator()->create_user()->id);
        $this->setUser($user);

        // Remove the view personal goals cap.
        $user_role = builder::table('role')->where('shortname', 'user')->one();
        assign_capability('perform/goal:viewownpersonalgoals', CAP_PREVENT, $user_role->id, context_system::instance(), true);
        try {
            (new goals_overview())->action();
            $this->fail("Permission check should have failed but didn't");
        } catch (moodle_exception $exception) {
            $this->assertEquals('Access denied', $exception->getMessage());
        }
    }

    public function test_properties(): void {
        [$subject_user_id, $exp_goal1, $exp_goal2, $exp_goal3, $exp_interactor] = $this->setup_env();
        $subject_user = new user($subject_user_id);

        self::setUser($subject_user_id);

        $_POST['userid'] = $subject_user_id;
        $_POST['goal'] = 999;

        $props = (new goals_overview())->properties();

        $expected_props = [
            'user',
            'user-id',
            'current-user-id',
            'open-goal',
            'permissions',
            'available-goal-statuses',
            'available-sort-options',
            'default-sort-option',
        ];
        self::assertEqualsCanonicalizing($expected_props, array_keys($props));

        self::assertEquals($subject_user_id, $props['user']['id']);
        self::assertEquals($subject_user->fullname, $props['user']['fullname']);
        // Don't need to check details of card display, they should be tested elsewhere. Just check it's there.
        self::assertArrayHasKey('card_display', $props['user']);
        self::assertArrayHasKey('display_fields', $props['user']['card_display']);
        self::assertArrayHasKey('profile_url', $props['user']['card_display']);
        self::assertEquals($subject_user_id, $props['user-id']);
        self::assertEquals($subject_user_id, $props['current-user-id']);
        self::assertEquals(999, $props['open-goal']);
        self::assert_user_goal_permissions($exp_interactor, (object)$props['permissions']);
        self::assertEquals([
            ['id' => 'created_at', 'label' => 'Creation date'],
            ['id' => 'target_date', 'label' => 'Due date'],
            ['id' => 'most_complete', 'label' => 'Most complete'],
            ['id' => 'least_complete', 'label' => 'Least complete'],
        ], $props['available-sort-options']);
        self::assertEquals([
            ['id' => 'not_started', 'label' => 'Not started'],
            ['id' => 'in_progress', 'label' => 'In progress'],
            ['id' => 'completed', 'label' => 'Completed'],
            ['id' => 'cancelled', 'label' => 'Cancelled'],
        ], $props['available-goal-statuses']);
    }

    /**
     * Checks if the goal properties are correct
     *
     * @param goal $expected expected goal.
     * @param stdClass $actual actual goal properties.
     */
    private static function assert_goal_properties(
        goal $expected,
        stdClass $actual
    ): void {
        self::assertEquals(
            $expected->assignment_type,
            $actual->assignment_type,
            'wrong assignment type'
        );

        self::assertEquals(
            format_float($expected->current_value, 5, false, true),
            $actual->current_value,
            'wrong current value'
        );

        $html_fmt = (new text_field_formatter(format::FORMAT_HTML, $expected->context))
            ->set_pluginfile_url_options(
                $expected->context, 'perform_goal', 'description', $expected->id
            );

        self::assertEquals(
            $html_fmt->format($expected->description),
            $actual->description,
            'wrong description'
        );

        self::assertEquals($expected->id, $actual->id, 'wrong goal id');
        self::assertEquals($expected->name, $actual->name, 'wrong name');
        self::assertEquals(
            self::unpack_status($expected->status),
            $actual->status,
            'wrong status'
        );

        self::assertEquals(
            format_float($expected->target_value, 5, false, true),
            $actual->target_value,
            'wrong target value'
        );

        $date_fmt = new date_field_formatter(
            date_format::FORMAT_DATELONG, $expected->context
        );

        $date_fields = [
            'start_date',
            'target_date',
            'updated_at'
        ];

        foreach ($date_fields as $field) {
            self::assertEquals(
                $date_fmt->format($expected->$field),
                $actual->$field,
                "wrong $field value"
            );
        }
    }

    /**
     * Generates test data.
     *
     * @return mixed[] (goal, goal raw data, interactor) tuple.
     */
    private function setup_env(): array {
        $this->setAdminUser();

        $core_generator  = core_generator::instance();
        $owner_id = $core_generator->create_user()->id;
        $subject_id = $core_generator->create_user()->id;
        $context = context_user::instance($subject_id);

        self::assign_as_staff_manager($owner_id, $context);

        $cfg = [
            'description' => self::jsondoc_description(
                '<h1>This is a <strong>test</strong> description</h1>'
            ),
            'owner_id' => $owner_id,
            'user_id' => $subject_id,
            'target_type' => date::get_type(),
            'context' => $context,
        ];

        $goal1 = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));
        $goal3 = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        $cfg['user_id'] = $core_generator->create_user()->id;
        $goal2 = generator::instance()
            ->create_goal(goal_generator_config::new($cfg));

        return [
            $subject_id,
            $goal1,
            $goal2,
            $goal3,
            goal_interactor::from_goal($goal1)
        ];
    }
}
