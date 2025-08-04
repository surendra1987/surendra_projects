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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Scott Davies <scott.davies@totara.com>
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package perform_goal
 */

namespace perform_goal\performelement_linked_review;

use coding_exception;
use core\collection;
use core\date_format;
use core\entity\user;
use core\format;
use core\webapi\formatter\field\date_field_formatter;
use mod_perform\entity\activity\element_response;
use mod_perform\entity\activity\element_response_snapshot;
use mod_perform\entity\activity\participant_section as participant_section_entity;
use mod_perform\entity\activity\participant_section;
use mod_perform\entity\activity\section_relationship;
use mod_perform\models\activity\participant_instance;
use mod_perform\models\activity\section_element;
use mod_perform\models\activity\subject_instance;
use perform_goal\entity\goal;
use perform_goal\entity\goal as goal_entity;
use perform_goal\formatter\goal as goal_formatter;
use perform_goal\formatter\perform_status_change as perform_status_change_formatter;
use perform_goal\formatter\status;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal_category;
use perform_goal\model\perform_status_change;
use perform_goal\model\status\status_helper;
use performelement_linked_review\content_type;
use performelement_linked_review\linked_review;
use performelement_linked_review\helper\lifecycle\evaluation_result;
use performelement_linked_review\models\linked_review_content;
use performelement_linked_review\rb\helper\content_type_response_report;
use performelement_perform_goal_creation\model\goal_snapshot as goal_snapshot_model;
use stdClass;
use totara_core\advanced_feature;
use totara_core\relationship\relationship as relationship_model;
use totara_core\relationship\relationship;

/**
 * A content type for a performance activity 'linked review' with goal(s).
 */
class perform_goal_content_type extends content_type {
    /**
     * The format type to use when formatting strings for output.
     */
    protected const TEXT_FORMAT = format::FORMAT_PLAIN;

    // Removal check result messages.
    public const ERR_ALREADY_RATED = 'ERR_ALREADY_RATED';

    /**
     * @inheritDoc
     * @return string
     */
    public static function get_component(): string {
        return 'perform_goal';
    }

    /**
     * @inheritDoc
     */
    public static function is_enabled(): bool {
        return advanced_feature::is_enabled('perform_goals');
    }

    /**
     * @inheritDoc
     */
    public static function get_admin_settings_component(): ?string {
        return 'perform_goal/components/performelement_linked_review/AdminEdit';
    }

    /**
     * @inheritDoc
     */
    public static function get_available_settings(): array {
        return [
            'enable_status_change' => false,
            'status_change_relationship' => null,
        ];
    }

    /**
     * @param array $settings
     * @return array
     */
    public static function get_display_settings(array $settings): array {
        $display_settings = [];

        $status_change_enabled = $settings['enable_status_change'] ?? false;
        $display_settings[get_string('goal_enable_goal_progress_change', 'perform_goal')] = $status_change_enabled
            ? get_string('yes', 'core')
            : get_string('no', 'core');

        if ($status_change_enabled && !empty($settings['status_change_relationship'])) {
            $display_settings[get_string('goal_enable_goal_progress_change_participant', 'perform_goal')] =
                relationship_model::load_by_id($settings['status_change_relationship'])->get_name();
        }

        return $display_settings;
    }

    /**
     * Append the actual human readable name of the status changing relationship if changing status is enabled.
     *
     * @param array $content_type_settings
     * @return array
     */
    public static function get_content_type_settings(array $content_type_settings): array {
        if (empty($content_type_settings['status_change_relationship']) || !$content_type_settings['enable_status_change']) {
            return $content_type_settings;
        }

        $relationship = relationship::load_by_id($content_type_settings['status_change_relationship']);
        $content_type_settings['status_change_relationship_name'] = $relationship->get_name();

        return $content_type_settings;
    }

    /**
     * Remove/clean any unwanted settings attributes before saving.
     *
     * @param array $content_type_settings
     * @return array
     */
    public static function clean_content_type_settings(array $content_type_settings): array {
        if ($content_type_settings['enable_status_change'] === false) {
            $content_type_settings['status_change_relationship'] = null;
        }
        unset($content_type_settings['status_change_relationship_name']);

        return $content_type_settings;
    }

    /**
     * @inheritDoc
     */
    public static function get_content_picker_component(): string {
        return 'perform_goal/components/performelement_linked_review/ParticipantContentPicker';
    }

    /**
     * @inheritDoc
     */
    public static function get_participant_content_component(): string {
        return 'perform_goal/components/performelement_linked_review/ParticipantContent';
    }

    /**
     * @inheritDoc
     */
    public static function get_admin_view_component(): string {
        return 'perform_goal/components/performelement_linked_review/AdminView';
    }

    /**
     * @inheritDoc
     */
    public static function get_participant_content_footer_component(): string {
        return 'perform_goal/components/performelement_linked_review/ChangeStatusForm';
    }

    /**
     * @inheritDoc
     */
    public static function get_admin_content_footer_component(): string {
        return 'perform_goal/components/performelement_linked_review/ChangeStatusFormPreview';
    }

