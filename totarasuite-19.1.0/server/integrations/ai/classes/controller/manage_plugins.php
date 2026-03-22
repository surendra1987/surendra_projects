<?php
/**
 * This file is part of Totara Core
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_ai
 */

namespace core_ai\controller;

use coding_exception;
use core\plugininfo\ai;
use core_ai\subsystem;
use core_plugin_manager;
use flexible_table;
use html_writer;
use moodle_url;
use pix_icon;

/**
 * Controller for manage plugins page.
 *
 * Note this is not a standard MVC controller.
 *
 */
class manage_plugins {
    /** @var object the url of the manage plugins page */
    private $page_url;

    /**
     * Constructor for this ai plugin manager
     */
    public function __construct(moodle_url $url) {
        $this->page_url = $url;
    }

    /**
     * Render the manage plugins page.
     *
     * @return void
     */
    public function render(): void {
        // View.
        $this->view_header();
        $this->view_plugins_table();
        $this->view_interactions_table();
        $this->view_footer();
    }

    /**
     * This is the entry point for this controller class.
     *
     * @param string $plugin - Name of a plugin type to perform the action on
     * @param string $action - The action to perform
     */
    public function execute(string $plugin, string $action): void {
        // Process hide, show, default.
        switch ($action) {
            case 'hide':
                subsystem::disable_plugin($plugin);
                break;
            case 'show':
                subsystem::enable_plugin($plugin);
                break;
            case 'default':
                subsystem::set_default_plugin($plugin);
                break;
            default:
                throw new coding_exception("Unknown action '$action'");
        }
    }

    /**
     * Construct a table object for the plugins table, and tell it to write to output.
     */
    private function view_plugins_table(): void {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir . '/tablelib.php');

        // Header.
        echo $OUTPUT->heading(get_string('connectors', 'ai'), 3);

        // Load plugins, show notification if none found.
        $plugins = subsystem::get_ai_plugins();
        $plugin_type = subsystem::PLUGIN_TYPE;
        if (empty($plugins)) {
            echo $OUTPUT->notification(get_string('no_plugins_installed', 'ai'), 'notifymessage');
            return;
        }

        // Set up the table.
        $table = new flexible_table("$plugin_type-pluginsadminttable");
        $table->define_baseurl($this->page_url);
        $table->define_columns([
            'pluginname',
            'supported_features',
            'hideshow',
            'default',
            'settings',
            'version',
            'uninstall',
        ]);

        $table->define_headers([
            get_string('connector', 'ai'),
            get_string('supported_features', 'ai'),
            get_string('enable'),
            get_string('default_heading', 'ai'),
            get_string('settings'),
            get_string('version'),
            get_string('uninstallplugin', 'core_admin'),
        ]);
        $table->set_attribute('id', "$plugin_type-plugins");
        $table->set_attribute('class', 'admintable generaltable');
        $table->setup();
        $default_plugin = subsystem::get_default_plugin();

        foreach ($plugins as $plugin) {
            $row = [];
            $class = '';
            $component = $plugin_type . "_" . $plugin->name;

            $row[] = $plugin->displayname;

            $features = [];
            foreach ($plugin->get_supported_features() as $supported_feature) {
                $features[] = $supported_feature::get_name();
            }
            $row[] = implode('<br>', $features);

            $is_enabled = $plugin->is_enabled() ?? false;
            if ($is_enabled) {
                $row[] = $this->format_icon_link('hide', $plugin->name, 't/hide', get_string('disable'));
            } else {
                $class = 'dimmed_text';
                $enable_check = $plugin->get_enable_check();
                if ($enable_check->can_be_enabled()) {
                    $row[] = $this->format_icon_link('show', $plugin->name, 't/show', get_string('enable'));
                } else {
                    // Only show reasons if plugin cannot be enabled.
                    $row[] = html_writer::alist($enable_check->get_reasons());
                }
            }

            // mark as default
            if ($plugin->is_enabled()) {
                $mark_as_default_url = ai::get_manage_url();
                $mark_as_default_url->params([
                    'action' => 'default',
                    'plugin' => $plugin->name,
                    'sesskey' => sesskey(),
                ]);
                $row[] = !empty($default_plugin) && $plugin->name === $default_plugin->name
                    ? get_string('default_ai', 'ai')
                    : html_writer::link(
                        $mark_as_default_url,
                        get_string('set_as_default', 'ai')
                    );
            } else {
                $row[] = '';
            }

            // Plugin has configuration options.
            if ($is_enabled && $plugin->get_config_collection()->has_options()) {
                $settings_url = new moodle_url(
                    '/admin/settings.php',
                    ['section' => $component]
                );
                $row[] = html_writer::link(
                    $settings_url,
                    get_string('settings')
                );
            } else {
                $row[] = '';
            }

            $row[] = $plugin->versiondisk;

            $row[] = $this->format_icon_link('delete', $plugin->name, 't/delete', get_string('uninstallplugin', 'core_admin'));
            $table->add_data($row, $class);
        }

