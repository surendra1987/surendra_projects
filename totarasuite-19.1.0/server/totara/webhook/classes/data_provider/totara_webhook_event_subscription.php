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

namespace totara_webhook\data_provider;

use core\orm\entity\filter\filter_factory;
use totara_core\data_provider\provider;
use totara_core\data_provider\provider_interface;
use totara_webhook\entity\totara_webhook_event_subscription as totara_webhook_event_subscription_entity;

/**
 * Webhook Event Subscription data provider class
 */
class totara_webhook_event_subscription extends provider implements provider_interface {
    public const DEFAULT_PAGE_SIZE = 100;

    // Mapping of sort field display names to physical entity _columns_.
    public const SORT_FIELDS = [
        'created_at' => 'created_at',
        'updated_at' => 'updated_at',
    ];

    /**
     * @inheritDoc
     */
    protected function get_default_sort_by(): ?string {
        return 'created_at';
    }

    /**
     * @inheritDoc
     */
    public static function create(?filter_factory $filter_factory = null): provider {
        return new static(
            totara_webhook_event_subscription_entity::repository(),
            self::SORT_FIELDS,
            $filter_factory
        );
    }

    /**
     * @inheritDoc
     */
    public static function get_type(): string {
        return 'totara_webhook_event_subscription';
    }

    /**
     * @inheritDoc
     */
    public static function get_summary_format_select() {
        return 'summaryformat';
    }
}