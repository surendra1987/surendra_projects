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
 * @package totara_competency
 */

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_instance\not_started;
use mod_perform\state\participant_section\open;
use pathway_perform_rating\models\perform_rating;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator as lrc_generator;
use totara_competency\entity\assignment;
use totara_competency\performelement_linked_review\competency_assignment;
use totara_competency\testing\generator;
use totara_core\relationship\relationship;

/**
 * Tests the removal conditions for linked reviews with competency assignments.
 *
 * There could be other removal conditions but independent of the actual linked
 * review content type; see mod_perform performelement_linked_review for those.
 *
 * @group perform
 * @group pathway_perform_rating
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_removal
 * @group totara_competency
 */
class totara_competency_perform_linked_competencies_remove_conditions_test extends testcase {
    public function test_competency_assignment_removed(): void {
        [$linked_review, $assignment, $subject_pi] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Goal rated; now cannot remove.
        $competency_id = $assignment->competency_id;
        perform_rating::create(
            $competency_id,
            null,
            $subject_pi->id,
            $linked_review->section_element_id,
        );
        self::assertFalse($linked_review->can_remove);

        // Now delete the competency assignment. Linked review item magically
        // becomes removable again.
        assignment::repository()->where('id', $assignment->id)->delete();
        self::assertTrue($linked_review->can_remove);
    }

    public function test_rated(): void {
        [$linked_review, $assignment, $subject_pi] = $this->setup_env();
        $this->setUser($subject_pi->participant_user->to_record());

        // Competency has not been rated so can remove.
        $competency_id = $assignment->competency_id;
        $rating = perform_rating::get_existing_rating(
            $competency_id, $subject_pi->subject_instance_id
        );
        self::assertNull($rating);
        self::assertTrue($linked_review->can_remove);

        // Goal rated; now cannot remove.
        perform_rating::create(
            $competency_id,
            null,
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
     *         - [stdClass] associated competency assignment
     *         - [participant_instance] participant instance of the subject in
     *           the activity
     */
    private function setup_env(): array {
        self::setAdminUser();

        $data = [
            'content_type' => competency_assignment::get_identifier(),
            'content_type_settings' => [
                'enable_rating' => true,
                'rating_relationship' => relationship::load_by_idnumber(
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

        $competency_generator = generator::instance();
        $competency = $competency_generator->create_competency();
        $competency_assignment = $competency_generator
            ->assignment_generator()
            ->create_user_assignment($competency->id, $spi->participant_id);

        // Note this test is for removing already selected content; it does not
        // matter who originally "selected" the competency. What matters is the
        // subject is "recorded" as being the selector. Hence the false as the
        // last parameter.
        $content = linked_review_content::create(
            $competency_assignment->id, $section_element->id, $spi->id, false
        );

        $section_id = $section_element->section_id;
        $spi = $this->set_type_independent_conditions(
            $spi, $section_id, in_progress::class
        );

        $this->set_type_independent_conditions(
            $ppi, $section_id, not_started::class
        );

        return [$content, $competency_assignment, $spi];
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