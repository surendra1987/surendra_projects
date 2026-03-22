<?php
/**
 * This file is part of Totara Learn
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

namespace theme_inspire\quickaccessmenu;

use \totara_core\quickaccessmenu\group;
use \totara_core\quickaccessmenu\item;

class inspire implements \totara_core\quickaccessmenu\provider {

    /**
     * Return the items that core_user wishes to introduce to the quick access menu.
     *
     * @return item[]
     */
    public static function get_items(): array {
        global $USER, $PAGE;

        // Do not show this for admin user.
        if (is_siteadmin($USER)) {
            return [];
        }

        // Do not show this if theme is not inspire.
        if ($PAGE->theme->name !== 'inspire') {
            return [];
        }

        return [
            item::from_provider(
                'inspire_editor',
                group::get(group::CONFIGURATION),
                new \lang_string('pluginname', 'theme_inspire'),
                1000
            )
        ];
    }
}
