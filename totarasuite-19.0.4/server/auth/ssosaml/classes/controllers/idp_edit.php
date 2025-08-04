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

use moodle_url;
use totara_mvc\tui_view;
use auth_ssosaml\data_provider\user_fields;
use auth_ssosaml\model\idp as model;
use auth_ssosaml\model\idp\config\nameid;

/**
 * Controller for IdP edit page.
 */
class idp_edit extends base_admin {
    /**
     * @return tui_view
     */
    public function action(): tui_view {
        $id = $this->get_required_param('id', PARAM_INT);

        $this->set_url(new moodle_url('/auth/ssosaml/idp_edit.php', $id ? ['id' => $id] : null));

        $query_idp = $this->execute_graphql_operation('auth_ssosaml_idp_for_editing', ['id' => $id])['data']['idp'];

        $idp = model::load_by_id($id);
        $sp_config = $idp->sp_config;

        $metadata = [
            'sp_metadata_url' => $sp_config->metadata_url,
            'entity_id' => $sp_config->entity_id,
            'default_entity_id' => $sp_config->default_entity_id,
            'acs_url' => $sp_config->acs_url,
            'slo_url' => $sp_config->slo_url,
        ];

        return tui_view::create('auth_ssosaml/pages/IdPEdit', [
            'idp' => $query_idp,
            'metadata' => $metadata,
            'fieldInfo' => user_fields::get_info(),
            'userIdFieldNames' => user_fields::get_user_id_fields(),
            'nameIdFormats' => nameid::get_all(),
        ]);
    }
}
