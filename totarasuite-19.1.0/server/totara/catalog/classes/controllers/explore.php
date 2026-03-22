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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_catalog
 */

namespace totara_catalog\controllers;

use context;
use context_system;
use moodle_url;
use totara_catalog\local\config;
use totara_catalog\provider_handler;
use totara_core\advanced_feature;
use totara_mvc\controller;
use totara_mvc\tui_view;

/**
 * Controller for catalog explore page.
 */
class explore extends controller {
    protected $layout = 'columnpage';

    public function setup_context(): context {
        return context_system::instance();
    }

    /**
     * @return tui_view
     */
    public function action(): tui_view {
        global $CFG, $SESSION, $PAGE, $OUTPUT;

        $this->set_url(new moodle_url('/totara/catalog/explore.php'));

        // If the catalogtype is not 'explore', redirect to the old entry point.
        if ($CFG->catalogtype !== 'explore') {
            $query_string = $_SERVER['QUERY_STRING'];
            $url = '/totara/catalog/index.php';
            if ($query_string) {
                $url .= '?' . $query_string;
            }
            redirect(new moodle_url($url));
        }

        $this->check_editing();
        // Get rid of debugging info.
        $PAGE->set_url(new moodle_url('/totara/catalog/explore.php'));

        $config = config::instance();
        $filters = $this->execute_graphql_operation('totara_catalog_filters')['data']['totara_catalog_filters'];
        $PAGE->blocks->load_blocks();

        $on_ms_teams = isset($SESSION->theme) && $SESSION->theme === 'msteams';
        $has_bottom_blocks = $PAGE->blocks->is_known_region('bottom') && !empty($PAGE->blocks->get_content_for_region('bottom', $OUTPUT));

        $props = [
            'filters' => $filters,
            'hideWhenShowingResults' => '#block-region-bottom',
            'buttons' => $this->get_buttons(),
            'config' => [
                'enableDetails' => (bool)$config->get_value('details_content_enabled'),
            ],
            'alwaysShowResults' => $on_ms_teams || !$has_bottom_blocks,
            'subtitle' => $this->get_page_subtitle(),
            'hideHeaderButtons' => $on_ms_teams,
        ];

        return static::create_tui_view('totara_catalog/explore/ExplorePage', $props)
            ->set_title(get_string('explore', 'totara_catalog'));
    }

    /**
     * Check if we need to enable/disable editing.
     */
    protected function check_editing(): void {
        global $USER;
        $edit = optional_param('edit', -1, PARAM_BOOL);
        if (!isset($USER->editing)) {
            $USER->editing = 0;
        }
        if ($this->get_page()->user_allowed_editing()) {
            if ($edit == 1 && confirm_sesskey()) {
                $USER->editing = 1;
                redirect($this->url);
            } elseif ($edit == 0 && confirm_sesskey()) {
                $USER->editing = 0;
                redirect($this->url);
            }
        } else {
            $USER->editing = 0;
        }
    }

    /**
     * Get data for header buttons.
     *
     * @return array
     */
    protected function get_buttons(): array {
        global $USER;

        $extra_buttons = [];
        $create_buttons = [];

        // Customise this page
        if ($this->get_page()->user_allowed_editing()) {
            if (!empty($USER->editing)) {
                $extra_buttons[] = (object)[
                    'label' => get_string('customiseoff', 'totara_dashboard'),
                    'url' => (new \moodle_url($this->url, ['edit' => '0', 'sesskey' => sesskey()]))->out(),
                ];
            } else {
                $extra_buttons[] = (object)[
                    'label' => get_string('customiseon', 'totara_dashboard'),
                    'url' => (new \moodle_url($this->url, ['edit' => '1', 'sesskey' => sesskey()]))->out(),
                ];
            }
        }

        // Configure catalog
        if (has_capability('totara/catalog:configurecatalog', \context_system::instance())) {
            $extra_buttons[] = (object)[
                'label' => get_string('catalog:configurecatalog', 'totara_catalog'),
                'url' => (new \moodle_url('/totara/catalog/config.php'))->out(),
            ];
        }

        // Ask providers for buttons
        foreach (provider_handler::instance()->get_active_providers() as $provider) {
            $extra_buttons = array_merge($extra_buttons, $provider->get_buttons());
            $create_buttons = array_merge($create_buttons, $provider->get_create_buttons());
        }

        // Existing code returns URL already HTML-escaped :'(
        $fix_button = fn($x) => [...(array)$x, 'url' => html_entity_decode($x->url ?? '')];
        $extra_buttons = array_map($fix_button, $extra_buttons);
        $create_buttons = array_map($fix_button, $create_buttons);

        return [
            'extra' => $extra_buttons,
            'create' => $create_buttons,
        ];
    }

    /**
     * @return string
     */
    protected function get_page_subtitle(): string {
        if (advanced_feature::is_enabled('engage_resources') && advanced_feature::is_enabled('container_workspace')) {
            return get_string('explore_page_subtitle_learning_resources_workspaces', 'totara_catalog');
        }

        if (advanced_feature::is_enabled('engage_resources') && advanced_feature::is_disabled('container_workspace')) {
            return get_string('explore_page_subtitle_learning_resources', 'totara_catalog');
        }

        if (advanced_feature::is_disabled('engage_resources') && advanced_feature::is_enabled('container_workspace')) {
            return get_string('explore_page_subtitle_learning_workspaces', 'totara_catalog');
        }

        return get_string('explore_page_subtitle_learning', 'totara_catalog');
    }
}
