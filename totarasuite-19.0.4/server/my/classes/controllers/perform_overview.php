<?php
/*
 * This file is part of Totara Learn
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package core_my
 */

namespace core_my\controllers;

use context;
use context_user;
use core\entity\user;
use core_my\perform_overview_util as util;
use core_user\profile\card_display;
use core_user\profile\display_setting;
use core_user\profile\null_card_display_field;
use core_user\profile\user_field_resolver;
use moodle_url;
use mod_perform\controllers\activity\user_activities;
use mod_perform\models\activity\helpers\manual_participant_helper;
use mod_perform\models\activity\helpers\participant_instance_helper;
use mod_perform\views\override_nav_breadcrumbs;
use perform_goal\controllers\goals_overview;
use totara_competency\controllers\profile\user_assignment;
use totara_competency\controllers\profile\index as user_profile;
use totara_mvc\controller;
use totara_mvc\tui_view;

/**
 * Perform overview controller
 *
 * @package my\controllers
 */
class perform_overview extends controller {

    /**
     * Get the user id for the user that this overview is for.
     * If it's not provided as 'user_id' parameter, it's the logged-in user.
     *
     * @return int
     */
    private function get_target_user_id(): int {
        return $this->get_optional_param('user_id', null, PARAM_INT) ?: user::logged_in()->id;
    }

    /**
     * @return void
     */
    protected function authorize(): void {
        require_login(null, false);

        if (!util::has_any_permission($this->get_target_user_id())) {
            print_error('no_permission', 'core_my');
        }
    }

    /**
     * @return string
     */
    public static function get_base_url(): string {
        return '/my/perform_overview.php';
    }

    /**
     * The URL for this page, with params.
     *
     * @param array $params
     * @return moodle_url
     */
    final public static function get_url(array $params = []): moodle_url {
        return new moodle_url(static::get_base_url(), $params);
    }

    /**
     * @inheritDoc
     */
    public function action() {
        $target_user_id = $this->get_target_user_id();
        $target_user = new user($target_user_id);

        $current_user_id = (int)user::logged_in()->id;
        $goals_url = goals_overview::create_url(
            null,
            $target_user_id === $current_user_id ? null : $target_user_id
        );

        $this->set_url(static::get_url(['user_id' => $target_user_id]));

        // Add breadcrumbs.
        $this->add_breadcrumbs();

        $this->get_page()->set_totara_menu_selected('\totara_core\totara\menu\my_overview');

        $pending_subject_instances_count = manual_participant_helper::for_user($target_user_id)->get_pending_subject_instances_count();
        [$activities_about_others_count, $activities_about_others_overdue_count] = $this->activities_requiring_action($target_user);
        $user_field_resolver = user_field_resolver::from_id($target_user_id);

        $props = [
            'current-user-id' => $current_user_id,
            'activities-about-others-count' => $activities_about_others_count,
            'activities-about-others-overdue-count' => $activities_about_others_overdue_count,
            'activities-enabled' => util::can_view_activities_overview_for($target_user_id),
            'activities-url' => (string) user_activities::get_url(),
            'competencies-assign-url' => (string) user_assignment::get_url(),
            'competencies-enabled' => util::can_view_competencies_overview_for($target_user_id),
            'competencies-url' => (string) user_profile::get_url(['user_id' => (string) $target_user_id]),
            'goals-enabled' => util::can_view_perform_goals_overview_for($target_user_id),
            'goals-url' => $goals_url->out(),
            'period-options' => util::get_overview_period_options(),
            'require-manual-participants-count' => $pending_subject_instances_count,
            'user' => [
                'id' => $target_user_id,
                'fullname' => $target_user->fullname,
                'card_display' => [
                    'display_fields' => $this->get_card_display_fields_for_perform_overview($user_field_resolver),
                    'profile_url' => $user_field_resolver->get_field_value('profileurl'),
                ],
            ]
        ];

        if (display_setting::display_user_picture()) {
            $props['user']['card_display']['profile_picture_url'] = $user_field_resolver->get_field_value('profileimageurl');
            $props['user']['card_display']['profile_picture_alt'] = $user_field_resolver->get_field_value('profileimagealt');
        }

        return tui_view::create('core_my/pages/PerformOverview', $props)
            ->set_title(get_string('overview_page_title', 'core_my'))
            ->add_override(new override_nav_breadcrumbs());
    }

    /**
     * @inheritDoc
     */
    protected function setup_context(): context {
        return context_user::instance($this->get_target_user_id());
    }

    /**
     * @param user_field_resolver $user_field_resolver
     * @return array
     */
    private function get_card_display_fields_for_perform_overview(user_field_resolver $user_field_resolver): array {
        $fields = card_display::create($user_field_resolver)->get_card_display_fields();
        $results = [];
        foreach ($fields as $field) {
            if ($field instanceof null_card_display_field) {
                continue;
            }
            $results[] = [
                'value' => $field->get_field_value(),
                'associate_url' => $field->get_field_url(),
                'label' => $field->get_field_label(),
            ];
        }
        return $results;
    }

    /**
     * @param user $user
     * @return array
     */
    private function activities_requiring_action(user $user): array {
        $pending_participant_instances = participant_instance_helper::action_required_for($user);
        $overdue_participant_instances = $pending_participant_instances->filter('is_overdue', true);
        return [$pending_participant_instances->count(), $overdue_participant_instances->count()];
    }

    /**
     * Add breadcrumb navigation if looking at someone else's overview.
     *
     * @return void
     */
    private function add_breadcrumbs(): void {
        /** @var user $target_user */
        $target_user = user::repository()->find($this->get_target_user_id());
        if ($target_user->is_logged_in()) {
            // Remove breadcrumbs when looking at your own overview.
            $this->get_page()->navbar->ignore_active();
        } else {
            // Add breadcrumbs when looking at someone else's overview.
            $this->get_page()->navigation->extend_for_user($target_user->to_record());
            $this->get_page()->navbar->add(get_string('menu_title_my_overview', 'core_my'));
        }
    }
}
