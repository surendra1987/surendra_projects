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
 * @package perform_goal
 */

namespace perform_goal\performelement_linked_review;

use mod_perform\entity\activity\element_response_snapshot;
use mod_perform\hook\post_element_response_submission;
use mod_perform\models\response\section_element_response;
use perform_goal\model\goal;
use performelement_linked_review\entity\linked_review_content;
use performelement_linked_review\linked_review;
use totara_core\advanced_feature;

/**
 * Hook watcher, creates goal snapshots for a linked element review response, when submitted.
 * Works in tandem with the goal_snapshot class for perform_goal linked element review.
 */
class post_response_submission {

    /**
     * Process the response submission hook.
     *
     * @param post_element_response_submission $hook
     */
    public static function process_response(post_element_response_submission $hook): void {
        // Only process linked_review element responses.
        if (!$hook->matches_element_plugin(linked_review::class)) {
            return;
        }

        // Only process if perform_goals is enabled.
        if (!advanced_feature::is_enabled('perform_goals')) {
            return;
        }

        // Load the response.
        $response_id = $hook->get_response_id();
        $section_element_response = section_element_response::load_by_id($response_id);

        // Load the perform_goal linked_review_content records for this response.
        $linked_review_contents = linked_review_content::repository()
            ->where('section_element_id', '=', $section_element_response->section_element->id)
            ->where('subject_instance_id', '=', $hook->get_participant_instance()->subject_instance_id)
            ->where('selector_id', '=', $hook->get_participant_instance()->participant_id)
            ->where('content_type', '=', 'perform_goal')
            ->get();

        // Each content record contains a goal id.
        $perform_goal_ids = [];
        foreach ($linked_review_contents as $linked_review_content) {
            $perform_goal_ids[] = $linked_review_content->content_id;
        }

        // Load existing snapshots (if any).
        $snapshots_here = element_response_snapshot::repository()
            ->where('response_id', '=', $response_id)
            ->where('item_type', '=', goal_snapshot::ITEM_TYPE)
            ->get()
            ->map_to(goal_snapshot::class);

        // Delete any snapshots that are no longer required.
        foreach ($snapshots_here as $snapshot) {
            /** @var goal_snapshot $snapshot */
            if (empty($perform_goal_ids) || !in_array($snapshot->item_id, $perform_goal_ids)) {
                $snapshot->delete();
            }
        }

        if (!empty($perform_goal_ids)) {
            foreach ($perform_goal_ids as $goal_id) {
                $goal = goal::load_by_id($goal_id);

                // Update any existing snapshots.
                if ($snapshots_here->has('item_id', $goal_id)) {
                    $snapshot = $snapshots_here->find('item_id', $goal_id);
                    $snapshot->update_snapshot();
                } else {
                    // Create any new snapshots required.
                    goal_snapshot::create($section_element_response, $goal);
                }
            }
        }
    }
}