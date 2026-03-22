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

namespace totara_webhook\entity;

use core\orm\entity\entity;

/**
  * Totara webhook dead letter queue item entity class
  *
  * Entity for Totara webhook dead letter queue item model
  *
  * Properties:
  * @property-read int $id ID
  * @property int $webhook_id The ID of the webhook the payload failed to send to
  * @property int $attempt The number of times the payload has been attempted to send to the webhook
  * @property string $event The event class name the payload failed to send to
  * @property string $body The payload that failed to send to the webhook
  * @property int $time_created The time the event triggered and the payload was created
  * @property int $updated_at
  * @property int $created_at
  * @method static totara_webhook_dlq_item_repository repository()
  */
class totara_webhook_dlq_item extends entity {
    public const TABLE = 'totara_webhook_dlq_item';

    public const CREATED_TIMESTAMP = 'created_at';
    public const UPDATED_TIMESTAMP = 'updated_at';
    public const SET_UPDATED_WHEN_CREATED = true;
}