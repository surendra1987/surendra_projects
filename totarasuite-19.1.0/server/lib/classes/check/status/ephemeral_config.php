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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package core
 */

namespace core\check\status;

defined('MOODLE_INTERNAL') || die();

use core\check\check;
use core\check\result;

class ephemeral_config extends check {

    /**
     * Ephemeral config flags should follow the pattern "revert_TL-xxxx_until_t20" but
     * in case they do not, we can list them here, along with the version where they go away.
     *
     * @var array[$key=>$version]
     */
    public const KNOWN_EPHEMERAL_CONFIGS = [
        // Keep this for tests:
        '00unit_test_match' => 42,
        // Add exceptions below this comment:
        'allow_course_completion_reset_for_program_courses' => 20,
    ];

    /**
     * Pattern to use for matching ephemeral config flags.
     */
    protected const FLAG_PATTERN = '_until_t';

    /**
     * Get the short check name
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('checkephemeralconfig', 'admin');
    }

    /**
     * A link to a place to action this
     *
     * @return \action_link|null
     */
    public function get_action_link(): ?\action_link {
        return null;
    }

    /**
     * Return result
     * @return result
     */
    public function get_result(): result {
        global $CFG;

        // Determine Totara major version.
        $totarainfo = totara_version_info();
        list($major_version, $minor_version) = explode('.', $totarainfo->newtotaraversion);

        // Default to OK.
        $status = result::OK;
        $summary = get_string('checkephemeralconfig_ok', 'core_admin');
        $details = get_string('checkephemeralconfig_details', 'core_admin');

        // Look for matching config.php keys.
        $matches = [];
        foreach ($CFG->config_php_settings as $key => $value) {
            $until_version = static::check_key($key);
            if (!is_null($until_version)) {
                $matches[] = '$CFG->' . $key;
                if ($until_version <= $major_version) {
                    $status = result::CRITICAL;
                } elseif ($status != result::CRITICAL) {
                    $status = result::WARNING;
                }
            }
        }

        // If matches found, prepare list for details.
        if (!empty($matches)) {
            $a = '<li>' . implode('</li><li>', $matches) . '</li>';
            $details = get_string('checkephemeralconfig_details_found', 'core_admin', $a);
        }

        // If status not OK, get new summary.
        if ($status === result::WARNING) {
            $summary = get_string('checkephemeralconfig_warning', 'core_admin');
        } elseif ($status === result::CRITICAL) {
            $summary = get_string('checkephemeralconfig_critical', 'core_admin');
        }

        return new result($status, $summary, $details);
    }

    protected static function check_key($key): ?int {
        $until_version = static::check_key_against_pattern($key);
        if (is_null($until_version)) {
            $until_version = static::check_key_against_list($key);
        }
        return $until_version;
    }

    protected static function check_key_against_pattern($key): ?int {
        $until_version = null;
        $matches_pattern = strpos($key, self::FLAG_PATTERN);
        if ($matches_pattern) {
            $until_version = substr($key, $matches_pattern + strlen(self::FLAG_PATTERN));
        }
        return $until_version;
    }

    protected static function check_key_against_list($key): ?int {
        $until_version = null;
        $matches_list = array_key_exists($key, self::KNOWN_EPHEMERAL_CONFIGS);
        if ($matches_list) {
            $until_version = self::KNOWN_EPHEMERAL_CONFIGS[$key];
        }
        return $until_version;
    }
}
