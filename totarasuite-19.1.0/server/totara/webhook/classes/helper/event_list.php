<?php
/**
 * This file is part of Totara Core
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

namespace totara_webhook\helper;

class event_list {

    /**
     * Get a list of all events that exist in the system
     * with class and name formatted for use in webhooks
     *
     * @return array
     */
    public static function get_all_events(): array {
        $event_classes = [];
        $all_events = \report_eventlist_list_generator::get_all_events_list();
        foreach ($all_events as $key => $event) {
            $event_name = str_replace($key, '', $event['raweventname']);
            $event_name = trim($event_name);
            $class_name = str_starts_with($key, "\\") ? substr($key, 1) : $key;
            $event_classes[$class_name] = [
                'name' => $event_name,
                'class' => $class_name,
            ];
        }
        return $event_classes;
    }
    public static function get_all_events_for_subscribed(array $event_subscriptions): array {
        $all_events = static::get_all_events();
        $flipped = array_flip($event_subscriptions);
        return array_intersect_key($all_events, $flipped);
    }
}