    /**
     * @inheritDoc
     */
    public static function get_response_report_helper(): content_type_response_report {
        return new response_report();
    }

    /**
     * @inheritDoc
     */
    public static function get_identifier(): string {
        return 'perform_goal';
    }

    /**
     * @inheritDoc
     */
    public static function get_display_name(): string {
        return get_string('perform_goal', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public static function get_generic_display_name(): string {
        return get_string('goal_generic_display_name', 'perform_goal');
    }

    /**
     * @inheritDoc
     */
    public static function get_table_name(): string {
        return goal_entity::TABLE;
    }

    /**
     * @inheritDoc
     */
    public function load_content_items(
        subject_instance $subject_instance,
        collection $content_items,
        ?participant_section_entity $participant_section,
        bool $can_view_other_responses,
        int $created_at
    ): array {
        if ($content_items->count() === 0) {
            return [];
        }

        [$can_view_status, $can_change_status] = self::get_goal_status_permissions(
            $content_items,
            $participant_section,
            $can_view_other_responses
        );
        // Get goal snapshots for this section element.
        $section_element_id = $content_items->first()->section_element_id;
        $goal_records = element_response_snapshot::repository()
            ->as('ers')
            ->select('ers.*')
            ->join([element_response::TABLE, 'er'], 'ers.response_id', 'er.id')
            ->where('er.section_element_id', $section_element_id)
            ->where('ers.item_type', goal_snapshot::ITEM_TYPE)
            ->where_in('ers.item_id', $content_items->pluck('content_id'))
            ->get()
            ->map(
                fn (element_response_snapshot $snapshot_entity): stdClass
                    => goal_snapshot_model::load_by_id($snapshot_entity->id)->get_snapshot()
            );

        // This method can be called before snapshots are created. In this case get them from the goal table.
        if ($goal_records->count() < $content_items->count()) {
            $goal_records = $this->append_missing_goal_records(
                $goal_records,
                $content_items,
                $subject_instance->subject_user_id
            );
        }

        $deleted_goal_ids_in_content_items = $this->get_deleted_goal_ids_in_content_items($content_items);

        return $goal_records
            ->key_by('id')
            ->map(
                function (stdClass $goal_record) use ($subject_instance, $can_change_status, $deleted_goal_ids_in_content_items) {
                    $goal_exists = !in_array($goal_record->id, $deleted_goal_ids_in_content_items);
                    return $this->create_result_item(
                        $goal_record,
                        $subject_instance,
                        $goal_exists && $can_change_status
                    );
                }
            )
            ->all(true);
    }

    /**
     * Get the IDs for the goals that are in the content items but don't exist in the goal table anymore.
     * Even though they may have snapshot data, we have to know if they are deleted from the goal table by now.
     *
     * @param collection $content_items
     * @return array
     */
    private function get_deleted_goal_ids_in_content_items(collection $content_items): array {
        $content_item_goal_ids = $content_items->pluck('content_id');

        $existing_goal_ids =  goal::repository()
            ->where_in('id', $content_item_goal_ids)
            ->get()
            ->pluck('id');

        return array_diff($content_item_goal_ids, $existing_goal_ids);
    }

    /**
     * Fetch goal records that were missing in the snapshot table from the goal table instead.
     *
     * @param collection $goal_records
     * @param collection $content_items
     * @param int $subject_user_id
     * @return collection
     */
    private function append_missing_goal_records(
        collection $goal_records,
        collection $content_items,
        int        $subject_user_id
    ): collection {
        $found_goal_records_ids = $goal_records->pluck('id');

        $missing_content_ids = $content_items->filter(
            fn (linked_review_content $content_item): bool
                => !in_array($content_item->content_id, $found_goal_records_ids)
        );

        $missing_goals =  goal_entity::repository()
            ->where('user_id', $subject_user_id)
            ->where_in('id', $missing_content_ids->pluck('content_id'))
            ->get();

        /** @var goal_entity $missing_goal_record */
        foreach ($missing_goals as $missing_goal_record) {
            $goal_records->append($missing_goal_record->to_record());
        }

        return $goal_records;
    }

    /**
     * Create the data for one goal content item
     *
     * @param stdClass $goal_record
     * @param subject_instance $subject_instance
     * @param bool $can_change_status
     * @return array
     */
    private function create_result_item(
        stdClass $goal_record,
        subject_instance $subject_instance,
        bool $can_change_status
    ): array {
        $goal_formatter = new goal_formatter($goal_record, $this->context);

        $existing_status_change = perform_status_change::get_existing_status($goal_record->id, $subject_instance->id);

        $can_view = false;
        if (isset($goal_record->user_id)) {
            $goal_interactor = goal_interactor::for_user(new user($goal_record->user_id));
            $can_view = $goal_interactor->can_view_personal_goals();
        }

        return [
            'id' => $goal_formatter->format('id'),
            'goal' => [
                'id' => $goal_formatter->format('id'),
                'plugin_name' => goal_category::load_by_id($goal_record->category_id)->plugin_name,
                'name' => $goal_formatter->format('name', self::TEXT_FORMAT),
                'description' => $goal_formatter->format('description', format::FORMAT_HTML),
                'status' => $this->format_status($goal_record->status),
                'target_date' => $goal_formatter->format('target_date', date_format::FORMAT_DATE),
                'target_value' => $goal_formatter->format('target_value'),
                'current_value' => $goal_formatter->format('current_value'),
                'updated_at' => $goal_formatter->format('updated_at', date_format::FORMAT_DATE),
            ],
            'available_statuses' => array_map(
                fn(string $status_code) => $this->format_status($status_code),
                status_helper::all_status_codes('perform_goal')
            ),
            'can_change_status' => $can_change_status,
            'status_change' =>  $existing_status_change
                ? $this->format_status_change($existing_status_change)
                : null,
            'permissions' => ['can_view' => $can_view]
        ];
    }

    /**
     * Format the status change, making sure the data runs through our formatters
     *
     * @param perform_status_change $perform_status_change
     * @return array
     */
    private function format_status_change(perform_status_change $perform_status_change): array {
        $status_changer_user = null;
        // User can be null when userdata purge happened.
        if ($perform_status_change->status_changer_user) {
            $status_changer_user = [
                'fullname' => fullname($perform_status_change->status_changer_user->to_record())
            ];
        }

        $formatted_date = (new date_field_formatter(date_format::FORMAT_DATE, $this->context))
            ->format($perform_status_change->created_at);

        $status = $this->format_status($perform_status_change->status);

        $status_change_formatter = new perform_status_change_formatter($perform_status_change, $this->context);

        return [
            'created_at' => $formatted_date,
            'status_changer_user' => $status_changer_user,
            'status' => $status,
            'current_value' => $status_change_formatter->format('current_value'),
        ];
    }

    /**
     * Find out if a participant can view and change status for the given content items.
     *
     * @param collection $content_items
     * @param participant_section|null $participant_section
     * @param bool $can_view_other_responses
     * @return array
     */
    public static function get_goal_status_permissions(
        collection $content_items,
        ?participant_section $participant_section,
        bool $can_view_other_responses
    ): array {
        $can_change_status = false;
        $can_view_status = $can_view_other_responses;

        if ($participant_section) {
            // Element will be the same across all content items, so we can just get it from the first content item.
            /** @var linked_review_content $content_item */
            $content_item = $content_items->first();
            $participant_instance = participant_instance::load_by_entity($participant_section->participant_instance);

            $can_change_status = self::can_change_status($participant_instance, $content_item->section_element);

            // If users are in a status changer relationship or can view other responses they can view the status change
            $can_view_status = $can_change_status || $can_view_other_responses;
        }

        return [$can_view_status, $can_change_status];
    }

    /**
     * Check if changing status is enabled and the participant is permitted to make a status change.
     *
     * @param participant_instance $participant_instance
     * @param section_element $section_element
     * @return bool
     */
    private static function can_change_status(participant_instance $participant_instance, section_element $section_element): bool {
        /** @var linked_review $linked_review_plugin */
        $linked_review_plugin = $section_element->element->element_plugin;
        if (!$linked_review_plugin instanceof linked_review) {
            throw new coding_exception('The section element with ID ' . $section_element->id . ' is not a linked_review element');
        }
        $content_settings = $linked_review_plugin->get_content_settings($section_element->element);

        if (!$content_settings['enable_status_change'] || empty($content_settings['status_change_relationship'])) {
            return false;
        }

        if ((int)$content_settings['status_change_relationship'] !== (int)$participant_instance->core_relationship_id) {
            return false;
        }

        return participant_section::repository()
            ->join([section_relationship::TABLE, 'sr'], 'section_id', 'section_id')
            ->where('sr.core_relationship_id', $content_settings['status_change_relationship'])
            ->where('participant_instance_id', $participant_instance->id)
            ->where('section_id', $section_element->section_id)
            ->exists();
    }

    /**
     * @param string $status_code
     * @return array
     */
    protected function format_status(string $status_code): array {
        $status = status_helper::status_from_code($status_code);
        $status_formatter = new status($status, $this->context);

        return [
            'id' => $status_formatter->format('id'),
            'label' => $status_formatter->format('label'),
        ];
    }

    /**
     * @inheritDoc
     *
     * The linked review content cannot be removed if there is an existing
     * _perform goal rating_ ie an entry in the perform_goal_perform_status_change
     * table.
     */
    public function can_remove(
        linked_review_content $content
    ): evaluation_result {
        $rating = perform_status_change::get_existing_status(
            $content->content_id, $content->subject_instance_id
        );

        return is_null($rating)
            ? evaluation_result::passed()
            : evaluation_result::failed(
                self::ERR_ALREADY_RATED,
                get_string(
                    'remove_condition_failed:already_rated', 'perform_goal'
                )
            );
    }
}
