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
use moodle_exception;
use core_mfa\factor_factory;
use totara_mvc\controller;
use totara_mvc\tui_view;

class register_factor extends controller {
    /** @inheritDoc */
    protected function setup_context(): \context {
        return \context_system::instance();
    }

    /** @inheritDoc */
    protected function authorize(): void {
        $user_id = $this->currently_logged_in_user()->id;
        parent::authorize();
        if (!(new framework($user_id))->user_can_register()) {
            throw new moodle_exception('nopermissions', 'error', '', get_string('register_a_new_factor', 'mfa'));
        }
    }
    
    /** @inheritDoc */
    public function action() {
        $user_id = $this->currently_logged_in_user()->id;
        $name = $this->get_required_param('name', PARAM_PLUGIN);
        $this->set_url('/mfa/register_factor.php', ['name' => $name]);
        $factor_factory = new factor_factory();
        $factor = $factor_factory->get($name);
        if (!$factor->user_can_register($user_id)) {
            throw new moodle_exception('nopermissions', 'error', '', get_string('register_a_new_factor', 'mfa'));
        }

        return tui_view::create('core_mfa/pages/RegisterFactor', [
            'factor' => $factor->get_name(),
            'data' => (object)$factor->get_register_data($user_id),
            'title' => $factor->get_register_title(),
        ])->set_title($factor->get_register_title());
    }
}
