<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

use core_container\hook\module_supported_in_container;
use core_enrol\hook\enrol_instance_extra_settings_definition;
use core_enrol\hook\enrol_instance_extra_settings_save;
use core_enrol\hook\enrol_instance_extra_settings_validation;
use core_role\hook\core_role_potential_assignees_container;
use core_user\hook\allow_view_profile;
use core_user\hook\allow_view_profile_field;
use editor_weka\hook\find_context;
use editor_weka\hook\search_users_by_pattern;
use mod_approval\watcher\activity;
use mod_approval\watcher\core_role_container;
use mod_approval\watcher\assignment_deletion_checker;
use mod_approval\watcher\userdata_label;
use mod_approval\watcher\core_user;
use mod_approval\watcher\editor_weka_watcher;
use mod_approval\watcher\enrol_instance_extra_settings;
use mod_approval\watcher\performance;
use totara_cohort\hook\pre_delete_cohort_check;
use totara_core\hook\navigation\global_navigation_for_ajax_intialise;
use totara_core\hook\navigation\global_navigation_initialise;
use totara_core\hook\navigation\settings_navigation_initialise;
use totara_hierarchy\hook\pre_delete_framework_check;
use totara_hierarchy\hook\pre_delete_item_check;
use totara_userdata\hook\userdata_normalise_label;

$watchers = [
    [
        'hookname' => module_supported_in_container::class,
        'callback' => [activity::class, 'filter_module'],
    ],
    [
        'hookname' => allow_view_profile_field::class,
        'callback' => [core_user::class, 'allow_view_profile_field'],
    ],
    [
        'hookname' => userdata_normalise_label::class,
        'callback' => [userdata_label::class, 'normalise'],
    ],
    [
        'hookname' => find_context::class,
        'callback' => [editor_weka_watcher::class, 'set_context'],
    ],
    [
        'hookname' => search_users_by_pattern::class,
        'callback' => [editor_weka_watcher::class, 'on_search_users']
    ],
    [
        'hookname' => allow_view_profile::class,
        'callback' => [core_user::class, 'handle_allow_view_profile'],
    ],
    [
        'hookname' => core_role_potential_assignees_container::class,
        'callback' => [core_role_container::class, 'get_potential_assignees']
    ],
    [
        'hookname' => global_navigation_for_ajax_intialise::class,
        'callback' => [performance::class, 'override_global_navigation_for_ajax']
    ],
    [
        'hookname' => global_navigation_initialise::class,
        'callback' => [performance::class, 'override_global_navigation']
    ],
    [
        'hookname' => settings_navigation_initialise::class,
        'callback' => [performance::class, 'override_settings_navigation']
    ],
    [
        'hookname' => pre_delete_framework_check::class,
        'callback' => [assignment_deletion_checker::class, 'can_hierarchy_be_deleted']
    ],
    [
        'hookname' => pre_delete_item_check::class,
        'callback' => [assignment_deletion_checker::class, 'can_hierarchy_item_be_deleted']
    ],
    [
        'hookname' => pre_delete_cohort_check::class,
        'callback' => [assignment_deletion_checker::class, 'can_cohort_be_deleted']
    ],
    [
        'hookname' => enrol_instance_extra_settings_validation::class,
        'callback' => [enrol_instance_extra_settings::class, 'validation'],
    ],
    [
        'hookname' => enrol_instance_extra_settings_save::class,
        'callback' => [enrol_instance_extra_settings::class, 'save'],
    ],
    [
        'hookname' => enrol_instance_extra_settings_definition::class,
        'callback' => [enrol_instance_extra_settings::class, 'definition'],
    ]
];
