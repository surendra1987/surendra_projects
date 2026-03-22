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

 * @package totara_webhook
 * @author ben fesili <ben.fesili@totara.com>
 */

namespace totara_webhook\testing;

use core\testing\component_generator;
use totara_webhook\model\totara_webhook_dlq_item;
use totara_webhook\totara_webhook_payload;

/**
  * Totara webhook dead letter queue item model generator
  */
final class totara_webhook_dlq_item_generator extends component_generator {

    /**
     * @param totara_webhook_payload $payload
     * @return totara_webhook_dlq_item
     * @throws \coding_exception
     */
    public function create_totara_webhook_dlq_item_from_payload(totara_webhook_payload $payload): totara_webhook_dlq_item {
        return totara_webhook_dlq_item::create_from_totara_webhook_payload($payload);
    }
}