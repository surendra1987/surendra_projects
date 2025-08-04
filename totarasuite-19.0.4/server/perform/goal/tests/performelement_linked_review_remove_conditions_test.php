<?php
/*
 * This file is part of Totara Perform
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
 * @author Murali Nair <murali.nair@totara.com>
 * @package perform_goal
 */

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\state\participant_instance\in_progress as pi_in_progress;
use mod_perform\state\participant_instance\not_started as pi_not_started;
use mod_perform\state\participant_section\open;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator as lrc_generator;
use perform_goal\model\perform_status_change;
use perform_goal\model\status\in_progress;
use perform_goal\performelement_linked_review\perform_goal_content_type;
use perform_goal\testing\generator;
use perform_goal\testing\goal_generator_config;
use totara_core\relationship\relationship;

/**
 * Tests the removal conditions for linked reviews with perform goals.
 *
 * There could be other removal conditions but independent of the actual linked
 * review content type; see mod_perform performelement_linked_review for those.
 *
 * @group perform
 * @group perform_element
 * @group perform_goal
 * @group perform_linked_review
 * @group perform_linked_review_removal
 */
class perform_goal_performelement_linked_review_remove_conditions_test extends testcase {
    public function test_goal_removed(): void {
        [$linked_review, $goal, $subject_pi] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Goal rated; now cannot remove.
        perform_status_change::create(
            $subject_pi->id,
            $linked_review->section_element_id,
            in_progress::get_code(),
            0.0,
            $goal->id
        );
        self::assertFalse($linked_review->can_remove);

        // Now delete the goal. Linked review item magically becomes removable
        // again.
        $goal->delete();
        self::assertTrue($linked_review->can_remove);
    }

    public function test_rated(): void {
        [$linked_review, $goal, $subject_pi] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Goal has not been rated so can remove.
        $rating = perform_status_change::get_existing_status(
            $goal->id, $subject_pi->subject_instance_id
        );
        self::assertNull($rating);
        self::assertTrue($linked_review->can_remove);

        // Goal rated; now cannot remove.
        perform_status_change::create(
            $subject_pi->id,
            $linked_review->section_element_id,
            in_progress::get_code(),
            0.0,
            $goal->id
        );
        self::assertFalse($linked_review->can_remove);
    }

    /**
     * Creates test data.
     *
     * This creates a linked review content question with these details:
     * - the content selector is the activity subject
     * - the subject's manager is a participant
     * - subject is the rater
     *
     * @return mixed[] test data tuple comprising these:
     *         - [linked_review_content] generated linked review content
     *         - [goal] associated goal
     *         - [participant_instance] participant instance of the subject in
     *           the activity
     */
    private function setup_env(): array {
        self::setAdminUser();

        $data = [
            'content_type' => perform_goal_content_type::get_identifier(),
            'content_type_settings' => [
                'enable_status_change' => true,
                'status_change_relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_SUBJECT
                )
            ],
        ];

        $lrc_generator = lrc_generator::instance();
        [$activity, $section, , $section_element] = $lrc_generator
            ->create_activity_with_section_and_review_element($data);

        // Set a subject as a participant in the activity.
        [, $si, $spi, ] = $lrc_generator->create_participant_in_section(
            ['activity' => $activity, 'section' => $section]
        );

        // Set another user as a manager participant in the activity.
        [, , $ppi, ] = $lrc_generator->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
                'subject_instance' => $si,
                'relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_MANAGER
                )
            ]
        );

        $subject_uid = $spi->participant_id;
        $cfg = goal_generator_config::new(
            ['owner_id' => $subject_uid, 'user_id' => $subject_uid]
        );
        $goal = generator::instance()->create_goal($cfg);

        // Note this test is for removing already selected content; it does not
        // matter who originally "selected" the goal. What matters is the subject
        // is "recorded" as being the selector. Hence the false as the last parameter.
        $content = linked_review_content::create(
            $goal->id, $section_element->id, $spi->id, false
        );

        $section_id = $section_element->section_id;
        $spi = $this->set_type_independent_conditions(
            $spi, $section_id, pi_in_progress::class
        );

        $this->set_type_independent_conditions(
            $ppi, $section_id, pi_not_started::class
        );

        return [$content, $goal, $spi];
    }

    /**
     * Sets up the content type independent conditions so that they do not trip
     * for the tests in this file.
     *
     * See performelement_linked_review\helper\lifecycle\removal_conditions for
     * content type agnostic conditions that have to be fulfilled first.
     *
     * @param participant_instance $pi participant instance to update.
     * @param int $section_id section for which to set the availability.
     * @param string $progress_class new progress to set.
     *
     * @return participant_instance the updated participant instance.
     */
    private function set_type_independent_conditions(
        participant_instance $pi,
        int $section_id,
        string $progress_class
    ): participant_instance {
        $pi->participant_sections
            ->filter(
                fn (participant_section $ps): bool =>
                    (int)$ps->section_id === $section_id
            )
            ->map(
                fn (participant_section $ps): participant_section => $ps
                    ->set_attribute('availability', open::get_code())
                    ->save()
                    ->refresh()
            );

        return $pi
            ->set_attribute('progress', $progress_class::get_code())
            ->save()
            ->refresh();
    }
}