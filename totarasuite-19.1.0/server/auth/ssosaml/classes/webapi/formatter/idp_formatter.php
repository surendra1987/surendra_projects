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

namespace auth_ssosaml\webapi\formatter;

use auth_ssosaml\model\idp;
use core\orm\formatter\entity_model_formatter;
use core\webapi\formatter\field\string_field_formatter;

/**
 * Formatter for IdP.
 *
 * @property-read idp $object
 */
class idp_formatter extends entity_model_formatter {
    /**
     * @return array
     */
    protected function get_map(): array {
        return [
            'id' => null,
            'metadata' => null,
            'label' => string_field_formatter::class,
            'idp_user_id_field' => null,
            'totara_user_id_field' => null,
            'status' => null,
            'debug' => null,
            'logout_idp' => null,
            'logout_url' => null,
            'create_users' => null,
            'field_mapping_config' => null,
            'saml_config' => function (): array {
                return $this->object->sp_config->idp_config;
            },
            'autolink_users' => function (): string {
                return $this->object->autolink_users_enum;
            },
            'login_hide' => null,
        ];
    }
}
