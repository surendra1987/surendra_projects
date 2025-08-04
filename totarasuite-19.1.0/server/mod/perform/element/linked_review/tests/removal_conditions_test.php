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
 * @package performelement_linked_review
 */

use core_phpunit\testcase;
use mod_perform\constants;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\participant_section;
use mod_perform\state\participant_instance\complete;
use mod_perform\state\participant_instance\in_progress;
use mod_perform\state\participant_instance\not_started;
use mod_perform\state\participant_instance\progress_not_applicable;
use mod_perform\state\participant_section\availability_not_applicable;
use mod_perform\state\participant_section\closed;
use mod_perform\state\participant_section\open;
use mod_perform\testing\generator as perform_generator;
use performelement_linked_review\helper\lifecycle\removal_conditions;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\testing\generator;
use totara_core\relationship\relationship;

/**
 * Tests for removal conditions that are independent of the linked review content
 * type. There could be type specific condition but these should be verified by
 * tests elsewhere.
 *
 * @group perform
 * @group perform_element
 * @group perform_linked_review
 * @group perform_linked_review_removal
 */
class performelement_linked_review_removal_conditions_test extends testcase {
    public function test_user_not_selector(): void {
        [$linked_review, $subject_pi, $manager_pi, $appraiser_pi] =
            $this->setup_env(
                subject_progress_class: in_progress::class,
                manager_progress_class: not_started::class
            );

        $conditions = removal_conditions::create($linked_review);

        // The subject is the selector; the manager cannot remove the linked
        // review content.
        $this->setUser($manager_pi->participant_user->to_record());

        $result = $conditions->evaluate();
        self::assertFalse($result->is_fulfilled);
        self::assertEquals(
            removal_conditions::ERR_USER_IS_NOT_SELECTOR, $result->code
        );

        // Neither can the readonly participant.
        $this->setUser($appraiser_pi->participant_user->to_record());

        $result = $conditions->evaluate();
        self::assertFalse($result->is_fulfilled);
        self::assertEquals(
            removal_conditions::ERR_USER_IS_NOT_SELECTOR, $result->code
        );

        // Running as the subject should allow the removal.
        $this->setUser($subject_pi->participant_user->to_record());
        self::assertTrue($conditions->evaluate()->is_fulfilled);
    }

    public function test_selector_section_unavailable(): void {
        [$linked_review, $subject_pi, , ] = $this->setup_env(
            subject_progress_class: complete::class,
            manager_progress_class: not_started::class
        );

        $conditions = removal_conditions::create($linked_review);

        // Running as the subject should allow the removal because his section
        // is still editable even if he has completed it.
        $this->setUser($subject_pi->participant_user->to_record());
        self::assertTrue($conditions->evaluate()->is_fulfilled);

        // Whoops, selector somehow closed his section.
        $this->set_ps_availability(
            $subject_pi,
            $linked_review->section_element->section_id,
            closed::class
        );

        $result = $conditions->evaluate();
        self::assertFalse($result->is_fulfilled);
        self::assertEquals(
            removal_conditions::ERR_SECTION_NOT_EDITABLE, $result->code
        );
    }

    public function test_others_already_progressed(): void {
        [$linked_review, $subject_pi, $manager_pi] = $this->setup_env(
            subject_progress_class: in_progress::class,
            manager_progress_class: in_progress::class
        );
        $conditions = removal_conditions::create($linked_review);

        // Manager has started on the activity. Therefore cannot remove.
        $this->setUser($subject_pi->participant_user->to_record());

        $result = $conditions->evaluate();
        self::assertFalse($result->is_fulfilled);
        self::assertEquals(
            removal_conditions::ERR_OTHERS_ALREADY_PROGRESSED, $result->code
        );

        // Manager's progress mysteriously go back to not started. Now can
        // remove.
        $this->set_pi_progress($manager_pi, not_started::class);
        self::assertTrue($conditions->evaluate()->is_fulfilled);
    }

    public function test_others_are_view_only(): void {
        [$linked_review, $subject_pi, ] = $this->setup_env(
            subject_progress_class: in_progress::class,
            manager_progress_class: progress_not_applicable::class
        );
        $conditions = removal_conditions::create($linked_review);

        // Selector has started but other participants are view only. Removal
        // always allowed.
        $this->setUser($subject_pi->participant_user->to_record());
        self::assertTrue($conditions->evaluate()->is_fulfilled);
    }

