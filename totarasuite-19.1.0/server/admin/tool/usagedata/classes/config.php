<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package tool_usagedata
 */

namespace tool_usagedata;

use core\base_plugin_config;

class config extends base_plugin_config {

    const COMPONENT = 'tool_usagedata';
    const CONFIG_OPT_OUT = 'opt_out';

    const SIGNING_SERVICE_BASE_URL = 'signing_service_base_url';
    const SIGNING_SERVICE_BASE_URL_DEFAULT = 'https://analytics.totara.com';

    const SIGNING_SERVICE_ENDPOINT = 'signing_service_endpoint';
    const SIGNING_SERVICE_ENDPOINT_DEFAULT = '/upload';

    /**
     * Site identifier is sha256 hash of get_site_identifier and $CFG->wwwroot
     * Appending wwwroot is to protect against accidental sharing of unique identifiers through copied databases
     *
     * @return string
     */
    public static function get_site_identifier(): string {
        global $CFG;

        return hash('sha256', get_site_identifier() . '@' . $CFG->wwwroot);
    }

    /**
     * Return hash of the registration code and wwwroot
     *
     * @return string
     */
    public static function get_registration_hash(): string {
        global $CFG;

        if (empty($CFG->registrationcode)) {
            return '';
        }

        return hash('sha256', $CFG->registrationcode . $CFG->wwwroot);
    }

    /**
     * @return bool
     */
    public static function is_opt_out_enabled(): bool {
        return !empty(get_config(self::COMPONENT, self::CONFIG_OPT_OUT));
    }

    /**
     * get the signing url for product usage analytics
     *
     * @return string
     */
    public static function get_signing_service_base_url(): string {
        $url = get_config(self::COMPONENT, self::SIGNING_SERVICE_BASE_URL);
        if (empty($url)) {
            return self::SIGNING_SERVICE_BASE_URL_DEFAULT;
        }
        return $url;
    }

    /**
     * get the signing path for product usage analytics
     *
     * @return string
     */
    public static function get_signing_service_endpoint(): string {
        $endpoint = get_config(self::COMPONENT, self::SIGNING_SERVICE_ENDPOINT);
        if (empty($endpoint)) {
            return self::SIGNING_SERVICE_ENDPOINT_DEFAULT;
        }
        return $endpoint;
    }

    /**
     * @return string
     */
    protected static function get_component(): string {
        return self::COMPONENT;
    }
}
