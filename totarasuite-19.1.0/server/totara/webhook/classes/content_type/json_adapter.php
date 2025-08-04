<?php
/**
 * This file is part of Totara TXP
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author  Kelsey Scheurich <kelsey.scheurich@totara.com>
 * @package totara_webhook
 */

namespace totara_webhook\content_type;

use totara_webhook\totara_webhook_content_type_adapter;
use totara_webhook\totara_webhook_payload;

class json_adapter implements totara_webhook_content_type_adapter {
 public function format(totara_webhook_payload $payload) {
     return json_encode($payload->to_request_body());
 }

 public function get_content_type(): string {
     return 'application/json';
 }
}
