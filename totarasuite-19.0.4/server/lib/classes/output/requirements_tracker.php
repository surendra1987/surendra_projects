<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core
 */

namespace core\output;

/**
 * Tracker for page requirements.
 *
 * Records the origin of each requirement, so that shell and content requirements can be separated if needed.
 *
 * @package core
 */
class requirements_tracker {
    /**
     * Default origin key.
     */
    public const DEFAULT_ORIGIN = 'default';

    /**
     * Requirement storage. Dictionary of lists, $entries[origin][index]
     *
     * @var array
     */
    private $entries = [];

    /**
     * Stack of origins.
     *
     * @var array
     */
    private $origin = [];

    /**
     * Add a requirement.
     *
     * @param string $type Requirement type -- e.g. amd module, js file, etc
     * @param mixed $value Requirement value. This can be anything, usually a fixed structure for each type
     * @param null|string $unique_key Unique identifier for this requirement, used to deduplicate.
     *     Deduplication happens when requirements are read, after applying filters, in order to correctly deduplicate when
     *     multiple origins are involved.
     * @return void
     */
    public function add(string $type, $value, ?string $unique_key = null): void {
        $record = [
            'unique_key' => $unique_key,
            'origin' => $this->get_origin(),
            'value' => $value,
        ];
        $this->entries[$type][] = $record;
    }

    /**
     * Get a previously added requirement by key.
     *
     * @param string $type
     * @param string $unique_key
     * @param null|string $origin
     * @return mixed
     */
    public function get_by_key(string $type, string $unique_key, ?string $origin = null) {
        $entries = $this->entries[$type] ?? [];
        $found = null;
        foreach ($entries as $entry) {
            if ($origin !== null && $entry['origin'] !== $origin) {
                continue;
            }
            if ($entry['unique_key'] == $unique_key) {
                $found = $entry['value'];
            }
        }
        return $found;
    }

    /**
     * Get the current active origin
     *
     * @return string
     */
    public function get_origin(): string {
        return count($this->origin) > 0 ? end($this->origin) : static::DEFAULT_ORIGIN;
    }

    /**
     * Add a origin to the stack.
     *
     * @param string $origin
     * @return void
     */
    private function push_origin(string $origin): void {
        $this->origin[] = $origin;
    }

    /**
     * Remove the last added origin from the stack.
     *
     * @return void
     */
    private function pop_origin(): void {
        array_pop($this->origin);
    }

    /**
     * Mark all requirements added within the callback as having been added by the provided origin.
     *
     * @param string $origin
     * @param callable $fn
     * @return void
     */
    public function with_origin(string $origin, callable $fn): void {
        $this->push_origin($origin);
        try {
            $fn();
        } finally {
            $this->pop_origin($origin);
        }
    }

    /**
     * Get all requirements of the specified type, optionally filtered by origin.
     *
     * @param string $type
     * @param null|string $origin
     * @return array
     */
    public function get_entries(string $type, ?string $origin = null): array {
        $entries = $this->entries[$type] ?? [];
        if ($origin !== null) {
            $entries = array_filter($entries, fn ($x) => $x['origin'] === $origin);
        }
        $entries = $this->deduplicate($entries);
        return array_column($entries, 'value');
    }

    /**
     * Deduplicate requirements by unique_key.
     *
     * @param array $arr
     * @return array
     */
    private function deduplicate(array $arr): array {
        $out = [];
        foreach ($arr as $item) {
            $unique_key = $item['unique_key'];
            if ($unique_key !== null) {
                $out[$unique_key] = $item;
            } else {
                // PHP arrays preserve insert order even with mixed string and automatic number keys
                $out[] = $item;
            }
        }
        return $out;
    }
}
