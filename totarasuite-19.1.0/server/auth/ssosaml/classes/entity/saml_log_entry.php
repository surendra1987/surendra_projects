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

namespace auth_ssosaml\entity;

use core\orm\entity\entity;

/**
 * Represents a single logged-in session.
 *
 * @property-read int $id ID
 * @property int $idp_id
 * @property string|null $request_id Null for IdP initiated logins
 * @property string|null $session_id
 * @property string $type
 * @property bool $test
 * @property int $status
 * @property string|null $content_request
 * @property string|null $content_response
 * @property string|null $error
 * @property string|null $notice
 * @property int|null $content_request_time
 * @property int|null $content_response_time
 * @property int|null $user_id
 * @property-read int $created_at
 */
class saml_log_entry extends entity {
    public const TABLE = 'auth_ssosaml_saml_log';
    public const CREATED_TIMESTAMP = 'created_at';

    /**
     * @param int|null|bool $value
     * @return bool
     * @codeCoverageIgnore
     */
    protected function get_test_attribute($value = false): bool {
        return boolval($value);
    }

    /**
     * @param bool $value
     * @return void
     * @codeCoverageIgnore
     */
    protected function set_test_attribute(bool $value): void {
        $this->set_attribute_raw('test', (int) $value);
    }
}
