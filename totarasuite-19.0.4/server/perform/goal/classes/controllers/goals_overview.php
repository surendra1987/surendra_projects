<?php
/*
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
 * @author Murali Nair <murali.nair@totara.com>
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package perform_goal
 */

namespace perform_goal\controllers;

use context;
use context_user;
use core\entity\user;
use core_user\profile\card_display;
use core_user\profile\display_setting;
use core_user\profile\null_card_display_field;
use core_user\profile\user_field_resolver;
use moodle_url;
use perform_goal\constants;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\status\status_helper;
use totara_mvc\tui_view;

/*
 * Controller for goals landing page.
 */
class goals_overview extends controller_base {
    // Page related url and parameters.
    public const BASE_URL = '/perform/goal/index.php';

    // Page parameter.
    public const PARAM_GOAL_ID = 'goal';

    /**
     * Constructs this page's URL given its input parameters.
     */
    public static function create_url(
        ?int $goal_id,
        ?int $target_user_id
    ): moodle_url {
        $args = [];
        if ($goal_id) {
            $args[self::PARAM_GOAL_ID] = $goal_id;
        }

        if ($target_user_id) {
            $args[self::PARAM_TARGET_USER_ID] = $target_user_id;
        }

        return new moodle_url(self::BASE_URL, $args);
    }

    /**
     * Constructor.
     */
    public function __construct() {
        require_login(null, false);
        parent::__construct(goal_interactor::for_user(user::logged_in()));
    }

    /**
     * @param user_field_resolver $user_field_resolver
     * @return array
     */
    private function get_card_display_fields_for_goal_overview(user_field_resolver $user_field_resolver): array {
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
     * @inheritDoc
     */
    public function action(): tui_view {
        $title = $this->target_user->id === $this->user->id
            ? get_string('goals_overview_title', 'perform_goal')
            : get_string(
                'goals_overview_title_for_user',
                'perform_goal',
                $this->target_user->fullname
            );

        $url = self::create_url(
            null,
            $this->target_user->id === $this->user->id ? null : $this->target_user->id
        );
        $this->set_url($url);

        $props = $this->properties();

        return tui_view::create('perform_goal/pages/Goals', $props)
            ->set_title($title);
    }

    /**
     * @inheritDoc
     */
    protected function setup_context(): context {
        return context_user::instance($this->user->id);
    }

    /**
     * Returns the properties to use when generating the UI page.
     *
     * @return array<string,mixed> these properties:
     *         - int user_id: logged-in user id
     *         - array<string,mixed> goals: list of goals
     *         - array<string,mixed> permissions: goals overview permissions
     */
    public function properties(): array {
        $target_user_name = $this->target_user->fullname;
        $user_field_resolver = user_field_resolver::from_id($this->target_user->id);

        $props = [
            'user' => [
                'id' => $this->target_user->id,
                'fullname' => $target_user_name,
                'card_display' => [
                    'display_fields' => $this->get_card_display_fields_for_goal_overview($user_field_resolver),
                    'profile_url' => $user_field_resolver->get_field_value('profileurl'),
                ]
            ],
            'user-id' => $this->target_user->id,
            'current-user-id' => $this->user->id,
            'open-goal' => $this->get_optional_param('goal', null, PARAM_NOTAGS),
            'permissions' => $this->permission_properties(),
            'available-goal-statuses' => $this->get_available_goal_statuses(),
            'available-sort-options' => $this->get_available_sort_options(),
            'default-sort-option' => 'created_at',
        ];

        if (display_setting::display_user_picture()) {
            $props['user']['card_display']['profile_picture_url'] = $user_field_resolver->get_field_value('profileimageurl');
            $props['user']['card_display']['profile_picture_alt'] = $user_field_resolver->get_field_value('profileimagealt');
        }

        return $props;
    }

    /**
     * @return array
     */
    private function get_available_sort_options(): array {
        return array_map(
            static fn (string $sort_option): array => [
                'id' => $sort_option,
                'label' => get_string('sort_option_label_' . $sort_option, 'perform_goal')
            ],
            array_keys(constants::EXPOSED_SORT_OPTIONS)
        );
    }

    /**
     * @return array
     */
    private function get_available_goal_statuses(): array {
        $result = [];
        foreach (status_helper::all_status_labels() as $status_code => $status_label) {
            $result[] = ['id' => $status_code, 'label' => $status_label];
        }
        return $result;
    }

    /**
     * Returns the permission properties for this controller. Note this has the
     * same structure as the graphql permissions type.
     *
     * @return array<string,mixed> these properties:
     *         bool can_view_personal_goals: if the current user can view a goal
     *         bool can_create_personal_goal: if the current user can create a goal.
     */
    protected function permission_properties(): array {
        return [
            'can_view_personal_goals' => $this->interactor->can_view_personal_goals(),
            'can_create_personal_goal' => $this->interactor->can_create_personal_goal(),
        ];
    }
}
