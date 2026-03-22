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

use auth_ssosaml\model\idp;
use moodle_url;
use totara_mvc\tui_view;

/**
 * Controller for IdP metadata view page.
 */
class saml_log extends base_admin {
    /**
     * @return tui_view
     */
    public function action(): tui_view {
        $idp_id = $this->get_required_param('idp', PARAM_INT);

        $this->set_url(new moodle_url('/auth/ssosaml/saml_log.php', ['idp' => $idp_id]));

        $idp = idp::load_by_id($idp_id);

        return static::create_tui_view('auth_ssosaml/pages/SamlLog', [
            'idp-id' => (string)$idp_id,
            'idp-name' => $idp->label ?? get_string('default_label', 'auth_ssosaml'),
            'debug-enabled' => $idp->debug,
        ]);
    }
}
