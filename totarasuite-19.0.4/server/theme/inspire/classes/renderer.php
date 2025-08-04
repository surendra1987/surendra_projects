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
 * @author Brian Barnes <brian.barnes@totara.com>
 * @package theme_inspire
 */

use core\theme\settings as theme_settings;
use totara_core\output\masthead_logo;
use totara_tui\output\component;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/totara/core/renderer.php');

class theme_inspire_totara_core_renderer extends totara_core_renderer {

    /**
     * @inheritDoc
     */
    public function masthead(bool $hasguestlangmenu = true, bool $nocustommenu = false) {
        global $CFG, $USER;
        $layout_options = $this->output->page->layout_options;

        $show_lang_menu = $hasguestlangmenu && (!isloggedin() || isguestuser()) && $CFG->langmenu;

        $mastheaddata = new stdClass();
        if (totara_core\quickaccessmenu\factory::can_current_user_have_quickaccessmenu()) {
            $menuinstance = totara_core\quickaccessmenu\factory::instance($USER->id);

            if (!empty($menuinstance->has_possible_items())) {
                $mastheaddata->masthead_quickaccessmenu = true;
            }
        }

        if (isset($layout_options['nototaramenu']) && $layout_options['nototaramenu']) {
            $logo = new masthead_logo();
            $mastheaddata->logo = $logo->export_for_template($this->output);
            $mastheaddata->has_background = true;
        }

        $mastheaddata->masthead_plugins = $this->output->navbar_plugin_output();
        $mastheaddata->masthead_usermenu = $this->output->user_menu();
        $mastheaddata->masthead_lang = $show_lang_menu ? $this->output->language_select() : '';

        return $this->render_from_template('totara_core/masthead', $mastheaddata);
    }
}

class theme_inspire_renderer extends theme_legacy_renderer {
    /** @var array Logo data. */
    protected $logo_data;

    /**
     * Get logo data for rendering.
     *
     * @return array
     */
    protected function get_logo_data(): array {
        if (!$this->logo_data) {
            $logo = new masthead_logo();
            $this->logo_data = $logo->export_for_template($this->output);
        }
        return $this->logo_data;
    }

    /**
     * Preload neccesary resources.
     *
     * @return void
     */
    public function preload(): void {
        global $PAGE;
        $logo_data = $this->get_logo_data();
        $PAGE->requires->preload('image', $logo_data['logourl']);
        $PAGE->requires->preload('image', $logo_data['logomarkurl']);
    }

    /**
     * Render menu.
     *
     * @return string
     */
    public function menu(): string {
        global $SESSION;
        $layout_options = $this->output->page->layout_options;
        if (isset($layout_options['nototaramenu']) && $layout_options['nototaramenu']) {
            return '';
        }
        $logo_data = $this->get_logo_data();

        $user = \core\entity\user::logged_in();

        $theme_config = \theme_config::load('inspire');
        $theme_settings = new theme_settings($theme_config, $user->tenantid ?? 0);

        $displaynavicons = $theme_settings->get_property('brand', 'formbrand_field_displaynavicons');
        $expandnav = $theme_settings->get_property('brand', 'formbrand_field_expandnav');

        if (isset($displaynavicons['value'])) {
            $icons_enabled = filter_var($displaynavicons['value'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $icons_enabled = true;
        }
    
        $desktop_default = 'expanded';
        if (isset($expandnav['value']) && $expandnav['value'] === 'collapse') {
            $desktop_default = 'collapsed';
        }

        if (isset($SESSION->theme_inspire_navigation_state)) {
            $user_state = $SESSION->theme_inspire_navigation_state;
        }

        $state = $user_state ?? $desktop_default;

        $placeholder_id = uniqid('placeholder_');
        $placeholder = html_writer::div(
            '',
            'theme_inspire__navPlaceholder' .
                ($icons_enabled ? ' theme_inspire__navPlaceholder--has-icons' : '') .
                ' theme_inspire__navPlaceholder--state-' . $state,
            ['id' => $placeholder_id]
        );

        $component = new component('theme_inspire/components/navigation/Navigation', [
            'menu-data' => totara_build_menu(plain_text: true),
            'site-url' => $logo_data['siteurl'],
            'logo-url' => $logo_data['logourl'],
            'logo-mark' => $logo_data['logomarkurl'],
            'logo-alt' => $logo_data['logoalt'],
            'initial-state' => $state,
            'icons-enabled' => $icons_enabled,
            'placeholder-id' => $placeholder_id,
        ]);
        return '<div class="theme_inspire__nav">' . $placeholder . $component->out_html() . '</div>';
    }
}