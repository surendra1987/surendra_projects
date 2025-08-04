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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package approvalform_enrol
 */

namespace approvalform_enrol\watcher;

use approvalform_enrol\enrol;
use mod_approval\form\approvalform_core_enrol_base;
use mod_approval\hook\workflow_version_pre_activate;

class workflow_version {

    /**
     * Handles the workflow_version_pre_activate hook call.
     *
     * @param workflow_version_pre_activate $hook
     */
    public static function handle_workflow_version_pre_activate(workflow_version_pre_activate $hook): void {
        $workflow_version = $hook->get_workflow_version();

        // Only watch our plugin's hooks.
        if (!is_a($workflow_version->workflow->plugin, enrol::class)) {
            return;
        }

        // Get any verification warnings.
        $warnings = approvalform_core_enrol_base::verify_workflow_structure($workflow_version);
        if (count($warnings) < 1) {
            // All good.
            return;
        }

        // One or more warnings: do not activate.
        $hook->do_not_activate();

        // Report back.
        foreach ($warnings as $warning) {
            $hook->add_warning($warning);
        }
    }
}