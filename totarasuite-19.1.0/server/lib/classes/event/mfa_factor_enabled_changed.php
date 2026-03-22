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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package core
 */

namespace core\event;

use moodle_url;

/**
 * Factor (MFA plugin) was enabled or disabled.
 */
class mfa_factor_enabled_changed extends base {

    /**
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
    }

    /**
     * Link in the event log report.
     *
     * @return moodle_url
     */
    public function get_url() {
        return new moodle_url("/admin/settings.php", ['section' => 'mfa_settings']);
    }

    /**
     * Name shown in the event log report.
     *
     * @return string
     */
    public static function get_name(): string {
        return get_string('event_factor_enabled_changed', 'core_mfa');
    }

    /**
     * Description shown in the event log report.
     *
     * @return string
     */
    public function get_description(): string {
        $data = $this->data['other'];

        $action = $data['action'] ?? '';
        $plugin = $data['plugin'] ?? '';

        if ('' === $action) {
            // This should never be a case, but who knows ?
            return '';
        }

        return "The user with id '{$this->userid}' {$action} the MFA plugin: '{$plugin}'";
    }

    /**
     * @param string $plugin
     * @param string $action
     *
     * @return self
     */
    private static function create_event(string $plugin, string $action): self {
        global $USER;

        $data = [
            'userid' => $USER->id,
            'contextid' => \context_system::instance()->id,
            'other' => [
                'action' => $action,
                'plugin' => $plugin
            ]
        ];

        /** @var self $event */
        $event = static::create($data);
        return $event;
    }

    /**
     * MFA plugin that is being enabled.
     *
     * @param string $plugin
     *
     * @return self
     */
    public static function enabled(string $plugin): self {
        return static::create_event($plugin, 'enabled');
    }

    /**
     * MFA plugin that is being disabled.
     *
     * @param string $plugin
     *
     * @return self
     */
    public static function disabled(string $plugin): self {
        return static::create_event($plugin, 'disabled');
    }
}