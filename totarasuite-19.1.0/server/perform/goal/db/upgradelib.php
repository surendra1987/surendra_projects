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
 * @author Oleg Demeshev <oleg.demeshev@totara.com>
 * @package perform_goal
 */

use core\orm\query\builder;
use hierarchy_goal\entity\company_goal as company_goal_entity;
use hierarchy_goal\entity\personal_goal as personal_goal_entity;
use perform_goal\entity\goal_category as goal_category_entity;
use perform_goal\model\goal_category;
use perform_goal\settings_helper;
use totara_flavour\helper;


function perform_goal_upgrade_admin_subsystems_perform_setting() {

    $goal_exist = builder::table(personal_goal_entity::TABLE)->exists()
        || builder::table(company_goal_entity::TABLE)->exists();

    // if there are legacy records & legacy goals is enabled
    if ($goal_exist && settings_helper::is_legacy_goals_enabled()) {
        // it is already set to 'legacy goals enabled', so disable perform goals
        settings_helper::disable_perform_goals();
        set_config('goals_choice', settings_helper::LEGACY_GOALS, 'perform_goal');
    } else if ($goal_exist && settings_helper::is_legacy_goals_disabled()) {
        // it is already set to 'legacy goals disabled', so disable perform goals
        settings_helper::disable_perform_goals();
        set_config('goals_choice', settings_helper::NO_GOALS, 'perform_goal');
    } else if (!$goal_exist) {
        settings_helper::enable_perform_goals();
        settings_helper::disable_legacy_goals();
        set_config('goals_choice', settings_helper::TOTARA_GOALS, 'perform_goal');
    }
}

/**
 * Creates the initial basic goal_category.
 *
 * @return int
 */
function perform_goal_create_basic_goal_category(): int {
    $basic_category = goal_category_entity::repository()->where('id_number', '=', 'system-basic')->one();
    if (!$basic_category) {
        $basic_category = goal_category::create('basic', 'multilang:basic', 'system-basic');
        $basic_category->activate();
    }
    return $basic_category->id;
}

/**
 * Dut to a bug, Perform goals could be enabled even on non-perform sites.
 * Make sure this feature is disabled for non-perform sites.
 *
 * @return void
 */
function perform_goal_fix_goal_settings_for_non_perform_sites(): void {
    $legacy_goals_exist = builder::table(personal_goal_entity::TABLE)->exists()
        || builder::table(company_goal_entity::TABLE)->exists();

    $flavour = helper::get_active_flavour_definition();

    if ($legacy_goals_exist && settings_helper::is_legacy_goals_enabled()) {
        /**
         * Legacy goals exist and they are enabled.
         * Disable perform goals unless flavour allows perform goals.
         */
        if ($flavour && !helper::is_allowed($flavour, 'perform_goals')) {
            settings_helper::disable_perform_goals();
            set_config('goals_choice', settings_helper::LEGACY_GOALS, 'perform_goal');
        }
    } elseif (settings_helper::is_perform_goals_enabled()) {
        /**
         * Perform goals are enabled. Due to a bug, it was enabled even on non-perform sites.
         * So check if the flavour allows perform goals and disable if not.
         * If there is no flavour, do nothing.
         */
        if ($flavour && !helper::is_allowed($flavour, 'perform_goals')) {
            settings_helper::disable_perform_goals();

            // Also disable legacy goals. If it was enabled before, no goals have been created (see above).
            settings_helper::disable_legacy_goals();
            set_config('goals_choice', settings_helper::NO_GOALS, 'perform_goal');
        }
    }
}

/**
 * Configure goal settings initially.
 * It depends on legacy goals being enabled and what flavour is set.
 *
 * @return void
 */
function perform_goal_initialise_goal_settings(): void {
    $flavour = helper::get_active_flavour_definition();
    if (!$flavour) {
        /**
         * This is the case during initial installation. We can't deal with flavour definition then.
         * The flavour code takes care of initialising goal settings in that case.
         */
        return;
    }

    // When we come here, we are installing this component during upgrade.

    $legacy_goals_exist = builder::table(personal_goal_entity::TABLE)->exists()
        || builder::table(company_goal_entity::TABLE)->exists();

    if ($legacy_goals_exist && settings_helper::is_legacy_goals_enabled()) {
        settings_helper::disable_perform_goals();
        set_config('goals_choice', settings_helper::LEGACY_GOALS, 'perform_goal');
    } else {
        // Default when no legacy goals exist (or legacy goals are disabled):
        // Set it to perform goals if flavour allows.
        settings_helper::disable_legacy_goals();
        if (helper::is_allowed($flavour, 'perform_goals')) {
            settings_helper::enable_perform_goals();
            set_config('goals_choice', settings_helper::TOTARA_GOALS, 'perform_goal');
        } else {
            settings_helper::disable_perform_goals();
            set_config('goals_choice', settings_helper::NO_GOALS, 'perform_goal');
        }
    }
}

