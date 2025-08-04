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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\models\activity;

use coding_exception;
use core\entity\user;
use mod_perform\controllers\activity\view_user_activity;
use mod_perform\entity\activity\participant_instance as participant_instance_entity;
use mod_perform\entity\activity\subject_instance as subject_instance_entity;
use mod_perform\state\participant_section\complete as participant_section_complete;
use mod_perform\state\participant_section\in_progress as participant_section_in_progress;
use mod_perform\state\subject_instance\in_progress;

/**
 * Class for mapping a subject instance entity to an object that is structured for perform overview purposes.
 *
 * Only use this for subject instances where the subject is also a participant (both responding and view-only are OK).
 *
 * @package mod_perform\models\activity
 *
 */
class subject_instance_overview_item {

    private const DUE_SOON_DAYS = 7;

    public $id;

    public $name;

    public $url;

    public $completion_date;

    public $description;

    public $assignment_date;

    public $job_assignment;

    public $last_update;

    public $due;

    /**
     * subject_instance_overview_item constructor.
     *
     * @param int $participant_id
     * @param subject_instance_entity $subject_instance_entity
     * @throws coding_exception
     */
    public function __construct(int $participant_id, subject_instance_entity $subject_instance_entity) {
        $subject_instance_model = subject_instance::load_by_entity($subject_instance_entity);
        $activity = $subject_instance_model->get_activity();

        $this->name = $activity->name;

        $this->id = $subject_instance_entity->id;

        $participant_instance = $this->get_subjects_participant_instance($subject_instance_entity);
        if ((int)$participant_instance->participant_id !== $participant_id) {
            throw new coding_exception(
                "User with id {$participant_id}"
                . " is not the subject of the subject instance with id {$subject_instance_model->id}"
            );
        }

        $this->url = $this->get_activity_url($subject_instance_entity);

        $this->completion_date = $subject_instance_entity->completed_at;

        $this->description = $activity->description;

        $this->assignment_date = $participant_instance->created_at;

        $this->job_assignment = $subject_instance_model->job_assignment->fullname ?? null;

        $participant_section = participant_instance::load_by_entity($participant_instance)
            ->get_most_recently_progressed_participant_section();
        $this->last_update = [
            'date' => $participant_section->progress_updated_at ?? null,
            'description' => $this->get_last_updated_description($subject_instance_entity, $activity->get_multisection_setting(false)),
        ];

        $due_on = $subject_instance_model->get_due_on();
        $overdue = $due_on && $due_on->is_overdue();
        $due_date = $due_on ? $due_on->get_due_date() : null;
        $due_soon = !$overdue && $due_date && $due_date < (time() + (self::DUE_SOON_DAYS * DAYSECS));
        $this->due = [
            'date' => $due_date,
            'due_soon' => $due_soon,
            'overdue' => $overdue,
        ];
    }

    /**
     * @param subject_instance_entity $subject_instance
     * @return string|null
     */
    private function get_activity_url(subject_instance_entity $subject_instance): ?string {
        $logged_in_user_id = user::logged_in()->id;

        // Link to the participant instance of the logged-in user. Doesn't have to be the subject - it could be someone
        // else viewing the overview page who is also a participant.
        $logged_in_user_participant_instance = $subject_instance->participant_instances->find(
            fn (participant_instance_entity $participant_instance) =>
                (int)$participant_instance->participant_id === $logged_in_user_id
                && (int)$participant_instance->participant_source === participant_source::INTERNAL
        );
        if ($logged_in_user_participant_instance) {
            return view_user_activity::get_url(['participant_instance_id' => $logged_in_user_participant_instance->id])->out();
        }

        // Not a participant - doesn't get a link to the activity.
        return null;
    }

    /**
     * Get the participant instance for the subject user.
     *
     * @param subject_instance_entity $subject_instance
     * @return participant_instance_entity
     */
    private function get_subjects_participant_instance(subject_instance_entity $subject_instance): participant_instance_entity {
        $participant_instance = $subject_instance->participant_instances->find(
            fn (participant_instance_entity $participant_instance) =>
                (int)$participant_instance->participant_id === (int)$subject_instance->subject_user_id
                && (int)$participant_instance->participant_source === participant_source::INTERNAL
        );
        if (!$participant_instance) {
            throw new coding_exception(
                "User with id {$subject_instance->subject_user_id}"
                . " is not participating in subject instance with id {$subject_instance->id}"
            );
        }
        return $participant_instance;
    }

    /**
     * Get a string that describes what last happened for the subject user.
     *
     * @param subject_instance_entity $subject_instance_entity
     * @param bool $is_multi_section
     * @return string|null
     */
    private function get_last_updated_description(
        subject_instance_entity $subject_instance_entity,
        bool $is_multi_section
    ): ?string {

        // Only used for "in progress" instances.
        if ((int)$subject_instance_entity->progress !== in_progress::get_code()) {
            return null;
        }

        $participant_instance = $this->get_subjects_participant_instance($subject_instance_entity);

        $all_progress = $participant_instance->participant_sections->pluck('progress');
        $num_sections = count($all_progress);
        $progress_count = array_count_values($all_progress);
        $num_status_complete = $progress_count[participant_section_complete::get_code()] ?? 0;
        $num_status_in_progress = $progress_count[participant_section_in_progress::get_code()] ?? 0;

        // All sections complete
        if ($num_status_complete === $num_sections) {
            return get_string('subject_instance_overview_last_updated_waiting_for_others', 'mod_perform');
        }

        if ($is_multi_section) {
            // Not all complete, but at least one.
            if ($num_status_complete > 0) {
                $participant_section = participant_instance::load_by_entity($participant_instance)
                    ->get_most_recently_completed_participant_section();
                if (!$participant_section) {
                    return null;
                }
                return get_string(
                    'subject_instance_overview_last_updated_most_recent_section_completed',
                    'mod_perform',
                    $participant_section->section->title
                );
            }
        }

        if ($num_status_in_progress > 0) {
            return get_string('subject_instance_overview_last_updated_activity_viewed', 'mod_perform');
        }

        return null;
    }

    /**
     * Helper method to get a due soon count from an array of overview item collections.
     *
     * @param array $overview_items
     * @return int
     * @throws coding_exception
     */
    public static function count_due_soon(array $overview_items): int {
        $count = 0;
        foreach ($overview_items as $collection) {
            $count += $collection
                ->filter(fn (subject_instance_overview_item $overview_item) => $overview_item->due['due_soon'] === true)
                ->count();
        }
        return $count;
    }
}
