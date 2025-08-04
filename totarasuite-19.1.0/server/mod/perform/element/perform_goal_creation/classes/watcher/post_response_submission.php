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

namespace performelement_perform_goal_creation\watcher;

use mod_perform\entity\activity\element_response_snapshot;
use mod_perform\hook\post_element_response_submission;
use mod_perform\models\response\section_element_response;
use perform_goal\model\goal;
use performelement_perform_goal_creation\model\goal_snapshot;
use performelement_perform_goal_creation\perform_goal_creation;

/**
 * Hook watcher, creates personal goal snapshots for a personal goal creation response, when submitted.
 */
class post_response_submission {

    /**
     * Process the response submission hook.
     *
     * @param post_element_response_submission $hook
     */
    public static function process_response(post_element_response_submission $hook): void {
        // Only process personal goal creation element responses.
        if (!$hook->matches_element_plugin(perform_goal_creation::class)) {
            return;
        }

        // Load the response.
        $response_id = $hook->get_response_id();
        $section_element_response = section_element_response::load_by_id($response_id);

        // Get the data. We trust that by the time it gets here, it has been validated and all is
        // proper and correct with these goal ids.
        $data = $hook->get_response_data();
        $personal_goal_ids = empty($data) ? null : json_decode($data);

        // Load existing snapshots (if any).
        $snapshots_here = element_response_snapshot::repository()
            ->where('response_id', '=', $response_id)
            ->where('item_type', '=', goal_snapshot::ITEM_TYPE)
            ->get()
            ->map_to(goal_snapshot::class);

        // Delete any snapshots that are no longer required.
        foreach ($snapshots_here as $snapshot) {
            /** @var goal_snapshot $snapshot */
            if (is_null($personal_goal_ids) || !in_array($snapshot->item_id, $personal_goal_ids)) {
                $snapshot->delete();
            }
        }

        if (!is_null($personal_goal_ids)) {
            foreach ($personal_goal_ids as $goal_id) {
                $goal = goal::load_by_id($goal_id);

                // Update any existing snapshots.
                if ($snapshots_here->has('item_id', $goal_id)) {
                    $snapshot = $snapshots_here->find('item_id', $goal_id);
                    $snapshot->update_snapshot();
                } else {
                    // Create any new snapshots required.
                    $snapshot = goal_snapshot::create($section_element_response, $goal);
                }
            }
        }
    }
}