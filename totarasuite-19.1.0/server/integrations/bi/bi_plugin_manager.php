<?php
/**
 * This file is part of Totara Learn
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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package core_bi
 */

use core\plugininfo\bi;
use core\hook\business_intelligence_enable;

/**
 * Allows the admin to manage bi plugins
 *
 */
class bi_plugin_manager {
    /** @var object the url of the manage bi plugins page */
    private $pageurl;

    /** @var string component name */
    private string $component = 'bi';

    /**
     * Constructor for this bi plugin manager
     */
    public function __construct() {
        $this->pageurl = new moodle_url('/integrations/bi/managebi.php');
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

        $url = $this->pageurl;

        if ($action === 'delete') {
            $url = core_plugin_manager::instance()->get_uninstall_url($this->component . '_' . $plugin, 'manage');
            if (!$url) {
                return '&nbsp;';
            }
            return html_writer::link($url, get_string('uninstallplugin', 'core_admin'));
        }

        return $OUTPUT->action_icon(new moodle_url($url,
                array('action' => $action, 'plugin' => $plugin, 'sesskey' => sesskey())),
                new pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null, array('title' => $alt)) . ' ';
    }

    /**
     * Write the HTML for the bi plugins table.
     *
     */
    private function view_plugins_table(): void {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir . '/tablelib.php');

        $plugins = bi::get_all_plugins();

        // Set up the table.
        $this->view_header();
        $table = new flexible_table($this->component . 'pluginsadminttable');
        $table->define_baseurl($this->pageurl);
        $table->define_columns(array('pluginname', 'version', 'hideshow', 'settings', 'uninstall'));
        $table->define_headers(array(get_string('plugin'), get_string('version'), get_string('enable'), get_string('settings'), get_string('uninstallplugin', 'core_admin')));
        $table->set_attribute('id', $this->component . 'plugins');
        $table->set_attribute('class', 'admintable generaltable');
        $table->setup();

        foreach ($plugins as $idx => $plugin) {
            $row = array();
            $class = '';
            $name = $plugin->name;
            $component = $this->component . "_" . $plugin->name;

            $row[] = get_string('pluginname', $component);
            $row[] = get_config($component, 'version');

            $visible = get_config($component, 'enabled');

            if ($visible) {
                $row[] = $this->format_icon_link('hide', $name, 't/hide', get_string('disable'));
            } else {
                // Trigger a hook to check if any other components want to prevent enabling of this plugin.
                $hook = new business_intelligence_enable($name);
                $hook->execute();
                $class = 'dimmed_text';

                if ($hook->prevent_enable === true) {
                    $a = ['pluginname' => $hook->pluginname, 'reason' => $hook->prevent_enable_reason];
                    $reason = get_string('error:cannot_enable_business_intelligence_plugin', 'plugin', $a);
                    $row[] = $OUTPUT->flex_icon('show', array('classes' => 'ft-state-disabled', 'alt' => $reason, 'title' => $reason));
                } else {
                    $row[] = $this->format_icon_link('show', $name, 't/show', get_string('enable'));
                }
            }

            $exists = file_exists($CFG->dirroot . '/integrations/bi/' . $name . '/settings.php');
            if ($row[1] != '' && $exists) {
                $row[] = html_writer::link(new moodle_url('/admin/settings.php',
                    array('section' => $component)), get_string('settings'));
            } else {
                $row[] = '&nbsp;';
            }

            $row[] = $this->format_icon_link('delete', $name, 't/delete', get_string('uninstallplugin', 'core_admin'));

            $table->add_data($row, $class);
        }

        $table->finish_output();
        $this->view_footer();
    }

    /**
     * Write the page header
     *
     */
    private function view_header(): void {
        global $OUTPUT, $CFG;
        require_once($CFG->dirroot . '/lib/adminlib.php');

        admin_externalpage_setup('manage' . $this->component . 'plugins');
        // Print the page heading.
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('bi', 'totara_core'), 1);
    }

    /**
     * Write the page footer
     *
     */
    private function view_footer(): void {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    /**
     * Check this user has permission to edit the list of installed plugins
     *
     */
    private function check_permissions(): void {
        // Check permissions.
        require_login();
        $systemcontext = context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    /**
     * Hide this plugin.
     *
     * @param string $plugin - The plugin to hide
     * @return string The next page to display
     */
    public function hide_plugin(string $plugin): string {
        set_config('enabled', 0, $this->component . '_' . $plugin);
        core_plugin_manager::reset_caches();
        return 'view';
    }

    /**
     * Show this plugin.
     *
     * @param string $plugin - The plugin to show
     * @return string The next page to display
     */
    public function show_plugin(string $plugin): string {
        // Trigger a hook to check if any other components want to prevent enabling of this plugin.
        $hook = new business_intelligence_enable($plugin);
        $hook->execute();

        if ($hook->prevent_enable === true) {
            $a = ['pluginname' => $hook->pluginname, 'reason' => $hook->prevent_enable_reason];
            print_error('error:cannot_enable_business_intelligence_plugin', 'plugin', '', $a);
        }

        set_config('enabled', 1, $this->component . '_' . $plugin);
        core_plugin_manager::reset_caches();
        return 'view';
    }

    /**
     * This is the entry point for this controller class.
     *
     * @param string|null $plugin - Optional name of a plugin type to perform the action on
     * @param string|null $action - The action to perform
     */
    public function execute(?string $action, ?string $plugin): void {
        if ($action == null) {
            $action = 'view';
        }

        $this->check_permissions();

        // Process.
        if ($action == 'hide' && $plugin != null) {
            $action = $this->hide_plugin($plugin);
        } else if ($action == 'show' && $plugin != null) {
            $action = $this->show_plugin($plugin);
        }

        // View.
        if ($action == 'view') {
            $this->view_plugins_table();
        }
    }
}