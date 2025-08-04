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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Nathan Lewis <nathan.lewis@totara.com>
 * @package totara_notification
 * @depreacted since T19, use notifications.php instead
 */

use totara_core\extended_context;

global $CFG;

require_once(__DIR__ . '/../../config.php');

// Get URL parameters
$context_id = optional_param('context_id', context_system::instance()->id, PARAM_INT);
$component = optional_param('component', extended_context::NATURAL_CONTEXT_COMPONENT, PARAM_TEXT);
$area = optional_param('area', extended_context::NATURAL_CONTEXT_AREA, PARAM_TEXT);
$item_id = optional_param('item_id', extended_context::NATURAL_CONTEXT_ITEM_ID, PARAM_INT);

debugging('This file has been deprecated. Use notifications.php instead.', DEBUG_DEVELOPER);

redirect(new moodle_url("/totara/notification/notifications.php",
    [
        'context_id' => $context_id,
        'component' => $component,
        'area' => $area,
        'item_id' => $item_id,
    ]
));