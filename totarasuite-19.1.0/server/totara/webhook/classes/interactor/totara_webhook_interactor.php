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

namespace totara_webhook\interactor;

use context;
use totara_webhook\model\totara_webhook as totara_webhook_model;

/**
 * Interactor class which is used to check what actions a user can perform on the Webhook.
 */
class totara_webhook_interactor {
    /**
     * @var totara_webhook_model $totara_webhook The model object for the Webhook.
     */
    private totara_webhook_model $totara_webhook;

    /**
     * @var ?int $actor_user_id The user ID of the person who is performing the action. If not provided the current user
     *                         will be used.
     */
    private ?int $actor_user_id;

    /**
     * @var context $context The context of the Webhook used when checking capabilities.
     */
    private context $context;

    protected function __construct(
        totara_webhook_model $totara_webhook,
        ?int $actor_user_id = null
    ) {
        $this->actor_user_id = $actor_user_id;
        $this->totara_webhook = $totara_webhook;
        $this->context = $this->totara_webhook->get_context();
    }

    /**
     * Create an instance from a Webhook model instance.
     *
     * @param totara_webhook_model $totara_webhook
     * @param ?int $actor_user_id
     * @return self
     */
    public static function from_totara_webhook(totara_webhook_model $totara_webhook, ?int $actor_user_id = null): self {
        return new self($totara_webhook, $actor_user_id);
    }

    /**
     * Create an instance from a Webhook id.
     *
     * @param int $totara_webhook_id
     * @param ?int $actor_user_id
     * @return self
    */
    public static function from_totara_webhook_id(int $totara_webhook_id, ?int $actor_user_id = null): self {
        $totara_webhook = totara_webhook_model::load_by_id($totara_webhook_id);
        return new self($totara_webhook, $actor_user_id);
    }

    /**
     * Returns true if the actor is allowed to view this specific Webhooks.
     *
     * @return bool
     */
    public function can_view(): bool {
        return has_capability(
            'totara/webhook:viewtotara_webhooks',
            $this->context,
            $this->actor_user_id
        );
    }

    /**
     * Returns true if the actor is allowed to manage this specific Webhooks.
     *
     * @return bool
     */
    public function can_manage(): bool {
        return has_capability(
            'totara/webhook:managetotara_webhooks',
            $this->context,
            $this->actor_user_id
        );
    }
}

