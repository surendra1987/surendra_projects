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

namespace totara_webhook\webapi\resolver\type;

use core\webapi\type_resolver;
use coding_exception;
use core\format;
use core\webapi\execution_context;
use totara_webhook\model\totara_webhook as totara_webhook_model;
use totara_webhook\formatter\totara_webhook as totara_webhook_formatter;

class totara_webhook extends type_resolver {
    /**
     * @param string $field
     * @param totara_webhook_model $totara_webhook
     * @param array $args
     * @param execution_context $ec
     *
     * @return mixed
     */
    public static function resolve(string $field, $totara_webhook, array $args, execution_context $ec): mixed {
        if (!$totara_webhook instanceof totara_webhook_model) {
            throw new coding_exception('Expected totara_webhook model');
        }

        $format = $args['format'] ?? format::FORMAT_HTML;
        if ($field === 'events') {
            return $totara_webhook
                ->get_event_subscriptions()
                ->pluck('event');
        }
        $formatter = new totara_webhook_formatter($totara_webhook, $ec->get_relevant_context());

        return $formatter->format($field, $format);
    }
}