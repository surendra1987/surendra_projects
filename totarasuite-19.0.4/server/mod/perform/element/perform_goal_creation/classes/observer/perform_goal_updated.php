<?php
/**
 * This file is part of Totara Perform
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
 * @author Chris Snyder <chris.snyder@totara.com>
 * @package performelement_perform_goal_creation
 */

namespace performelement_perform_goal_creation\observer;

use core\event\base as event_base;
use mod_perform\entity\activity\element_response_snapshot;
use performelement_perform_goal_creation\model\goal_snapshot;

/**
 * Local event observer for perform_goal updated events.
 */
class perform_goal_updated {

    public static function goal_updated(event_base $event) {
        $goal_id = $event->objectid;

        // Check to see if this goal has open performance activity responses.
        $snaps_to_update = element_response_snapshot::repository()
            ->part_of_open_response()
            ->where('item_type', '=', 'perform_goal')
            ->where('item_id', '=', $goal_id)
            ->get();

        // Update any that are found.
        foreach ($snaps_to_update as $snapshot_entity) {
            $snapshot_model = goal_snapshot::load_by_entity($snapshot_entity);
            $snapshot_model->update_snapshot();
        }
    }
}