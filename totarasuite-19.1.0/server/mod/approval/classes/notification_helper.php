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
* @author Nathan Lewis <nathan.lewis@totara.com>
* @package mod_approval
*/

namespace mod_approval;

use mod_approval\model\application\application;
use mod_approval\model\form\approvalform_base;
use totara_notification\external_helper;
use totara_notification\resolver\notifiable_event_resolver;

/**
 * Helper which figures out which notification resolver to call based on the application's form.
 */
class notification_helper {
    public static function trigger_notification(string $resolver_class_name, array $data, application $application): void {
        // Check if the application's form provides a replacement for all the core approval notification resolvers.
        $plugin_name = $application->get_workflow_version()->get_form_version()->get_form()->plugin_name;
        $plugin = approvalform_base::from_plugin_name($plugin_name);

        if ($plugin::replaces_base_notifications()) {
            // Figure out the form's notification resolver class name.
            $parts = explode("\\", $resolver_class_name);
            $resolver_name = end($parts);

            $resolver_class_name = "approvalform_{$plugin_name}\\totara_notification\\resolver\\{$resolver_name}";

            if (!class_exists($resolver_class_name) || !is_subclass_of($resolver_class_name, notifiable_event_resolver::class)) {
                // If it doesn't exist or it isn't a notification resolver then the event shouldn't be triggered for this form.
                return;
            }
        }

        // Trigger the notification event.
        external_helper::create_notifiable_event_queue(new $resolver_class_name($data));
    }
}