<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author  Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package container_workspace
 */
defined('MOODLE_INTERNAL') || die();

function container_workspace_update_hidden_workspace_with_audience_visibility(): void {
    global $DB, $CFG;

    if (!defined('COHORT_VISIBLE_ENROLLED')) {
        require_once("{$CFG->dirroot}/totara/core/totara.php");
    }

    $DB->execute(
        'UPDATE "ttr_course" SET audiencevisible = :new_audience_visible
         WHERE containertype = :workspace AND visible = 0 AND audiencevisible = :audience_visible',
        [
            'workspace' => 'container_workspace',
            'audience_visible' => COHORT_VISIBLE_ALL,
            'new_audience_visible' => COHORT_VISIBLE_ENROLLED
        ]
    );
}

function container_workspace_upgrade_migrate_messages() {
    totara_notification_sync_built_in_notification('container_workspace');
}

/**
 * Make sure workspace roles have the correct context levels assigned.
 *
 * @param string $archetype
 * @return void
 */
function container_workspace_upgrade_role_context_levels(string $archetype): void {
    global $CFG, $DB;

    require_once("{$CFG->dirroot}/lib/accesslib.php");

    $roles = get_archetype_roles($archetype);
    $context_levels = get_default_contextlevels($archetype);

    foreach ($roles as $role) {
        // Only update if none are set – if they've already set a context level then let's not override
        if ($DB->count_records('role_context_levels', ['roleid' => $role->id]) > 0) {
            continue;
        }

        set_role_contextlevels($role->id, $context_levels);
    }
}

/**
 * Updates the message_providers table to add/remove workspace notification
 * messages.
 */
function container_workspace_sync_built_in_notification() {
    totara_notification_sync_built_in_notification('container_workspace');
}

/**
 * Updates the message_providers table with the new 'container_workspace' set of message providers
 * 'transfer_ownership' notification message is deprecated(removed from message_providers table)
 *
 * @return void
 */
function container_workspace_upgrade_message_update_providers(): void {
    message_update_providers('container_workspace');
}