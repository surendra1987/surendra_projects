<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

namespace core_mfa\controllers;

use core_mfa\framework;
use core_mfa\mfa;
use totara_mvc\controller;
use totara_mvc\tui_view;
use core_mfa\data_provider\factor as factor_provider;
use core_mfa\data_provider\instance as instance_provider;
use core_mfa\model\instance;

class user_preferences extends controller {
    /** @inheritDoc */
    protected $url = '/mfa/user_preferences.php';

    /** @inheritDoc */
    protected function setup_context(): \context {
        return \context_system::instance();
    }

    /** @inheritDoc */
    public function action() {
        $user_id = $this->currently_logged_in_user()->id;
        return static::create_tui_view('core_mfa/pages/UserPreferences', [
            'factors' => $this->get_factors($user_id),
            'instances' => instance_provider::get_user_instances($user_id)
                ->map(function (instance $x) {
                    return $x->format_for_listing();
                }),
            'authPluginCompatibleWarning' => mfa::has_incompatible_auth_plugins(),
        ])->set_title(get_string('manage_multi_factor_authentication', 'mfa'));
    }

    /**
     * Get registerable factors for frontend.
     *
     * @param int $user_id
     * @return array
     */
    protected function get_factors(int $user_id): array {
        if (!(new framework($user_id))->user_can_register()) {
            return [];
        }
        $factors = factor_provider::get_registerable_factors($user_id);
        $result = [];
        foreach ($factors as $factor) {
            if ($factor->has_register_ui()) {
                $result[] = [
                    'id' => $factor->get_name(),
                    'name' => $factor->get_display_name(),
                    'description' => $factor->get_description(),
                ];
            }
        }
        return $result;
    }
}
