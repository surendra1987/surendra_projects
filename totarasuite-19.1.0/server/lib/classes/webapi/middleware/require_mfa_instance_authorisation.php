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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core
 */

namespace core\webapi\middleware;

use Closure;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use core_mfa\model\instance as instance_model;

/**
 * Check if user is allowed to access MFA instance.
 */
class require_mfa_instance_authorisation implements middleware {
    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        global $USER;

        $input = $payload->get_variable('input');

        $instance_model = instance_model::load_by_id($input['id']);

        if ($USER->id != $instance_model->user_id) {
            throw new \coding_exception('User is not allowed to access MFA instance');
        }
        $payload->set_variable('instance', $instance_model);

        return $next($payload);
    }
}