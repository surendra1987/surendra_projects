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
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @package mod_approval
 */

namespace mod_approval\controllers\application;

use cache;
use container_approval\approval as approval_container;
use context;
use core\task\manager;
use mod_approval\data_provider\application\applications_for_others;
use mod_approval\data_provider\application\capability_map\capability_map_controller;
use mod_approval\data_provider\application\role_map\role_map_controller;
use mod_approval\task\role_map_regenerate_all;
use mod_approval\task\role_map_regenerate_all_adhoc;
use mod_approval\totara\menu\dashboard as dashboard_menu;
use totara_mvc\tui_view;
use core\entity\user;

/**
 * The application dashboard.
 */
class dashboard extends base {

    /**
     * @inheritDoc
     */
    public function setup_context(): context {
        return approval_container::get_default_category_context();
    }

    /**
     * @inheritDoc
     */
    public function process(string $action = '') {
        $role_cache = cache::make('mod_approval', 'role_map');
        $capability_cache = cache::make('mod_approval', 'capability_map');
        $capability_maps_were_reset_this_session = $capability_cache->get('maps_reset');
        $user = user::logged_in();
        $role_maps_individual_was_reset = $role_cache->get('changed_for_' . $user->id);

        // Calculate capability maps only when role maps have been calculated.
        if ($role_cache->get('maps_clean') && (
                $capability_maps_were_reset_this_session == false ||
                $role_maps_individual_was_reset
        )) {
            capability_map_controller::regenerate_all_maps($user->id);
            $capability_cache->set('maps_reset', 1);
            if ($role_maps_individual_was_reset) {
                $role_cache->delete('changed_for_' . $user->id);
            }
        }
        parent::process($action);
    }

    /**
     * @return tui_view
     */
    public function action(): tui_view {
        $this->set_url(self::get_url());
        $this->get_page()->set_totara_menu_selected(dashboard_menu::class);

        $rebuild_role_maps = $this->get_optional_param('rebuild', false, PARAM_BOOL);
        if ($this->can_rebuild_role_maps() && $rebuild_role_maps) {
            // This sets maps_clean to true, so this is really just a first run after install or upgrade,
            // or if an event observer has invalidated the maps and they haven't been regenerated yet.
            manager::queue_adhoc_task(new role_map_regenerate_all_adhoc(), true);
            redirect(self::get_url([
                'notify_type' => 'success',
                'notify' => 'role_map_scheduled_now',
            ]));
        }
        $user = user::logged_in();
        $props = [
            'current-user-id' => $user->id,
            'contextId' => approval_container::get_default_category_context()->id,
            'page-props' => [
                'tabs' => [
                    'my-applications' => true,
                    'applications-from-others' => self::can_view_others_applications($user->id),
                ],
                'new-application-on-behalf' => self::can_create_application_on_behalf($user->id)
            ]
        ];
        $props['page-props']['role-maps'] = $this->get_role_map_info();
        $title = get_string('dashboard_title', 'mod_approval');

        return static::create_tui_view('mod_approval/pages/ApplicationDashboard', $props)
            ->set_title($title);
    }

    /**
     * Get information on the status of role map generation.
     * @return array
     */
    private function get_role_map_info(): array {
        $role_cache = cache::make('mod_approval', 'role_map');
        $can_rebuild = $this->can_rebuild_role_maps();
        $role_maps_info = [
            'clean' => (bool) $role_cache->get('maps_clean'),
            'can-rebuild' => $can_rebuild,
        ];

        // User has ability to re-trigger role map building. i.e admin or check for existing capability
        if ($can_rebuild) {
            // Check if an adhoc task has been scheduled.
            $pending_adhoc_tasks = manager::get_adhoc_tasks(role_map_regenerate_all_adhoc::class);
            if (count($pending_adhoc_tasks) > 0) {
                $role_maps_info['task-notice'] = get_string('information_not_available_reason_build_in_queue', 'mod_approval');
                return $role_maps_info;
            }

            $task = manager::get_scheduled_task(role_map_regenerate_all::class);
            if (!$task) {
                // Something's off, can't find the scheduled task
                $role_maps_info['task-notice'] = get_string('task_role_map_regenerate_all_not_found', 'mod_approval');
                return $role_maps_info;
            }
            $timestamp = $task->get_next_scheduled_time();
            $role_maps_info['next-runtime'] = get_string(
                'information_not_available_reason_for_admin',
                'mod_approval',
                userdate($timestamp, get_string('strftimedatetimeon', 'langconfig'))
            );
        }

        return $role_maps_info;
    }

    /**
     * Check on who can rebuild the role maps
     * @return bool
     */
    private function can_rebuild_role_maps(): bool {
        return has_capability('moodle/site:config', \context_system::instance());
    }

    /**
     * @param integer $user_id
     * @return boolean
     */
    private static function can_view_others_applications(int $user_id): bool {
        $provider = new applications_for_others($user_id);
        return $provider->has_any_capability();
    }

    /**
     * @inheritDoc
     */
    public static function get_base_url(): string {
        return '/mod/approval/application/index.php';
    }

    /**
     * Can create an application on behalf.
     * @param int $user_id
     * @return bool
     */
    private static function can_create_application_on_behalf(int $user_id): bool {
        return has_capability_in_any_context('mod/approval:create_application_any', [CONTEXT_SYSTEM, CONTEXT_COURSECAT, CONTEXT_COURSE, CONTEXT_MODULE], $user_id) ||
            has_capability_in_any_context('mod/approval:create_application_user', [CONTEXT_SYSTEM, CONTEXT_USER], $user_id);
    }
}
