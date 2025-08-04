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

namespace totara_webhook\model\filter;

use coding_exception;
use core\orm\entity\filter\filter;
use core\orm\entity\filter\filter_factory;
use core\orm\entity\filter\greater_equal_than as greater_equal_than_filter;
use core\orm\entity\filter\like as like_filter;
use core\orm\entity\filter\equal as equal_filter;
use totara_webhook\entity\totara_webhook_dlq_item;

/**
  * Totara webhook dead letter queue item filter factory class
  */
class totara_webhook_dlq_item_filter_factory implements filter_factory {
    /**
     * @inheritDoc
     */
    public function create(string $key, $value, ?int $user_id = null): ?filter {
        switch ($key) {
            case 'created_at_after':
                return static::create_created_at_after_filter($value);
            case 'updated_at_after':
                return static::create_updated_at_after_filter($value);
            default:
                return null;
        }
    }

    /**
     * Returns an instance of a created_at after filter for Totara webhook dead letter queue item.
     *
     * @param string $value the matching value(s).
     *
     * @return filter the filter instance.
     */
    public static function create_created_at_after_filter(string $value): filter {
        return (new greater_equal_than_filter('created_at'))
            ->set_value($value)
            ->set_entity_class(totara_webhook_dlq_item::class);
    }

    /**
     * Returns an instance of a updated_at after filter for Totara webhook dead letter queue item.
     *
     * @param string $value the matching value(s).
     *
     * @return filter the filter instance.
     */
    public static function create_updated_at_after_filter(string $value): filter {
        return (new greater_equal_than_filter('updated_at'))
            ->set_value($value)
            ->set_entity_class(totara_webhook_dlq_item::class);
    }

}