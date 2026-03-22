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

namespace totara_webhook\handler\default_handler\entity;

use core\orm\entity\entity;
use core\orm\entity\relations\belongs_to;
use totara_webhook\entity\totara_webhook;

/**
 * @property-read int $id ID
 * @property int $webhook_id
 * @property int $attempt
 * @property int $time_created
 * @property ?int $time_updated
 * @property ?int $time_sent
 * @property ?int $next_send
 * @property string $event
 * @property string $body
 *
 * @property-read totara_webhook $webhook
 */
class totara_webhook_payload_queue extends entity {

    public const TABLE = 'totara_webhook_payload_queue';

    public function webhook(): belongs_to {
        return $this->belongs_to(totara_webhook::class, "webhook_id");
    }

}
