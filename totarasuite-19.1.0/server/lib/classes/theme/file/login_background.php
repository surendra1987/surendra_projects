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
 * @package core
 */

namespace core\theme\file;

use core\files\type\file_type;
use core\files\type\web_image;
use theme_config;

/**
 * Handles the login_background image in the theme settings.
 */
class login_background extends theme_file {

    /**
     * resource constructor.
     *
     * @param theme_config|null $theme_config
     */
    public function __construct(?theme_config $theme_config = null) {
        parent::__construct($theme_config);
        $this->type = new web_image();
    }

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'totara_core/default_login_background';
    }

    /**
     * @inheritDoc
     */
    public function get_component(): string {
        return 'totara_core';
    }

    /**
     * @inheritDoc
     */
    public function get_area(): string {
        return 'loginbackground';
    }

    /**
     * @inheritDoc
     */
    public function get_ui_key(): string {
        return 'siteloginbackground';
    }

    /**
     * @inheritDoc
     */
    public function get_ui_category(): string {
        return 'images';
    }

    /**
     * @inheritDoc
     */
    public function get_type(): file_type {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function get_default_categories(): array {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function is_available(): bool {
        // Check if setting is enabled.
        $settings = $this->get_theme_settings_instance();
        return $settings->is_enabled('images', 'formimages_field_displaylogin', true);
    }
}