        $table->finish_output();
    }

    /**
     * Write the lists of features and interactions to output.
     *
     * @return void
     */
    private function list_features_and_interactions(): void {
        global $OUTPUT;
        $features = [];
        foreach (subsystem::get_features() as $feature) {
            $features[] = $feature::get_name();
        }
        echo $OUTPUT->container('<br>');
        echo $OUTPUT->container(get_string('available_features', 'ai', implode(', ', $features)));

        $interactions = [];
        foreach (subsystem::get_interactions() as $interaction) {
            $interactions[] = $interaction::get_name();
        }
        echo $OUTPUT->container(get_string('available_interactions', 'ai', implode(', ', $interactions)));
    }

    /**
     * Construct a table object for the interactions table, and tell it to write to output.
     *
     * @return void
     */
    private function view_interactions_table(): void {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir . '/tablelib.php');

        // Header.
        echo $OUTPUT->heading(get_string('interactions', 'ai'), 3);

        // Load interactions, show notification if none found.
        $interactions = subsystem::get_interactions();
        if (empty($interactions)) {
            echo $OUTPUT->notification(get_string('no_interactions_found', 'ai'), 'notifymessage');
            return;
        }

        // Set up the interactions table
        $table = new flexible_table("ai-interactions-table");
        $table->define_baseurl($this->page_url);
        $table->define_columns([
            'plugin',
            'description',
            'feature',
        ]);

        $table->define_headers([
            get_string('interaction', 'ai'),
            get_string('description'),
            get_string('feature', 'ai'),
        ]);
        $table->set_attribute('id', "ai-interactions");
        $table->set_attribute('class', 'admintable generaltable');
        $table->setup();

        foreach ($interactions as $interaction_class) {
            $row = [];
            $class = '';

            // Class name
            $row[] = $interaction_class::get_name();

            // Description
            $row[] = $interaction_class::get_description();

            // Feature
            $feature_class = $interaction_class::get_feature_class();
            $row[] = $feature_class::get_name();
            $table->add_data($row, $class);
        }

        $table->finish_output();
    }

    /**
     * Util function for writing an action icon link
     *
     * @param string $action URL parameter to include in the link
     * @param string $plugin URL parameter to include in the link
     * @param string $icon The key to the icon to use (e.g. 't/up')
     * @param string $alt The string description of the link used as the title and alt text
     * @return string The icon/link
     */
    private function format_icon_link(string $action, string $plugin, string $icon, string $alt): string {
        global $OUTPUT;

        $url = $this->page_url;

        if ($action === 'delete') {
            $url = core_plugin_manager::instance()->get_uninstall_url(subsystem::PLUGIN_TYPE . '_' . $plugin, 'manage');
            if (!$url) {
                return '&nbsp;';
            }
            return html_writer::link($url, get_string('uninstallplugin', 'core_admin'));
        }

        $moodle_url = clone $url;
        $moodle_url->params(
            [
                'action' => $action,
                'plugin' => $plugin,
                'sesskey' => sesskey(),
            ]
        );

        return $OUTPUT->action_icon(
            $moodle_url,
            new pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
            null,
            [
                'title' => $alt,
            ]
        );
    }

    /**
     * Write the page header to output.
     *
     */
    private function view_header(): void {
        global $OUTPUT, $CFG;
        require_once($CFG->dirroot . '/lib/adminlib.php');

        admin_externalpage_setup('manageaiplugins');
        // Print the page heading.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('manage_ai', 'ai'));
    }

    /**
     * Write the page footer to output.
     *
     */
    private function view_footer(): void {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }
}
