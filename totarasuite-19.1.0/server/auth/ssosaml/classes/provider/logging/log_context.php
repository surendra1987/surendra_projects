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
 * @package auth_ssosaml
 */

namespace auth_ssosaml\provider\logging;

use coding_exception;

/**
 * Object used to manage how to log SAML responses (IdP/SP)
 */
class log_context {
    public const TYPE_REQUEST_ID = 'REQUEST_ID';
    public const TYPE_LOG_ID = 'LOG_ID';
    private string $id;

    private string $type;

    private function __construct(string $type, string $id) {
        $this->type = $type;
        $this->id = $id;
    }

    public static function create(string $id, string $type = self::TYPE_REQUEST_ID): self {
        if (!in_array($type, [self::TYPE_LOG_ID, self::TYPE_REQUEST_ID])) {
            throw new coding_exception('Invalid type provided');
        }

        return new self($type, $id);
    }

    /**
     * Get Id
     *
     * @return string
     */
    public function get_id(): string {
        return $this->id;
    }

    /**
     * Get Type
     *
     * @return string
     */
    public function get_type(): string {
        return $this->type;
    }


}