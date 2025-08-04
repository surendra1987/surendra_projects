<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\middleware;

use Closure;
use coding_exception;
use moodle_exception;
use core\webapi\middleware;
use core\webapi\resolver\payload;
use core\webapi\resolver\result;
use totara_webhook\model\totara_webhook as totara_webhook_model;
use totara_webhook\interactor\totara_webhook_interactor;


/**
 * Interceptor that uses Webhook related data in the incoming graphql payload
 * to check user permissions.
 *
 * This requires the Webhook to have already been loaded and present in the payload.
 * The @see require_totara_webhook middleware does the loading so this middleware
 * depends on and must be called after that one.
 */
class require_totara_webhook_view_capability implements middleware {
    /**
     * @inheritDoc
     */
    public function handle(payload $payload, Closure $next): result {
        $totara_webhook_model = $payload->get_variable('totara_webhook');
        if (!$totara_webhook_model instanceof totara_webhook_model) {
            throw new coding_exception('Webhook was not loaded. Make sure a previous middleware loads the Webhook before this one is called.');
        }
        $interactor = totara_webhook_interactor::from_totara_webhook($totara_webhook_model);

        if (!$interactor->can_view()) {
            throw new moodle_exception('invalid_totara_webhook', 'totara_webhook');
        }

        return $next($payload);
    }
}

