<?php
/**
 * This file is part of Totara Perform
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package totara_flavour
 */

namespace totara_flavour;

use totara_core\advanced_feature;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for flavours with Perform
 */
abstract class flavour_with_perform extends definition {
    public function additional_activation_steps() {
        parent::additional_activation_steps();

        // Have to set these explicitly because they don't exist as admin_setting.
        advanced_feature::enable('perform_goals');
        advanced_feature::disable('goals');
    }
}
