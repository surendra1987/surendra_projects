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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package ml_service
 */

namespace ml_service\testing;

use coding_exception;
use core\lock\lock;
use core\lock\lock_config;
use ReflectionProperty;
use totara_core\http\clients\curl_client;
use totara_core\http\request;
use totara_core\http\response;

/**
 * Mock instance replacing ml_service. Can be activated by either behat or by
 * setting $CFG->ml_mock_service_source to a storage file.
 */
class mock_client extends curl_client {

    public const USERS = 0;
    public const ITEMS = 1;

    /**
     * @param int $item_id
     * @param int $recommended_item_id
     * @param string $component
     * @param string|null $area
     * @param float $score
     * @return void
     */
    public static function add_mock_item_recommendation(int $item_id, int $recommended_item_id, string $component, ?string $area, float $score): void {
        self::write_storage(self::ITEMS, [
            $component . $item_id => compact('recommended_item_id', 'component', 'area', 'score')
        ]);
    }

    /**
     * Write out the recommended records.
     *
     * @param int $bucket
     * @param array $records
     * @return void
     */
    private static function write_storage(int $bucket, array $records): void {
        $path = self::temp_storage_location();
        $lock = self::lock();

        $base_bucket = [
            self::USERS => [],
            self::ITEMS => [],
        ];
        if (static::is_configured()) {
            // Load the existing, use our existing lock
            $base_bucket = self::load(false);
        }

        foreach ($records as $group_id => $record) {
            if (!isset($base_bucket[$bucket][$group_id])) {
                $base_bucket[$bucket][$group_id] = [];
            }
            $base_bucket[$bucket][$group_id][] = $record;
        }

        file_put_contents($path, json_encode($base_bucket, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR, 32));

        if ($lock) {
            $lock->release();
        }
    }

    /**
     * Location where mock ml requests will be persisted to
     *
     * @return string
     */
    private static function temp_storage_location(): string {
        global $CFG;

        // Local development - mock service
        if (!empty($CFG->ml_mock_service_source) && is_file($CFG->ml_mock_service_source)) {
            return $CFG->ml_mock_service_source;
        }

        $path = rtrim($CFG->dataroot, "/\\");
        $index = self::process_id();
        return "{$path}/_mock_ml_service_{$index}.json";
    }

    /**
     * Return a unique ID related to this process. Only used specifically
     * when running behat as behat can run in multiple threads and we don't want crossover.
     *
     * @return string
     */
    private static function process_id(): string {
        global $CFG;

        if (!empty($CFG->behatrunprocess)) {
            return 'p' . $CFG->behatrunprocess;
        }

        return 's';
    }

    /**
     * Get the specific lock for this process.
     *
     * @return bool|lock
     * @throws coding_exception
     */
    private static function lock(): bool|lock {
        $factory = lock_config::get_lock_factory('ml_service_mock');
        $index = self::process_id();
        return $factory->get_lock('ml_service_mock' . $index, 15);
    }

    /**
     * Returns true if the storage file has been created and is readable.
     *
     * @return bool
     */
    public static function is_configured(): bool {
        $path = self::temp_storage_location();
        return file_exists($path) && is_readable($path);
    }

    /**
     * Load the existing temp file contents.
     * Will lock it but that can be skipped.
     *
     * @param bool $lock
     * @return array
     */
    private static function load(bool $lock = true): array {
        if (!static::is_configured()) {
            return [];
        }

        $locked = $lock ? self::lock() : false;
        $path = self::temp_storage_location();

        $contents = json_decode(file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
        if ($locked) {
            $locked->release();
        }

        return $contents;
    }

    /**
     * @param int $user_id
     * @param int $recommended_item_id
     * @param string $component
     * @param string|null $area
     * @param float $score
     * @return void
     */
    public static function add_mock_user_recommendation(int $user_id, int $recommended_item_id, string $component, ?string $area, float $score): void {
        self::write_storage(self::USERS, [
            'u' . $user_id => compact('recommended_item_id', 'component', 'area', 'score')
        ]);
    }

    /**
     * Delete the storage file
     *
     * @return void
     */
    public static function reset(): void {
        $path = self::temp_storage_location();
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Override the request to ml_service and respond with our mock data
     *
     * @param request $request
     * @return response
     */
    public function execute(request $request): response {
        // We want the raw URL
        // Only used in testing environments
        $reflected = new ReflectionProperty($request, 'url');
        $url = $reflected->getValue($request);

        // read-only, no locking
        $load = self::load(false);

        switch ($url->get_path()) {
            case '/user-items':
                // Recommending items to users
                $target_user_id = $url->param('totara_user_id');
                $collection = $load[self::USERS]['u' . $target_user_id] ?? [];
                $item_type = $url->param('item_type');
                $n_items = $url->param('n_items') ?? 5;

                $items = array_values(array_filter($collection, function ($item) use ($item_type) {
                    return $item_type === $item['component'];
                }));

                $items = array_slice($items, 0, $n_items);
                $items = array_map(fn($item) => [$item['recommended_item_id'], (float) $item['score']], $items);

                break;

            case '/similar-items':
                // Recommending items to item
                // Target is $component$id squashed together
                $target_item_id = $url->param('totara_item_id');
                $collection = $load[self::ITEMS][$target_item_id] ?? [];
                $n_items = $url->param('n_items') ?? 5;

                $items = array_values(array_filter($collection, function ($item) use ($target_item_id) {
                    return str_starts_with($target_item_id, $item['component']);
                }));

                $items = array_slice($items, 0, $n_items);
                $items = array_map(fn($item) => [$item['recommended_item_id'], (float) $item['score']], $items);
                break;

            default:
                $items = null;
                break;
        }

        $body = [
            'success' => is_array($items),
            'items' => $items,
        ];

        return new response(json_encode($body), 200, [], 'application/json');
    }

}