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

namespace totara_webhook\data_processor;

use core\event\base;
use stdClass;

class data_sanitiser {
    protected array $raw_data = [];
    protected array $sanitised_data = [];
    protected array $redacted_data = [];
    protected string $event_class = '';

    /**
     * A list of keys that should be redacted from the data.
     * The sanitise function will check for case insensitivity,
     * but the convention to adding to this list is to use
     * lowercase strings.
     *
     * @var array|string[]
     */
    protected array $redacted_keys = [
        'secret',
        'password',
        'hash',
        'token',
        'secure',
        'idp_entity_id',
        'certificates',
    ];

    /**
     * This is the value which we'll redact the data with.
     */
    public const REDACTED_MASK = '***';

    /**
     * @param array $event_data
     */
    public function __construct(string $event_class, array $event_data) {
        $this->raw_data = $event_data;
        $this->event_class = $event_class;
        $this->sanitise_data();
    }

    /**
     * Will sanitise the data. Moving all redacted keys to redacted data.
     * If the key is not found, it will do nothing.
     *
     * @param array $event_data
     * @return $this
     */
    protected function sanitise_data(): self {
        $escaped_keys = array_map('preg_quote', $this->redacted_keys);
        $pattern = '/(' . implode('|', $escaped_keys) . ')/i';
        foreach ($this->raw_data as $key => $value) {
            if (preg_match($pattern, $key)) {
                $this->redacted_data[$key] = static::REDACTED_MASK;
            } else {
                $this->sanitised_data[$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Will expose the data for the given key.
     * Moving it from redacted to exposed.
     * If the key is not found, it will do nothing.
     *
     * @param $key
     * @return $this
     */
    public function expose_data($key): self {
        if (!isset($this->redacted_data[$key])) {
            return $this;
        }
        // we grab the value from raw data - redacted is already set '***'
        $this->sanitised_data[$key] = $this->raw_data[$key];
        unset($this->redacted_data[$key]);
        return $this;
    }

    /**
     * Will redact the data for the given key.
     * If the key is not found, it will do nothing.
     *
     * @param $key
     * @return $this
     */
    public function redact_data($key): self {
        if (!isset($this->sanitised_data[$key])) {
            return $this;
        }
        $this->redacted_data[$key] = static::REDACTED_MASK;
        unset($this->sanitised_data[$key]);
        return $this;
    }

    /**
     * Will return all the data, both exposed and redacted.
     *
     * @return array
     */
    public function get_sanitised_data(): array {
        return array_merge($this->sanitised_data, $this->redacted_data);
    }

    /**
     * Will return only the exposed data.
     *
     * @return array
     */
    public function get_exposed_data(): array {
        return $this->sanitised_data;
    }

    /**
     * Will return only the redacted data.
     *
     * @return array
     */
    public function get_redacted_data(): array {
        return $this->redacted_data;
    }

    /**
     * Will return the raw data of the event regardless of redacted or exposed.
     *
     * @return array
     */
    public function get_raw_data(): array {
        return $this->raw_data;
    }

    /**
     * Will return the event class name.
     *
     * @return string
     */
    public function get_event_class(): string {
        return $this->event_class;
    }
}
