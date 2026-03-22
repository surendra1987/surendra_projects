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
 * @author Fabian Derschatta <fabian.derschatta@totara.com>
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package mod_approval
 */

namespace mod_approval\views;

use moodle_page;
use totara_mvc\view;
use totara_mvc\view_override;

/**
 * Class override_nav_breadcrumbs
 */
class override_nav_breadcrumbs_buttons implements view_override {

    /**
     * @inheritDoc
     */
    public function apply(view $view): void {
        static::remove_nav_breadcrumbs_and_buttons($view->get_page());
    }

    /**
     * Remove course navigation and settings blocks from the page.
     *
     * @param moodle_page $page
     */
    public static function remove_nav_breadcrumbs_and_buttons(moodle_page $page): void {
        $settings = $page->settingsnav->children;
        // Remove all breadcrumbs from settings.
        $settings->remove('categorysettings');
        $settings->remove('modulesettings');
        $settings->remove('courseadmin');
        $settings->remove('root');

        // Remove course-related breadcrumbs.
        $breadcrumbs = $page->navigation->children;
        $breadcrumbs->remove('courses');

        // Remove page buttons
        $page->set_button('');
    }

}
