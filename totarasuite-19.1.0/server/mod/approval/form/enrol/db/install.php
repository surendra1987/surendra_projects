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

use approvalform_enrol\installer;
use mod_approval\plugininfo\approvalform;

defined('MOODLE_INTERNAL') || die();

/**
 * Database installation.
 */
function xmldb_approvalform_enrol_install() {
    global $CFG;

    // Enable me.
    approvalform::enable_plugin('enrol', true);

    // Register all approvalform_enrol built-in notifications.
    require_once("{$CFG->dirroot}/totara/notification/db/upgradelib.php");
    totara_notification_sync_built_in_notification('approvalform_enrol');
}
