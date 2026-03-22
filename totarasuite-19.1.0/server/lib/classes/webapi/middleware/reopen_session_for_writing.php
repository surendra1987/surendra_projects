<?php
/**
 * This file is part of Totara Core
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package core
 */

namespace core\webapi\middleware;

use Closure;
use core\session\manager;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;


/**
 * This middleware is for reopening the session, when writing the session or getting the value from session
 * on mutation and query, because we close the session prior to the request is being processed by server to
 * improve the performance.
 *
 */
class reopen_session_for_writing implements middleware {

    /**
     * @param payload $payload
     * @param Closure $next
     * @return result
     */
    public function handle(payload $payload, Closure $next): result {
        if (!manager::is_session_active() && manager::is_active_session_expected()) {
            manager::start();
        }

        return $next($payload);
    }
}