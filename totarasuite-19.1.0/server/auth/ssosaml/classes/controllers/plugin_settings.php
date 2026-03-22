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

use auth_ssosaml\data_provider\user_fields;
use auth_ssosaml\local\util;
use auth_ssosaml\model\plugin_config;
use moodle_url;
use totara_mvc\tui_view;

/**
 * Controller for IdP settings page.
 */
class plugin_settings extends base_admin {
    /**
     * @var string
     */
    protected $admin_external_page_name = 'authsettingssosaml';

    /**
     * @return tui_view
     */
    public function action(): tui_view {
        $this->set_url(new moodle_url('/auth/ssosaml/plugin_settings.php'));

        $config = new plugin_config();

        return static::create_tui_view('auth_ssosaml/pages/Settings', [
            'opensslAvailable' => util::is_openssl_loaded(),
            'config' => [
                'fields' => $config->get_fields(),
            ],
            'fieldInfo' => user_fields::get_info(),
        ]);
    }
}
