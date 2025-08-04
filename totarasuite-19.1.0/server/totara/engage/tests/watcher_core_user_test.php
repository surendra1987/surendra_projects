<?php
/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author  Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_engage
 */

use container_course\course;
use container_workspace\workspace;
use core_user\hook\allow_view_profile;
use totara_engage\watcher\core_user as core_user_watcher;

defined('MOODLE_INTERNAL') || die;

class totara_engage_watcher_core_user_test extends \core_phpunit\testcase {

    private const ENGAGE_FEATURES = [
        'engage_resources',
        'container_workspace',
        'ml_recommender',
        'totara_msteams',
    ];

    public function test_allow_view_profile_based_on_features() {
        set_config('totara_engage_allow_view_profiles', true);
        $hook = new allow_view_profile(64, 66);

        foreach (self::ENGAGE_FEATURES as $feature) {
            \totara_core\advanced_feature::disable($feature);
        }

        core_user_watcher::handle_allow_view_profile($hook);
        self::assertFalse($hook->has_permission());

        foreach (self::ENGAGE_FEATURES as $feature) {
            \totara_core\advanced_feature::enable($feature);
        }

        core_user_watcher::handle_allow_view_profile($hook);
        self::assertTrue($hook->has_permission());
    }

    public function test_allow_view_profile_permission_already_granted() {
        $hook = new allow_view_profile(64, 66);
        $hook->give_permission();

        // It does not matter that the users are invalid, as permission has been granted already.
        core_user_watcher::handle_allow_view_profile($hook);
        self::assertTrue($hook->has_permission());
    }

    public function test_allow_view_profile_permission_forced_on() {
        set_config('totara_engage_allow_view_profiles', true);
        $hook = new allow_view_profile(64, 66);

        // It does not matter that the users are invalid, as it is forced on.
        core_user_watcher::handle_allow_view_profile($hook);
        self::assertTrue($hook->has_permission());
    }

    public function test_allow_view_profile_permission_forced_off() {
        set_config('totara_engage_allow_view_profiles', false);
        $hook = new allow_view_profile(64, 66);

        // It does not matter that the users are invalid, as it is forced on.
        core_user_watcher::handle_allow_view_profile($hook);
        self::assertFalse($hook->has_permission());
    }

    public function test_allow_view_profile_no_permission_in_course_context(): void {
        set_config('totara_engage_allow_view_profiles', true);

        $course = (object)[
            'id' => 111,
            'containertype' => course::get_type()
        ];
        $hook = new allow_view_profile(64, 66, $course);

        core_user_watcher::handle_allow_view_profile($hook);
        self::assertFalse($hook->has_permission());
    }

    public function test_allow_view_profile_permission_in_workspace_course_context(): void {
        set_config('totara_engage_allow_view_profiles', true);

        $course = (object)[
            'id' => 111,
            'containertype' => workspace::get_type()
        ];
        $hook = new allow_view_profile(64, 66, $course);

        core_user_watcher::handle_allow_view_profile($hook);
        self::assertTrue($hook->has_permission());
    }

    public function test_allow_view_profile_no_permission_in_course_without_container_type(): void {
        set_config('totara_engage_allow_view_profiles', true);

        $course = (object)[
            'id' => 111
        ];
        $hook = new allow_view_profile(64, 66, $course);

        core_user_watcher::handle_allow_view_profile($hook);
        self::assertFalse($hook->has_permission());
    }
}