<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Qingyang Liu <qingyang.liu@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\local;

use cache;
use core\orm\entity\traits\json_trait;
use mobile_findlearning\catalog;
use totara_catalog\catalog_retrieval;
use totara_catalog\exception\url_query_validation_exception;
use totara_catalog\provider;
use totara_catalog\provider_handler;
use totara_catalog\webapi\schema_objects\option;


/**
 * Query helper class is to provide validation function for graphql query input.
 */
class query_helper {

    use json_trait;

    /**
     * @var string
     */
    private const QUERY_STRING = 'query_string';

    /**
     * @var string
     */
    private const QUERY_STRUCTURE = 'query_structure';

    private const STARTING_LIMITFROM = 0;

    private const STARTING_MAXCOUNT = -1;

    /**
     * @var string[]
     */
    private static $expected_filter_keys = [
        'orderbykey',
        'itemstyle',
        'limitfrom',
        'maxcount',
        'perpageload',
        'objecttype',
        'cursor',
    ];

    /**
     * @var array[]
     */
    private static $expected_filters = [
        'itemstyle' => ['narrow', 'wide'],
        'orderbykey' => ['featured']
    ];

    /**
     * Validate GraphQL totara_catalog_query_input type.
     *
     * @param array $input
     * @param bool $strict If true, then expected input must be present.
     * @return void
     */
    public static function validate_input(array $input, bool $strict = true): void {
        if ($strict && !isset($input[self::QUERY_STRING]) && !isset($input[self::QUERY_STRUCTURE])) {
            throw new url_query_validation_exception('You must provide a query string or query structure.');
        }

        if (isset($input[self::QUERY_STRING], $input[self::QUERY_STRUCTURE])) {
            throw new url_query_validation_exception('Can not query two input fields.');
        }

    }

    /**
     * Checks that a value is not empty, or if an array, does not have empty values.
     *
     * @param mixed $value
     * @param string $key
     * @param array $errors
     * @return void
     */
    protected static function validate_empty(mixed $value, string $key, array &$errors): void {
        // Allow empty search.
        if ($key === 'catalog_fts') {
            return;
        }
        // Validate empty.
        if (is_array($value)) {
            if (empty($value)) {
                $errors[] ="{$key}'s value must not be empty.";
            }

            foreach ($value as $val) {
                if (trim($val) === '') {
                    $errors[] = "{$key}'s value must not be empty.";
                }
            }
        } elseif (is_string($value) && trim($value) === '') {
            $errors[] = "{$key}'s value must not be empty.";
        }
    }

    /**
     * Resets the pagination fields on an array of query parameters, and returns the adjusted array.
     *
     * @param array $params
     * @return array revised params
     */
    protected static function reset_pagination_params(array $params): array {
        $params['limitfrom'] = self::STARTING_LIMITFROM;
        $params['maxcount'] = self::STARTING_MAXCOUNT;
        $params['cursor'] = null;
        return $params;
    }

    /**
     * Normalise totara_catalog_query_input value into structured params.
     *
     * @param array $input
     * @return array

     */
    public static function parse_input_to_params(array $input): array {
        // No pagination by default
        $params = [];
        $params = static::reset_pagination_params($params);

        // Look for query_string input first, as priority.
        if (isset($input[self::QUERY_STRING])) {
            if (empty($input[self::QUERY_STRING])) {
                throw new url_query_validation_exception('Query string cannot be empty.');
            }
            parse_str($input[self::QUERY_STRING], $params);

            // Reset again to ensure not pagination for query string inputs.
            $params = static::reset_pagination_params($params);

            // Query string params return now.
            return $params;
        }

        // Look for query_structure input.
        if (isset($input[self::QUERY_STRUCTURE])) {
            // Overwrite default params with input.
            $params = static::json_decode_to_array($input[self::QUERY_STRUCTURE]);

            // Resolve pagination cursor.
            $params = static::resolve_cursor($params);
        }

        return $params;
    }

