<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @package    totara_plan
 * @subpackage navigation
 * @author     Oleg Demeshev <oleg.demeshev@totaralms.com>
 */

namespace totara_plan\totara\menu;

use totara_core\advanced_feature;

class recordoflearning extends \totara_core\totara\menu\item {

    protected $default_icon = 'book';

    protected function get_default_title() {
        global $CFG, $SESSION;
        // If top-level item in Inspire theme, then use short name.
        if ($this->parentid == 0) {
            // This is meant to be temporary (hah!) until we create a Learn landing page.
            $theme_config = \theme_config::load($SESSION->theme ?? $CFG->theme);
            if ($theme_config->is_or_inherits_from('inspire')) {
                return get_string('recordoflearning_inspire', 'totara_plan');
            }
        }
        return get_string('recordoflearning', 'totara_plan');
    }

    protected function get_default_url() {
        return '/totara/plan/record/index.php';
    }

    public function get_default_sortorder() {
        return 29000;
    }

    protected function check_visibility() {
        if (!isloggedin() or isguestuser()) {
            return false;
        }
        return true;
    }

    /**
     * Is this menu item completely disabled?
     *
     * @return bool
     */
    public function is_disabled() {
        return advanced_feature::is_disabled('recordoflearning');
    }
}
