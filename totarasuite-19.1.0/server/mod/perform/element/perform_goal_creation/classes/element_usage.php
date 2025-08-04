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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 */

namespace performelement_perform_goal_creation;

use mod_perform\models\activity\helpers\element_usage as base_element_usage;
use totara_core\advanced_feature;

/**
 * Configuration of where the personal goal creation element can be used.
 *
 * @package performelement_perform_goal_creation
 */
class element_usage extends base_element_usage {

    /**
     * @inheritDoc
     */
    public function get_can_be_child_element(): bool {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function get_can_be_top_level_element(): bool {
        return advanced_feature::is_enabled('perform_goals');
    }

}
