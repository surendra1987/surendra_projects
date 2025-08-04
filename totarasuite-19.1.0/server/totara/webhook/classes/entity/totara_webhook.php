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
use core\orm\entity\relations\has_many;

/**
  * Webhook entity class
  *
  * Entity for Webhook model
  *
  * Properties:
  * @property-read int $id ID
  * @property string $name Name of the webhook
  * @property string $endpoint URL the webhook data will be sent to
  * @property int $created_at
  * @property int $updated_at
  * @property string $content_type_adapter
  * @property bool $status
  * @property string $auth_class
  * @property string $auth_config
  * @property bool $immediate
  *
  * @method static totara_webhook_repository repository()
  */
class totara_webhook extends entity {
    public const TABLE = 'totara_webhook_webhook';

    public const CREATED_TIMESTAMP = 'created_at';
    public const UPDATED_TIMESTAMP = 'updated_at';
    public const SET_UPDATED_WHEN_CREATED = true;
    public const STATUS = 'status';
    public const CONTENT_TYPE_ADAPTER = 'content_type_adapter';

    /**
     * Retrieve the events mapped to the webhook
     *
     * @return has_many
     */
    public function event_subscriptions(): has_many {
        return $this->has_many(totara_webhook_event_subscription::class, 'webhook_id');
    }
}
