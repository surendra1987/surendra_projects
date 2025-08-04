<?php
/**
 * This file is part of Totara TXP
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
 * @author Michael Ivanov <michael.ivanov@totara.com>
 * @package bi_intellidata
 */


namespace bi_intellidata\helpers;

/**
 * Class RestrictedTablesHelper
 *
 * A helper class to determine whether a given database table is restricted or not
 */
class RestrictedTablesHelper {
// phpcs:disable Totara.NamingConventions
    /**
     * @var array $rulesExact An array of exact table names that are restricted
     */
    protected array $rulesExact;

    /**
     * @var array $rulesWildcard An array of wildcard table names that are restricted
     */
    protected array $rulesWildcard;

    /**
     * RestrictedTablesHelper constructor.
     */
    public function __construct() {
        $this->rulesWildcard = [
            '%_config',
            '%_log',
            'oauth2_%',
            '%_oauth2',
            '%_oauth2_%',
            'auth_%',
            '%_auth',
            '%_tokens',
            'totara_mobile_%',
            'totara_connect_%'
        ];

        $this->rulesExact = [
            'config',
            'config_plugins',
            'enrol_paypal',
            'external_tokens',
            'portfolio_instance_config',
            'repository_instance_config',
            'totara_mobile_tokens',
            'totara_oauth2_access_token',
            'virtualmeeting_auth',
            'user_private_key'
        ];
    }

    /**
     * Checks whether the given table matches the given wildcard pattern
     *
     * @param string $table The name of the table to check
     * @param string $wildcard The wildcard pattern to match against
     * @return bool Whether the table matches the wildcard pattern or not
     */
    protected function matchesWildcard(string $table, string $wildcard): bool {
        $parts = explode('%', $wildcard);
        $pattern = implode('.*?', array_map(fn($e) => preg_quote($e, '/'), $parts));
        return preg_match("/^$pattern$/", $table);
    }

    /**
     * Checks whether the given table is restricted or not
     *
     * @param string $table The name of the table to check
     * @return bool Whether the table is restricted or not
     */
    public function isRestricted(string $table): bool {
        $matched = in_array($table, $this->rulesExact);
        if ($matched) {
            return true;
        }
        foreach ($this->rulesWildcard as $wildcard) {
            if ($this->matchesWildcard($table, $wildcard)) {
                return true;
            }
        }
        return false;
    }
// phpcs:enable
}