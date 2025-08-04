<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author  Nathaniel Walmsley <nathaniel.walmsley@totara.com>
 * @package mod_facetoface
 * @category totara_notification
 */

namespace mod_facetoface\totara_notification\placeholder;

use coding_exception;
use mod_facetoface\seminar_event;
use totara_notification\placeholder\abstraction\placeholder_instance_cache;
use totara_notification\placeholder\abstraction\single_emptiable_placeholder;
use totara_notification\placeholder\customfield_options;
use totara_notification\placeholder\option;

class event_cancellation extends single_emptiable_placeholder {
    use placeholder_instance_cache;

    /** @var ?seminar_event */
    private ?seminar_event $event;

    /**
     * event constructor.
     * @param seminar_event|null $event
     */
    public function __construct(?seminar_event $event = null) {
        $this->event = $event;
    }

    /**
     * @param int $event_id
     *
     * @return self
     */
    public static function from_event_id(int $event_id): self {
        $cache_key = $event_id;

        $instance = self::get_cached_instance($cache_key);
        if (!$instance) {
            $event = new seminar_event($event_id);
            $instance = new static($event);
            self::add_instance_to_cache($cache_key, $instance);
        }
        return $instance;
    }

    /**
     * @return option[]
     */
    public static function get_options(): array {
        return customfield_options::get_options('facetoface_sessioncancel');
    }

    /**
     * @param string $key
     * @return bool
     */
    protected function is_available(string $key): bool {
        return $this->event !== null;
    }

    /**
     * @param string $key
     * @return string
     */
    public function do_get(string $key): string {
        if ($this->event === null) {
            throw new coding_exception("The seminar signup is empty");
        }

        $field_map = customfield_options::get_key_field_map('facetoface_sessioncancel');
        if (!isset($field_map[$key])) {
            throw new coding_exception("Invalid key '$key'");
        }

        return format_string(
            customfield_options::get_field_value(
                $this->event->get_id(),
                $field_map[$key],
                'facetoface_sessioncancel',
                'facetofacesessioncancel'
            )
        );
    }
}