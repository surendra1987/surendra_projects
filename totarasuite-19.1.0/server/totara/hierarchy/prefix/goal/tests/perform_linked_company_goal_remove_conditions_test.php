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
 * @package hierarchy_goal
 */

use core_phpunit\testcase;
use hierarchy_goal\entity\company_goal_assignment as assignment_entity;
use hierarchy_goal\models\company_goal_perform_status;
use hierarchy_goal\performelement_linked_review\company_goal_assignment;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_instance\not_started;
use mod_perform\state\participant_section\open;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator as lrc_generator;
use totara_hierarchy\testing\generator;
use totara_core\relationship\relationship;

/**
 * Tests the removal conditions for linked reviews with legacy company goals.
 *
 * There could be other removal conditions but independent of the actual linked
 * review content type; see mod_perform performelement_linked_review for those.
 *
 * @group hierarchy_goal
 * @group totara_hierarchy
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_removal
 * @group totara_goal
 */
class hierarchy_goal_perform_linked_company_goal_remove_conditions_test extends testcase {
    public function test_goal_assignment_removed(): void {
        [$linked_review, $assignment, $subject_pi] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Goal rated; now cannot remove.
        company_goal_perform_status::create(
            $assignment->id,
            $assignment->scale_value->scale->defaultid,
            $subject_pi->id,
            $linked_review->section_element_id,
        );
        self::assertFalse($linked_review->can_remove);

        // Now delete the goal assignment. Linked review item magically becomes
        // removable again.
        $assignment->delete();
        self::assertTrue($linked_review->can_remove);
    }

    public function test_rated(): void {
        [$linked_review, $assignment, $subject_pi] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Goal has not been rated so can remove.
        $rating = company_goal_perform_status::get_existing_status(
            $assignment->goalid, $subject_pi->subject_instance_id
        );

        self::assertNull($rating);
        self::assertTrue($linked_review->can_remove);

        // Goal rated; now cannot remove.
        company_goal_perform_status::create(
            $assignment->id,
            $assignment->scale_value->scale->defaultid,
            $subject_pi->id,
            $linked_review->section_element_id,
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
     *         - [company_goal_assignment] associated company goal assignment
     *         - [stdClass] associated company goal scale
     *         - [participant_instance] participant instance of the subject in
     *           the activity
     */
    private function setup_env(): array {
        self::setAdminUser();

        $data = [
            'content_type' => company_goal_assignment::get_identifier(),
            'content_type_settings' => [
                'enable_status_change' => true,
                'status_change_relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_SUBJECT
                )->id
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

        $hierarchy_generator = generator::instance();
        $fw = $hierarchy_generator->create_goal_frame([]);
        $goal = $hierarchy_generator->create_goal(['frameworkid' => $fw->id]);

        $subject_uid = $spi->participant_id;
        $hierarchy_generator->goal_assign_individuals($goal->id, [$subject_uid]);
        $assignment = assignment_entity::repository()
            ->where('userid', $subject_uid)
            ->where('goalid', $goal->id)
            ->one(true);

        // Note this test is for removing already selected content; it does not
        // matter who originally "selected" the goal. What matters is the subject
        // is "recorded" as being the selector. Hence the false as the last parameter.
        $content = linked_review_content::create(
            $assignment->id, $section_element->id, $spi->id, false
        );

        $section_id = $section_element->section_id;
        $spi = $this->set_type_independent_conditions(
            $spi, $section_id, in_progress::class
        );

        $this->set_type_independent_conditions(
            $ppi, $section_id, not_started::class
        );

        return [$content, $assignment, $spi];
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