    public function test_user_is_external_user(): void {
        [$linked_review, $subject_pi, , ] = $this->setup_env(
            subject_progress_class: in_progress::class,
            manager_progress_class: not_started::class
        );

        // Set up an external user for this linked review item. This is just
        // done here for completeness. It actually does not matter; see comments
        // below.
        $generator = perform_generator::instance();
        $generator->create_section_relationship(
            $linked_review->section_element->section,
            ['relationship' => constants::RELATIONSHIP_EXTERNAL]
        );

        [, $external_user, ] = $generator->generate_external_participant_instances(
            $subject_pi->subject_instance_id,
            ['fullname' => 'External user', 'email' => 'mytest@example.com']
        );

        // The key thing about external users is they access the activity via a
        // token; they are _never allowed to log in_. It is the lack of a login
        // that causes the removal conditions to be false for these users.
        self::assertNotEmpty($external_user->token);
        self::setUser(null);

        $result = removal_conditions::create($linked_review)->evaluate();
        self::assertFalse($result->is_fulfilled);
        self::assertEquals(
            removal_conditions::ERR_USER_IS_NOT_SELECTOR, $result->code
        );
    }

    /**
     * Updates the participant instance with the specified progress.
     *
     * @param participant_instance $pi participant instance to update.
     * @param string $progress_class new progress to set.
     *
     * @return participant_instance the updated participant instance.
     */
    private function set_pi_progress(
        participant_instance $pi,
        string $progress_class
    ): participant_instance {
        return $pi->set_attribute('progress', $progress_class::get_code())
            ->save()
            ->refresh();
    }

    /**
     * Updates the participant section for the given participant instance and
     * section with the specified progress.
     *
     * @param participant_instance $pi participant instance to update.
     * @param int $section_id section for which to set the availability.
     * @param string $availability_class new availability to set.
     */
    private function set_ps_availability(
        participant_instance $pi,
        int $section_id,
        string $availability_class
    ): void {
        $pi->participant_sections
            ->filter(
                fn (participant_section $ps): bool =>
                    (int)$ps->section_id === $section_id
            )
            ->map(
                fn (participant_section $ps): participant_section => $ps
                    ->set_attribute('availability', $availability_class::get_code())
                    ->save()
                    ->refresh()
            );
    }

    /**
     * Creates test data.
     *
     * This creates a linked review content question with these details:
     * - the content selector is the activity subject
     * - the subject's manager is a participant
     * - the appraiser is a read only participant
     * - all participants' sections are available (or read only where relevant)
     *
     * @param string $subject_progress_class initial progress for the subject
     *        participant instance.
     * @param string $manager_progress_class initial progress for manager
     *        participant instance.
     *
     * @return mixed[] test data tuple comprising these:
     *         - [linked_review_content] generated linked review content
     *         - [participant_instance] participant instance of the subject in
     *           the activity
     *         - [participant_instance] participant instance of the manager in
     *           the activity
     *         - [participant_instance] participant instance of the appraiser in
     *           the activity
     */
    private function setup_env(
        string $subject_progress_class,
        string $manager_progress_class
    ): array {
        self::setAdminUser();

        $generator = generator::instance();

        // By default this creates a competency linked review item that sets the
        // subject as the content selector.
        [$activity, $section, , $section_element] = $generator
            ->create_activity_with_section_and_review_element();

        // Set a subject as a participant in the activity.
        [, $si, $spi, ] = $generator->create_participant_in_section(
            ['activity' => $activity, 'section' => $section]
        );

        // Set another user as a manager participant in the activity.
        [, , $mpi, ] = $generator->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
                'subject_instance' => $si,
                'relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_MANAGER
                )
            ]
        );

        // Set a third user as an readonly appraiser in the activity.
        [, , $api, ] = $generator->create_participant_in_section(
            [
                'activity' => $activity,
                'section' => $section,
                'subject_instance' => $si,
                'relationship' => relationship::load_by_idnumber(
                    constants::RELATIONSHIP_APPRAISER
                )
            ]
        );

        // Note the following:
        // - this test is for the removal conditions independent of the content
        //   type; the "selected" content does not matter here. Hence the dummy
        //   content id for the first parameter.
        // - this test is for removing already selected content; it does not
        //   matter who originally "selected" the content. What matters is the
        //   subject is "recorded" as being the selector. Hence the false as the
        //   last parameter.
        $content = linked_review_content::create(
            231, $section_element->id, $spi->id, false
        );

        $section_id = $section_element->section_id;
        $this->set_ps_availability($spi, $section_id, open::class);
        $this->set_ps_availability($mpi, $section_id, open::class);
        $this->set_ps_availability(
            $api, $section_id, availability_not_applicable::class
        );

        return [
            $content,
            $this->set_pi_progress($spi, $subject_progress_class),
            $this->set_pi_progress($mpi, $manager_progress_class),
            $this->set_pi_progress($api, progress_not_applicable::class)
        ];
    }

}