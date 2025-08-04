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

namespace performelement_linked_review\helper\lifecycle;

use core\collection;
use core\entity\user;
use mod_perform\entity\activity\participant_instance as pi_entity;
use mod_perform\entity\activity\participant_section;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\section_element;
use mod_perform\state\participant_instance\not_started;
use mod_perform\state\participant_instance\progress_not_applicable;
use mod_perform\state\participant_section\availability_not_applicable;
use mod_perform\state\participant_section\open;
use performelement_linked_review\linked_review;
use performelement_linked_review\entity\linked_review_content;

/**
 * Holds the conditions required for creating a linked review content object.
 *
 * This class already incorporates a set of common conditions that must all be
 * fulfilled if a linked review content object can be created. However, there
 * can be additional custom conditions as well; these will be evaluated after
 * the common conditions pass.
 *
 * The default conditions for allowing creations are:
 * 1) the current user is the content selector
 * 2) the section in which the linked review content appears is available to the
 *    content selector ie still editable
 * 3a) There are no content selections for the linked review question
 * 3b) OR all other participants are view only
 * 3c) OR all other participants instances' progress are not 'not started'
 */
final class creation_conditions {
    // Failed result messages.
    public const ERR_OTHERS_ALREADY_PROGRESSED = 'ERR_OTHERS_ALREADY_PROGRESSED';
    public const ERR_SECTION_NOT_EDITABLE = 'ERR_SECTION_NOT_EDITABLE';
    public const ERR_USER_IS_NOT_SELECTOR = 'ERR_USER_IS_NOT_SELECTOR';

    /**
     * Virtual constructor.
     *
     * @param section_element $section_element section holding the linked review
     *        question.
     * @param participant_instance $participant_instance participant attempting
     *        to create linked review content.
     * @param callable[] $additional_verifiers more linked_review_content->result
     *        functions to evaluate whether the linked review content can be
     *        removed. These verifiers run_after the default ones_.
     */
    public static function create(
        section_element $section_element,
        participant_instance $participant_instance,
        array $additional_verifiers=[]
    ) {
        $builtin_verifiers = [
            self::verify_selector_user(...),
            self::verify_selector_section_editable(...),
            self::verify_others_progress_if_content_exists(...)
        ];

        $verifiers = array_merge($builtin_verifiers, $additional_verifiers);
        return new self(
            $section_element, $participant_instance, collection::new($verifiers)
        );
    }

    /**
     * Checks if the current user is one who selected the linked review content.
     *
     * @param section_element $section_element section holding the linked review
     *        question.
     * @param participant_instance $participant_instance participant attempting
     *        to create linked review content.
     *
     * @return evaluation_result the result of the check.
     */
    private static function verify_selector_user(
        section_element $section_element,
        participant_instance $participant_instance,
    ): evaluation_result {
        $participant_uid = (int)$participant_instance->participant_id;

        // External users will always get a null value for user::logged_in().
        // Therefore this creation condition will always fail for them, even if
        // it is a external user's participant instance that is passed in. Which
        // is ok because current business rules state that external users cannot
        // select linked review content anyway.
        if ($participant_uid === (int)user::logged_in()?->id) {
            $element = $section_element->element;
            $is_selector = linked_review::get_content_selector_roles($element)
                ->has('id', $participant_instance->core_relationship->id);

            if ($is_selector) {
                return evaluation_result::passed();
            }
        }

        return evaluation_result::failed(
            self::ERR_USER_IS_NOT_SELECTOR,
            get_string(
                'create_condition_failed:user_is_not_selector',
                'performelement_linked_review'
            )
        );
    }