    /**
     * Checks structured params to ensure that filters and options are valid.
     *
     * @param array $params
     * @param array $errors
     * @param catalog_retrieval $catalog
     * @return void
     */
    public static function validate_params(array &$params, array &$errors, catalog_retrieval $catalog): void {
        foreach (filter_handler::instance()->get_all_filters() as $filter) {
            $alias = $filter->datafilter->get_alias();
            // Collect the url query keys.
            static::$expected_filter_keys[] = $alias;

            if (!isset($params[$alias])) {
                continue;
            }

            $value = $params[$alias];
            static::validate_empty($value, $alias, $errors);

            try {
                $filter->selector->set_current_data($params);
                $standard_data = $filter->selector->get_data();
                $filter->datafilter->set_current_data($standard_data);
            } catch (\coding_exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        static::get_valid_sort_options($catalog);
        static::validate_keys($params, $errors);
        static::validate_objecttype($params, $errors);
    }

    /**
     * Validate url query key and value.
     *
     * @param array $params
     * @param array $errors
     * @return void
     */
    protected static function validate_keys(array $params, array &$errors): void {
        foreach ($params as $key => $values) {
            if (in_array($key, ['perpageload']) && (is_string($values) && !is_numeric($values))) {
                $errors[] = "{$key}'s value must be numeric.";
            }
            if (!in_array($key, static::$expected_filter_keys)) {
                $errors[] = "'{$key}' is not a valid url query key.";
            }
        }
    }

    /**
     * Returns valid sort options based on catalog configuration.
     *
     * @param catalog_retrieval $catalog
     * @return array[]
     */
    public static function get_valid_sort_options(catalog_retrieval $catalog): array {
        $sorts = [];

        foreach (catalog::get_orderbykey_options() as $option) {
            // Skip text sorting if alpha sorting is not enabled.
            if (!$catalog->alphabetical_sorting_enabled() && $option->key == 'text') {
                continue;
            }
            static::$expected_filters['orderbykey'][] = $option->key;
            // Make the first sort filter default.
            if (empty($sorts)) {
                $option->default = true;
            }
            $sorts[] = new option($option->key, $option->name, $option->default ?? false);
        }

        // Return sort option objects.
        return $sorts;
    }

    /**
     * @param array $params
     * @return string
     */
    public static function get_sort_value(array $params): string {
        if (isset($params['orderbykey'])) {
            if (is_array($params['orderbykey'])) {
                $orderbykey = $params['orderbykey'][0];
            } else {
                $orderbykey = $params['orderbykey'];
            }
        } elseif (isset($params['catalog_fts'])) {
            $orderbykey = 'score';
        }

        return $orderbykey ?? 'featured';
    }

    /**
     * @param array $params
     * @param array $errors
     * @return void
     */
    public static function validate_objecttype(array $params, array &$errors): void {
        if (isset($params['objecttype'])) {
            /** @var provider $provider_class */
            $object_types = [];
            foreach (provider_handler::instance()->get_active_providers() as $provider_class) {
                $object_types[] = $provider_class::get_object_type();
            }

            if (is_array($params['objecttype'])) {
                foreach ($params['objecttype'] as $object_type) {
                    if (!in_array($object_type, $object_types)) {
                        $errors[] = "'{$object_type}' is not invalid objecttype or not enabled.";
                    }
                }
            } elseif (is_string($params['objecttype']) && !in_array($object_type = $params['objecttype'], $object_types)) {
                $errors[] = "'{$object_type}' is not invalid objecttype or not enabled.";
            }
        }
    }

    /**
     * @param string $query_strings
     * @return bool
     */
    public static function validate_active_filters(string $query_strings) : bool {
        $filter_handler = filter_handler::instance();
        $query = [];
        parse_str($query_strings, $query);
        $keys = ['orderbykey'];
        foreach ($filter_handler->get_active_filters() as $filter) {
            $keys[] = $filter->datafilter->get_alias();
        }

        foreach (array_keys($query) as $query_key) {
            if (!in_array($query_key, $keys)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Updates query input parameters, making the pagination cursor transparent. This overrides
     * any values of limitfrom, maxcount, and cursor that were part of user input.
     *
     * @param array $params query input parameters
     * @return array input parameters including correct pagination fields
     */
    public static function resolve_cursor(array $params): array {
        // Need a limitfrom field to check.
        if (!isset($params['limitfrom'])) {
            $params['limitfrom'] = self::STARTING_LIMITFROM;
        }

        // Always reset maxcount and reported cursor.
        $params['maxcount'] = self::STARTING_MAXCOUNT;
        $params['cursor'] = null;

        // No cursor, return.
        if (empty($params['limitfrom'])) {
            return $params;
        }

        // Limitfrom should be a cache key that resolves to the actual limitfrom and maxcount values.
        $cache = cache::make('totara_catalog', 'pagination_cursors');
        $cache_data = $cache->get($params['limitfrom']);
        if (empty($cache_data)) {
            // There was no cache, so reset limitfrom and return.
            $params['limitfrom'] = self::STARTING_LIMITFROM;
            return $params;
        }

        // Re-use the cache key as cursor.
        $params['cursor'] = $params['limitfrom'];

        // Set pagination fields from cache.
        $params['limitfrom'] = $cache_data['limitfrom'];
        $params['maxcount'] = $cache_data['maxcount'];

        return $params;
    }

    /**
     * Creates (or reuses) a pagination cursor from the provided pagination field values.
     *
     * @param int $limitfrom
     * @param int $maxcount
     * @param string|null $cache_key
     * @return string a valid pagination cursor
     */
    public static function create_cursor(int $limitfrom, int $maxcount, ?string $cache_key = null): string {
        $cache = cache::make('totara_catalog', 'pagination_cursors');
        $cache_key = $cache_key ?? uniqid();
        $result = $cache->set($cache_key, ['limitfrom' => $limitfrom, 'maxcount' => $maxcount]);
        return $result ? $cache_key: '0';
    }
}
