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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_mfa
 */

namespace core_mfa\controllers;

use context;
use context_system;
use totara_mvc\controller;
use totara_mvc\tui_view;
use core_mfa\factor_factory;
use core_mfa\data_provider\instance as instance_provider;

class verify extends controller {
    /** @inheritDoc */
    protected $url = '/mfa/verify.php';

    /** @inheritDoc */
    protected $layout = 'pending_login';

    /** @inheritDoc */
    protected function setup_context(): context {
        return context_system::instance();
    }

    /** @inheritDoc */
    public function action() {
        global $USER;

        if (empty($USER->pseudo_id)) {
            redirect(get_login_url());
        }

        $registered_factors = $this->registered_factors($USER->pseudo_id);

        return static::create_tui_view('core_mfa/pages/Verify', [
            'registered-factors' => $registered_factors,
        ])->set_title(get_string('two_factor_verification', 'mfa'));
    }

    /** @inheritDoc */
    protected function require_login(): bool {
        return false;
    }

    /**
     * Get registered factors.
     *
     * @param int $user_id
     * @return array
     */
    protected function registered_factors($user_id): array {
        $factor_factory = new factor_factory();
        $instances = instance_provider::get_user_instances($user_id);
        $factors = [];

        foreach ($instances as $instance) {
            $type = $instance->type;
            if (!isset($factors[$type])) {
                $factor = $factor_factory->get($type);
                if (!$factor->has_verify_ui()) {
                    continue;
                }
                $factors[$type] = (object)[
                    'factor' => $factor,
                    'id' => $type,
                    'name' => $factor->get_display_name(),
                    'description' => $factor->get_description(),
                    'verifyTitle' => $factor->get_verify_title(),
                    'instances' => [],
                ];
            }
            $factors[$type]->instances[] = $instance;
        }

        foreach ($factors as $item) {
            $item->data = $item->factor->get_verify_data($item->instances);
            unset($item->factor);
            unset($item->instances);
        }

        return array_values($factors);
    }
}
