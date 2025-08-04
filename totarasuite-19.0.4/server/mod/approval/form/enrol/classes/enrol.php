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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totaralearning.com>
 * @package mod_approval
 */

namespace approvalform_enrol;

use mod_approval\form\approvalform_core_enrol_base;

/**
 * Class enrol provides an interface to the approvalform_enrol sub-plugin.
 *
 * @package approvalform_enrol
 */
class enrol extends approvalform_core_enrol_base {
    // Stub - go look at the base class.

    /**
     * @inheritDoc
     */
    public static function replaces_base_notifications(): bool {
        return true;
    }
}