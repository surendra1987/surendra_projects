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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

namespace auth_ssosaml\entity;

use core\orm\entity\entity;

/**
 * @property-read int $id ID
 * @property string $metadata JSON-encoded metadata configuration
 * @property string $idp_entity_id IdP entity_id
 * @property string $label
 * @property string $idp_user_id_field
 * @property string $totara_user_id_field
 * @property string $field_mapping_config JSON-encoded field mapping configuration. Contains delimiter for multi-value attributes and field mappings
 * @property string $saml_config JSON-encoded editable saml config
 * @property string $certificates JSON-encoded saml certificates.
 * @property bool $logout_idp
 * @property string|null $logout_url
 * @property bool $create_users
 * @property bool $status
 * @property int $autolink_users
 * @property bool $debug
 * @property bool $login_hide
 */
class idp extends entity {
    public const TABLE = 'auth_ssosaml_idp_config';

    /**
     * @param int|null|bool $value
     * @return bool
     * @codeCoverageIgnore
     */
    public function get_status_attribute($value = false): bool {
        return boolval($value);
    }

    /**
     * @param bool $value
     * @return void
     * @codeCoverageIgnore
     */
    public function set_status_attribute(bool $value): void {
        $this->set_attribute_raw('status', (int) $value);
    }

    /**
     * @param int|null|bool $value
     * @return bool
     * @codeCoverageIgnore
     */
    public function get_debug_attribute($value = false): bool {
        return boolval($value);
    }

    /**
     * @param bool $value
     * @return void
     * @codeCoverageIgnore
     */
    public function set_debug_attribute(bool $value): void {
        $this->set_attribute_raw('debug', (int) $value);
    }

    /**
     * @param bool $value
     * @return bool
     * @codeCoverageIgnore
     */
    public function get_logout_idp(bool $value = false): bool {
        return boolval($value);
    }

    /**
     * @param bool $value
     * @return void
     * @codeCoverageIgnore
     */
    public function set_logout_idp(bool $value): void {
        $this->set_attribute_raw('logout_idp', (int) $value);
    }

    /**
     * @param bool $value
     * @return bool
     * @codeCoverageIgnore
     */
    public function get_login_hide(bool $value = false): bool {
        return boolval($value);
    }

    /**
     * @param bool $value
     * @return void
     * @codeCoverageIgnore
     */
    public function set_login_hide(bool $value): void {
        $this->set_attribute_raw('login_hide', (int) $value);
    }

    /**
     * @param int $value
     * @return int
     * @codeCoverageIgnore
     */
    public function get_autolink_users_attribute(int $value): int {
        return intval($value);
    }
}