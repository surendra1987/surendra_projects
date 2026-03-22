<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package totara_flavour
 */

namespace totara_flavour;

use perform_goal\settings_helper;
use totara_core\advanced_feature;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for flavours without Perform
 */
abstract class flavour_without_perform extends definition {
    public function additional_activation_steps() {
        parent::additional_activation_steps();

        // Have to set these explicitly because they don't exist as admin_setting.
        advanced_feature::disable('perform_goals');
        advanced_feature::disable('goals');
    }

    /**
     * @inheritDoc
     */
    protected function load_enforced_settings() {
        $result = [];

        /**
         * Even for non-Perform sites, legacy goals can be enabled. If they are, make sure the 'goals_choice' setting
         * can still be changed, otherwise admin would not be able to turn goals off.
         *
         * If legacy goals are not enabled on a non-Perform site, enforce the setting, so it can't be changed.
         */
        if (!settings_helper::is_legacy_goals_enabled()) {
            $result['perform_goal'] = [
                'goals_choice' => settings_helper::NO_GOALS,
            ];
        }

        return $result;
    }
}
