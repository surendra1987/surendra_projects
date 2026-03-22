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
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package core
 */

use totara_core\advanced_feature;
use perform_goal\settings_helper;
use mod_perform\models\activity\helpers\participation_sync_settings_helper;

defined('MOODLE_INTERNAL') || die();

/**
 * @var admin_root $ADMIN
 * @var bool $hassiteconfig
 */

if ($hassiteconfig) {

    /** @var admin_settingpage $adv_features_perform */
    $adv_features_perform = $ADMIN->locate('advancedfeatures_perform');
    if (!$adv_features_perform) {
        return;
    }

    // When the mod_perform plugin is uninstalled, then it can't initialise its settings as the lang strings are no longer there.
    $mod_perform_installed = core_component::get_component_directory('mod_perform') !== null;

    if ($mod_perform_installed) {
        $adv_features_perform->add(
            new totara_core_admin_setting_feature_checkbox(
                'enableperformance_activities',
                new lang_string('enable_performance_activities', 'mod_perform'),
                new lang_string('enable_performance_activities_description', 'mod_perform'),
                advanced_feature::DISABLED
            )
        );

        $adv_features_perform->add(
            new admin_setting_configcheckbox(
                'perform_hide_suspended_users',
                new lang_string('perform_hide_suspended_users', 'mod_perform'),
                new lang_string('perform_hide_suspended_users_description', 'mod_perform'),
                0
            )
        );

        $adv_features_perform->add(
            new admin_setting_configcheckbox(
                'perform_close_suspended_user_instances',
                new lang_string('perform_close_suspended_user_instances', 'mod_perform'),
                new lang_string('perform_close_suspended_user_instances_description', 'mod_perform'),
                0
            )
        );

        $adv_features_perform->add(
            new admin_setting_configcheckbox(
                'perform_hide_incomplete_responses_closed_instances',
                new lang_string('perform_hide_incomplete_responses_closed_instances', 'mod_perform'),
                new lang_string('perform_hide_incomplete_responses_closed_instances_description', 'mod_perform'),
                0
            )
        );

        $adv_features_perform->add(
            new admin_setting_configcheckbox(
                'perform_sync_participant_instance_creation',
                new lang_string('perform_sync_participant_instance_role_change_creation', 'mod_perform'),
                new lang_string('perform_sync_participant_instance_creation_description', 'mod_perform'),
                0
            )
        );

        $participant_instance_closure_setting_configselect = new admin_setting_configselect(
            'perform_sync_participant_instance_closure',
            new lang_string('perform_sync_participant_instance_role_change_closure', 'mod_perform'),
            new lang_string('perform_sync_participant_instance_role_change_closure_description_with_view_only', 'mod_perform'),
            participation_sync_settings_helper::get_participant_instance_closure_default(),
            participation_sync_settings_helper::get_participant_instance_closure_options()
        );
        $adv_features_perform->add($participant_instance_closure_setting_configselect);
    }

    $goals_choice_admin_setting_configselect = new admin_setting_configselect(
        'perform_goal/goals_choice',
        new lang_string('enable_perform_goals', 'perform_goal'),
        new lang_string('enable_perform_goals_description', 'perform_goal'),
        settings_helper::TOTARA_GOALS,
        settings_helper::get_options()
    );
    $goals_choice_admin_setting_configselect->set_updatedcallback([settings_helper::class, 'advanced_features_callback']);
    $adv_features_perform->add($goals_choice_admin_setting_configselect);

    $adv_features_perform->add(
        new totara_core_admin_setting_feature_checkbox(
            'enablecompetency_assignment',
            new lang_string('enablecompetency_assignment', 'totara_competency'),
            new lang_string('enablecompetency_assignment_desc', 'totara_competency'),
            advanced_feature::DISABLED
        )
    );

    $adv_features_perform->add(
        new admin_setting_configcheckbox(
            'dynamicappraisals',
            new lang_string('dynamicappraisals', 'totara_core'),
            new lang_string('configdynamicappraisals', 'totara_core'),
            1
        )
    );

    $adv_features_perform->add(
        new admin_setting_configcheckbox(
            'dynamicappraisalsautoprogress',
            new lang_string('dynamicappraisalsautoprogress', 'totara_core'),
            new lang_string('configdynamicappraisalsautoprogress', 'totara_core'),
            1
        )
    );

    $adv_features_perform->add(
        new totara_core_admin_setting_feature_checkbox(
            'enableappraisals',
            new lang_string('enablelegacyappraisals', 'totara_appraisal'),
            new lang_string('configenablelegacyappraisals', 'totara_appraisal'),
            advanced_feature::DISABLED
        )
    );

    if ($mod_perform_installed) {
        $adv_features_perform->add(
            new admin_setting_configcheckbox(
                'showhistoricactivities',
                new lang_string('showhistoricactivities', 'mod_perform'),
                new lang_string('configshowhistoricactivities', 'mod_perform'),
                '0'
            )
        );
    }

    $adv_features_perform->add(
        new totara_core_admin_setting_feature_checkbox(
            'enablefeedback360',
            new lang_string('enablelegacyfeedback360', 'totara_feedback360'),
            new lang_string('configenablelegacyfeedback360', 'totara_feedback360'),
            advanced_feature::DISABLED
        )
    );
}