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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package core_mfa
 */

namespace core_mfa;

use core_mfa\model\instance;
use core_plugin_manager;

/**
 * Base definition for all MFA factors.
 */
abstract class factor {
    /**
     * Factor name
     * @var string
     */
    protected string $name;

    /**
     * If true this MFA factor can be configured by an end user for themselves.
     *
     * @param int $user_id
     * @return bool
     */
    public function user_can_register(int $user_id): bool {
        return false;
    }

    /**
     * Get the factor name, e.g. "totp".
     *
     * @return string
     */
    public function get_name(): string {
        return $this->name;
    }

    /**
     * Get the human-readable name of the factor.
     *
     * @return string
     */
    public function get_display_name(): string {
        $plugin_info = $this->get_plugin_info();
        $plugin_info->init_display_name();
        return $plugin_info->displayname;
    }

    /**
     * Get a description of the factor.
     * @return string 
     */
    public function get_description(): string {
        return get_string('factor_description', 'mfa_' . $this->name);
    }

    /**
     * Get the plugininfo instance for this factor.
     *
     * @return \core\plugininfo\mfa
     */
    public function get_plugin_info(): \core\plugininfo\mfa {
        return core_plugin_manager::instance()->get_plugin_info('mfa_' . $this->name);
    }

    /**
     * Does this factor have UI to display on the verify screen?
     *
     * @return bool
     */
    public function has_verify_ui(): bool {
        return false;
    }

    /**
     * Format data to be passed to the verify UI.
     *
     * @param instance[] $instances
     * @return array
     */
    public function get_verify_data(array $instances): array {
        return [
            'instances' => array_map(function ($instance) {
                return ['id' => $instance->id];
            }, $instances),
        ];
    }

    /**
     * Does this factor have UI to display on the registration screen?
     *
     * @return bool
     */
    public function has_register_ui(): bool {
        return false;
    }

    /**
     * Get custom data to pass to registration UI.
     *
     * @param int $user_id
     * @return array
     */
    public function get_register_data(int $user_id): array {
        return [];
    }

    /**
     * Get title of registration screen.
     *
     * @return string|null
     */
    public function get_register_title(): string {
        return get_string('register_title', 'mfa_' . $this->name);
    }

    /**
     * Get title of verify screen.
     *
     * @return string|null
     */
    public function get_verify_title(): string {
        return get_string('verify_title', 'mfa_' . $this->name);
    }

    /**
     * Verify the user input is correct.
     *
     * @param int $user_id
     * @param array $data
     * @param instance[] $instances
     * @return bool
     */
    public function verify(int $user_id, array $data, array $instances): bool {
        return false;
    }

    /**
     * Should we increment the lockout counter on failed verifications?
     *
     * @return true 
     */
    public function should_increment_lockout() {
        // On by default, cryptographically secured factors could choose to set this to false.
        return true;
    }

    /**
     * This factor can send notifications on instance creation.
     * Override if the factor should not notify.
     *
     * @return bool
     */
    public function notify_on_instance_create(): bool {
        return true;
    }

    /**
     * This factor can send notifications on instance deletion.
     * Override if the factor should not notify.
     *
     * @return bool
     */
    public function notify_on_instance_delete(): bool {
        return true;
    }
}
