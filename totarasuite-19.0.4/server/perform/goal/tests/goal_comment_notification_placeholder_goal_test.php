<?php
/**
 * This file is part of Totara Perform
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Murali Nair <murali.nair@totaralearning.com>
 * @package perform_goal
 */

use perform_goal\controllers\goals_overview;
use perform_goal\totara_notification\placeholder\goal;

require_once(__DIR__.'/goal_comment_notification_testcase.php');

/**
 * @group perform_goal
 * @group perform_goal_comment
 */
class perform_goal_goal_comment_notification_placeholder_goal_test extends perform_goal_comment_notification_testcase {
    public function test_get_placeholder_name(): void {
        [$goal, ] = $this->create_personal_goal();

        self::assertEquals(
            $goal->name, goal::from_id($goal->id)->do_get(goal::OPTION_NAME)
        );
    }

    public function test_get_placeholder_name_linked(): void {
        [$goal, ] = $this->create_personal_goal();
        $url = new moodle_url(goals_overview::BASE_URL, ['goal' => $goal->id]);

        self::assertStringContainsString(
            $url->out(),
            goal::from_id($goal->id)->do_get(goal::OPTION_NAME_LINKED)
        );
    }
}
