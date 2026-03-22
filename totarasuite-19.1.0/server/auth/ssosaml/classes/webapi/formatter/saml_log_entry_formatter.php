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
use auth_ssosaml\provider\logging\contract;
use core\orm\formatter\entity_model_formatter;
use core\webapi\formatter\field\date_field_formatter;

/**
 * Formatter for SAML log entries.
 *
 * @property-read idp $object
 */
class saml_log_entry_formatter extends entity_model_formatter {
    /**
     * @return array
     */
    protected function get_map(): array {
        return [
            'id' => null,
            'type' => function ($value) {
                switch ($value) {
                    case contract::TYPE_LOGIN:
                        return get_string('log_login', 'auth_ssosaml');
                    case contract::TYPE_IDP_LOGIN:
                        return get_string('log_idp_login', 'auth_ssosaml');
                    case contract::TYPE_LOGOUT:
                        return get_string('log_logout', 'auth_ssosaml');
                    case contract::TYPE_IDP_LOGOUT:
                        return get_string('log_idp_logout', 'auth_ssosaml');
                    default:
                        // When we don't know (because the data may not match), keep the internal value instead.
                        return $value;
                }
            },
            'content_request' => null,
            'content_response' => null,
            'error' => null,
            'notice' => null,
            'created_at' => date_field_formatter::class,
            'content_request_time' => date_field_formatter::class,
            'content_response_time' => date_field_formatter::class,
            'status' => function ($value) {
                switch ($value) {
                    case contract::STATUS_SUCCESS:
                        return 'SUCCESS';
                    case contract::STATUS_ERROR:
                        return 'ERROR';
                    default:
                        return 'INCOMPLETE';
                }
            },
            'status_label' => null,
        ];
    }
}
