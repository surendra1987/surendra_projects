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
 * @package core_mfa
 */

namespace core_mfa\admin\settings;

use admin_setting;
use core\collection;
use core\notification;
use core_mfa\factor;
use core_mfa\factor_factory;
use core_mfa\mfa;
use core_plugin_manager;
use core_text;
use html_table;
use html_table_row;
use html_writer;
use moodle_url;

/**
 * Admin settings table to manage MFA factors.
 */
class manage_mfa_factors extends admin_setting {
    protected core_plugin_manager $plugin_manager;
    protected factor_factory $factor_factory;

    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct(
        ?core_plugin_manager $plugin_manager = null,
        ?factor_factory $factor_factory = null
    ) {
        $this->nosave = true;
        $this->plugin_manager = $plugin_manager ?? core_plugin_manager::instance();
        $this->factor_factory = $factor_factory ?? new factor_factory();
        parent::__construct('manage_mfa_factors', get_string('factors', 'mfa'), '', '');
    }

    /**
     * Always returns true
     *
     * @return true
     */
    public function get_setting() {
        return true;
    }

    /**
     * Always returns true
     *
     * @return true
     */
    public function get_defaultsetting() {
        return true;
    }

    /**
     * Always returns '' and doesn't write anything
     *
     * @return string Always returns ''
     */
    public function write_setting($data) {
        return '';
    }

    /**
     * Search to find if query is related to MFA plugin
     *
     * @param string $query The string to search for
     * @return bool
     */
    public function is_related($query) {
        if (parent::is_related($query)) {
            return true;
        }

        $plugins = $this->plugin_manager->get_plugins_of_type('mfa');
        foreach ($plugins as $plugin) {
            if (strpos($plugin->component, $query) !== false) {
                return true;
            }
            if (strpos(core_text::strtolower($plugin->displayname), $query) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return HTML to display control
     *
     * @param mixed $data Unused
     * @param string $query
     * @return string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $plugins_enabled = $this->plugin_manager->get_enabled_plugins('mfa');

        $factors = collection::new($this->plugin_manager->get_plugins_of_type('mfa'))
            ->map(function ($plugin) {
                return $this->factor_factory->get($plugin->name);
            });

        // Filter out button test factors
        if ((!defined('BEHAT_SITE_RUNNING') || (defined('BEHAT_SITE_RUNNING') && BEHAT_SITE_RUNNING == false))
            && (!defined('PHPUNIT_TEST') || (defined('PHPUNIT_TEST') && PHPUNIT_TEST == false))
        ) {
            $factors = $factors->filter(function (factor $factor) {
                return $factor->get_name() !== 'button';
            });
        }

        $return = $OUTPUT->heading(get_string('factors', 'mfa'), 3, 'main');

        if (mfa::has_incompatible_auth_plugins()) {
            $return .= $OUTPUT->notification(
                get_string('error:auth_plugins_compatibility', 'mfa'),
                notification::WARNING
            );
        }

        $table = new html_table();
        $table->head = [
            get_string('factor', 'mfa'),
            get_string('enable')
        ];
        $table->data  = [];

        $show_settings_column = $factors->has(function ($factor) {
            return $factor->get_plugin_info()->get_settings_url() != null;
        });

        if ($show_settings_column) {
            $table->head[] = get_string('settings');
        }

        // iterate through plugins and add to the display table
        foreach ($factors as $plugin) {
            $name = $plugin->get_name();
            $plugin_info = $plugin->get_plugin_info();
            $action_url = new moodle_url('/mfa/admin_action.php', ['plugin' => $name, 'sesskey' => sesskey()]);

            $enabled = in_array($name, $plugins_enabled);
            $icon = $enabled
                ? $OUTPUT->flex_icon('hide', ['alt' => get_string('disable')])
                : $OUTPUT->flex_icon('show', ['alt' => get_string('enable')]);
            $hideshow = html_writer::link(new moodle_url($action_url, ['action' => $enabled ? 'disable' : 'enable']), $icon);

            $settings_url = $plugin_info->get_settings_url();
            $settings = $settings_url ? html_writer::link($settings_url, get_string('settings')) : '';

            $row = new html_table_row([
                $plugin->get_display_name(),
                $hideshow,
                $show_settings_column ? $settings : ''
            ]);
            $table->data[] = $row;
        }
        $return .= $OUTPUT->render($table);
        $return .= html_writer::tag('p', get_string('tablenosave', 'admin'));
        return highlight($query, $return);
    }
}