    /**
     * Checks if the selector's participant section is editable.
     *
     * @param section_element $section_element section holding the linked review
     *        question.
     * @param participant_instance $participant_instance participant attempting
     *        to create linked review content.
     *
     * @return evaluation_result the result of the check.
     */
    private static function verify_selector_section_editable(
        section_element $section_element,
        participant_instance $participant_instance,
    ): evaluation_result {
        // Believe it or not, the current PA design allows a readonly role in a
        // section to select the linked review content in that section. This is
        // why the section availability criteria also includes the 'read only'
        // status.
        $allowed = [open::get_code(), availability_not_applicable::get_code()];

        $selector_progress_valid = participant_section::repository()
            ->as('ps')
            ->join(
                [pi_entity::TABLE, 'pi'], 'ps.participant_instance_id', 'pi.id'
            )
            ->where('ps.section_id', $section_element->section_id)
            ->where_in('ps.availability', $allowed)
            ->where(
                'pi.subject_instance_id',
                $participant_instance->subject_instance_id
            )
            ->where('pi.participant_id', $participant_instance->participant_id)
            ->exists();

        return $selector_progress_valid
            ? evaluation_result::passed()
            : evaluation_result::failed(
                self::ERR_SECTION_NOT_EDITABLE,
                get_string(
                    'create_condition_failed:section_not_editable',
                    'performelement_linked_review'
                )
            );
    }

    /**
     * Checks if the other participant instances progress is valid if there is
     * already linked review content.
     *
     * @param section_element $section_element section holding the linked review
     *        question.
     * @param participant_instance $participant_instance participant attempting
     *        to create linked review content.
     *
     * @return evaluation_result the result of the check.
     */
    private static function verify_others_progress_if_content_exists(
        section_element $section_element,
        participant_instance $participant_instance,
    ): evaluation_result {
        // Current business rules state that linked review content can always be
        // created if it does not already exist for the given section element,
        // whatever the other participants' progress.
        $content_exists = linked_review_content::repository()
            ->where('section_element_id', $section_element->id)
            ->where('subject_instance_id', $participant_instance->subject_instance_id)
            ->exists();
        if (!$content_exists) {
            return evaluation_result::passed();
        }

        // If it gets to here, then linked review content does exist; so need to
        // check other participants' progress.
        $allowed = [
            not_started::get_code(), progress_not_applicable::get_code()
        ];

        $others_already_progressed = participant_section::repository()
            ->as('ps')
            ->join(
                [pi_entity::TABLE, 'pi'], 'ps.participant_instance_id', 'pi.id'
            )
            ->where('ps.section_id', $section_element->section_id)
            ->where(
                'pi.subject_instance_id',
                $participant_instance->subject_instance_id
            )
            ->where(
                'pi.participant_id', '!=', $participant_instance->participant_id
            )
            ->where_not_in('pi.progress', $allowed)
            ->exists();

        return $others_already_progressed
                ? evaluation_result::failed(
                    self::ERR_OTHERS_ALREADY_PROGRESSED,
                    get_string(
                        'create_condition_failed:others_already_progressed',
                        'performelement_linked_review'
                    )
                )
                : evaluation_result::passed();
    }

    /**
     * Default constructor.
     *
     * @param section_element $section_element section holding the linked review
     *        question.
     * @param participant_instance $participant_instance participant attempting
     *        to create linked review content.
     * @param collection<callable> $verifiers complete set of verifier functions
     *        to evaluate in turn. If any of these verifiers fail, the linked
     *        review content cannot be removed.
     */
    private function __construct(
        private readonly section_element $section_element,
        private readonly participant_instance $participant_instance,
        private readonly collection $verifiers
    ) {
        // EMPTY BLOCK.
    }

    /**
     * Indicates whether linked review content can be created in its parent
     * performance activity.
     *
     * @return evaluation_result the result of the check.
     */
    public function evaluate(): evaluation_result {
        return $this->verifiers->reduce(
            fn (evaluation_result $acc, callable $fn): evaluation_result =>
                $acc->is_fulfilled
                    ? $fn($this->section_element, $this->participant_instance)
                    : $acc,
            evaluation_result::passed()
        );
    }
}