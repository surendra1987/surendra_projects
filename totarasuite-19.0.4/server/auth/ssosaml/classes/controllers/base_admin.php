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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\controllers;

use auth_ssosaml\local\util;
use context_system;
use totara_mvc\admin_controller;

/**
 * Base controller for auth_ssosaml admin pages.
 */
abstract class base_admin extends admin_controller {
    /**
     * @var string
     */
    protected $admin_external_page_name = 'authsettingssosaml';

    /**
     * @inheritDoc
     */
    protected function authorize(): void {
        parent::authorize();
        $this->require_capability('auth/ssosaml:manage', $this->get_context());
        util::assert_saml_enabled();
    }

    /**
     * @inheritDoc
     */
    protected function setup_context(): \context {
        return context_system::instance();
    }
}
