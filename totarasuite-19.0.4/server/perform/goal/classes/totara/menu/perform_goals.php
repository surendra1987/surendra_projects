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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\totara\menu;

use core\entity\user;
use moodle_url;
use perform_goal\interactor\goal_interactor;
use totara_core\advanced_feature;
use totara_core\totara\menu\item as menu_item;
use totara_flavour\helper;

class perform_goals extends menu_item {

    protected const BASE_URL = '/perform/goal/index.php';

    /**
     * @param array $params
     * @return moodle_url
     */
    public static function get_base_url(array $params = []): moodle_url {
        return new moodle_url(self::BASE_URL, $params);
    }

    /**
     * @inheritDoc
     */
    protected function get_default_title() {
        return get_string('goals_overview_menu', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    protected function get_default_url() {
        return self::BASE_URL;
    }

    /**
     * @inheritDoc
     */
    public function get_default_sortorder() {
        return 50040;
    }

    /**
     * @inheritDoc
     */
    protected function check_visibility() {
        if (!isloggedin() or isguestuser()) {
            return false;
        }

        static $cache = null;

        if (isset($cache)) {
            return $cache;
        }

        $user = user::logged_in();

        $interactor = goal_interactor::for_user($user);
        if ($interactor->can_view_personal_goals()) {
            $cache = true;
        } else {
            $cache = false;
        }
        return $cache;
    }

    /**
     * Is this menu item completely disabled?
     *
     * @return bool
     */
    public function is_disabled() {
        $feature = 'perform_goals';
        $flavour = helper::get_active_flavour_definition();

        return $flavour && !helper::is_allowed($flavour, $feature)
            ? true
            : advanced_feature::is_disabled($feature);
    }

    /**
     * @inheritDoc
     */
    protected function get_default_parent() {
        return '\totara_core\totara\menu\perform';
    }

    /**
     * @inheritDoc
     */
    public function get_incompatible_preset_rules(): array {
        return ['can_view_my_goals'];
    }
}
