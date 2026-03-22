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
 * @package performelement_perform_goal_creation
 */

namespace performelement_perform_goal_creation;

use coding_exception;
use context_system;
use context_user;
use core\collection;
use core\date_format;
use core\entity\user;
use core\format;
use core\orm\query\order;
use core_user\formatter\user_formatter;
use mod_perform\entity\activity\element_response;
use mod_perform\entity\activity\element_response_snapshot;
use mod_perform\entity\activity\participant_section;
use mod_perform\models\activity\element;
use mod_perform\models\activity\helpers\element_usage as base_element_usage;
use mod_perform\models\activity\helpers\external_participant_token_validator;
use mod_perform\models\activity\respondable_element_plugin;
use mod_perform\models\response\element_validation_error;
use mod_perform\state\participant_section\closed as section_closed;
use perform_goal\entity\goal as goal_entity;
use perform_goal\formatter\goal as goal_formatter;
use perform_goal\formatter\goal_raw_data as goal_raw_data_formatter;
use perform_goal\interactor\goal_interactor;
use perform_goal\model\goal as goal_model;
use perform_goal\model\goal_category;
use perform_goal\model\goal_raw_data;
use perform_goal\model\status\status as goal_status;
use performelement_perform_goal_creation\model\goal_snapshot;
use totara_core\dates\date_time_setting;
use totara_core\formatter\date_time_setting_formatter;
use html_writer;

class perform_goal_creation extends respondable_element_plugin {

    /** @var array $can_view static cache for view permissions */
    private static $can_view = [];

    /** @var array $can_manage static cache for manage permissions */
    private static $can_manage = [];

    /** @var int $section_element_response_id */
    protected $section_element_response_id;

    /**
     * @inheritDoc
     */
    public function validate_response(?string $encoded_response_data, ?element $element, $is_draft_validation = false): collection {
        $created_personal_goal_ids = json_decode($encoded_response_data);

        if (!is_array($created_personal_goal_ids)) {
            throw new coding_exception('Response data must be a json encoded array.');
        }

        if (!empty($created_personal_goal_ids)) {
            // Goals must have been created before form submission.
            $existing_goals_count = goal_entity::repository()
                ->where_in('id', $created_personal_goal_ids)
                ->count();

            if (count($created_personal_goal_ids) !== $existing_goals_count) {
                throw new coding_exception('Not all personal goal ids in the response data exist.');
            }
        }
        $errors = new collection();

        if ($this->fails_required_validation(empty($created_personal_goal_ids), $element, $is_draft_validation)) {
            $error_message = get_string('error_answer_required', 'performelement_perform_goal_creation');
            $validation_error = new element_validation_error('PERSONAL_GOAL_CREATION_REQUIRED', $error_message);
            $errors->append($validation_error);
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function decode_response(?string $encoded_response_data, ?string $encoded_element_data) {
        $created_personal_goal_ids = empty($encoded_response_data) ? null : json_decode($encoded_response_data);

        if (empty($created_personal_goal_ids) || !is_array($created_personal_goal_ids)) {
            return null;
        }

        $personal_goals_in_db = goal_entity::repository()
            ->where_in('id', $created_personal_goal_ids)
            ->get();

        // The personal goals must all be for the same user.
        $user_ids = array_unique($personal_goals_in_db->pluck('user_id'));
        if (count($user_ids) > 1) {
            throw new coding_exception('Cannot have personal goals for different users in the response data.');
        }

        /*
         *  When we can't find all the goals in the DB anymore (some or all have been deleted),
         *  we may have to fall back to goal snapshot data ONLY if the participant section is closed.
         */
        $can_display_snapshots = false;
        if ($this->section_element_response_id && count($created_personal_goal_ids) > $personal_goals_in_db->count()) {
            /** @var element_response $section_element_response */
            $section_element_response = element_response::repository()->find($this->section_element_response_id);
            if ($section_element_response) {
                $participant_section = participant_section::repository()
                    ->where('section_id', $section_element_response->section_element->section->id)
                    ->where('participant_instance_id', $section_element_response->participant_instance->id)
                    ->order_by('id')
                    ->first();
                if ($participant_section) {
                    $can_display_snapshots = (int)$participant_section->availability === section_closed::get_code();
                }
            }
        }

        $is_external_participant = $this->is_external_participant();
        $goals_info = [];
        foreach ($created_personal_goal_ids as $created_personal_goal_id) {
            $permissions = [
                'can_view' => false,
                'can_manage' => false,
                'can_update_status' => false
            ];

            $personal_goal = $personal_goals_in_db->find('id', $created_personal_goal_id);
            if (is_null($personal_goal)) {
                // The goal doesn't exist anymore. Try to get the latest snapshot data.
                if ($can_display_snapshots) {
                    $goal_snapshots = element_response_snapshot::repository()
                        ->select('snapshot')
                        ->where('item_id', $created_personal_goal_id)
                        ->where('item_type', goal_snapshot::ITEM_TYPE)
                        ->where('response_id', $this->section_element_response_id)
                        ->order_by('created_at', order::DIRECTION_DESC)
                        ->get();
                    if ($goal_snapshots->count() > 0) {
                        $goal = json_decode($goal_snapshots->first()->snapshot, false);
                        if ($goal) {
                            $goal->plugin_name = goal_category::load_by_id($goal->category_id)->plugin_name;
                            $personal_goal = new goal_entity($goal, false, true);
                            $goals_info[] = json_encode([
                                'goal' => $this->format_goal_response_data($personal_goal),
                                'raw' => $this->format_goal_raw_response_data($personal_goal),
                                'permissions' => $permissions
                            ], JSON_THROW_ON_ERROR);
                        }
                    }
                }
            } else {
                // We always display the live goal (not snapshot) if it still exists.
                if (!$is_external_participant) {
                    $goal_interactor = goal_interactor::from_goal(goal_model::load_by_entity($personal_goal));
                    $permissions = [
                        'can_view' => $goal_interactor->can_view(),
                        'can_manage' => $goal_interactor->can_manage(),
                        'can_update_status' => $goal_interactor->can_set_progress()
                    ];
                }

                $goals_info[] = json_encode([
                    'goal' => $this->format_goal_response_data($personal_goal),
                    'raw' => $this->format_goal_raw_response_data($personal_goal),
                    'permissions' => $permissions
                ], JSON_THROW_ON_ERROR);
            }
        }
        return empty($goals_info) ? null : $goals_info;
    }

    /**
     * @param goal_entity $personal_goal
     * @return array
     */
    private function format_goal_raw_response_data(goal_entity $personal_goal): array {
        $goal_raw_data = new goal_raw_data(goal_model::load_by_entity($personal_goal));
        $formatter = new goal_raw_data_formatter($goal_raw_data, $goal_raw_data->context);
        $start_date_time_formatter = new date_time_setting_formatter(
            new date_time_setting($personal_goal->start_date), context_system::instance()
        );
        $target_date_time_formatter = new date_time_setting_formatter(
            new date_time_setting($personal_goal->target_date), context_system::instance()
        );

        return [
            'available_statuses' => array_map(function (goal_status $status) {
                return [
                    'id' => $status::get_code(),
                    'label' => $status::get_label(),
                ];
            }, $goal_raw_data->available_statuses),
            'description' => $formatter->format('description', format::FORMAT_RAW),
            'start_date' => [
                'iso' => $start_date_time_formatter->format('iso'),
            ],
            'target_date' => [
                'iso' => $target_date_time_formatter->format('iso'),
            ],
        ];
    }

    /**
     * @param goal_entity $personal_goal
     * @return array
     */
    private function format_goal_response_data(goal_entity $personal_goal): array {
        // user_id is nullable in the DB, but for goals created by this element, it must be set.
        $user_context = context_user::instance($personal_goal->user_id);
        $personal_goal_formatter = new goal_formatter(
            goal_model::load_by_entity($personal_goal), $user_context
        );

        $owner_formatter = new user_formatter($personal_goal->owner->to_record(), context_user::instance($personal_goal->owner_id));
        $user_formatter = new user_formatter($personal_goal->user->to_record(), $user_context);

        return [
            'id' => $personal_goal->id,
            'context_id' => $user_context->id,
            'owner' => [
                'id' => $personal_goal->owner_id,
                'username' => $owner_formatter->format('username', format::FORMAT_PLAIN),
                'firstname' => $owner_formatter->format('firstname', format::FORMAT_PLAIN),
                'lastname' => $owner_formatter->format('lastname', format::FORMAT_PLAIN),
            ],
            'user' => [
                'id' => $personal_goal->user_id,
                'username' => $user_formatter->format('username', format::FORMAT_PLAIN),
                'firstname' => $user_formatter->format('firstname', format::FORMAT_PLAIN),
                'lastname' => $user_formatter->format('lastname', format::FORMAT_PLAIN),
            ],
            'name' => $personal_goal_formatter->format('name', format::FORMAT_PLAIN),
            'id_number' => $personal_goal_formatter->format('id_number', format::FORMAT_PLAIN),
            'description' => $personal_goal_formatter->format('description', format::FORMAT_HTML),
            'start_date' => $personal_goal_formatter->format('start_date', date_format::FORMAT_DATELONG),
            'target_type' => $personal_goal_formatter->format('target_type'),
            'target_date' => $personal_goal_formatter->format('target_date', date_format::FORMAT_DATELONG),
            'target_value' => $personal_goal_formatter->format('target_value'),
            'current_value' => $personal_goal_formatter->format('current_value'),
            'current_value_updated_at' => $personal_goal_formatter->format('current_value_updated_at', date_format::FORMAT_DATELONG),
            'status' => $personal_goal_formatter->format('status'),
            'closed_at' => $personal_goal_formatter->format('closed_at', date_format::FORMAT_DATELONG),
            'created_at' => $personal_goal_formatter->format('created_at', date_format::FORMAT_DATELONG),
            'updated_at' => $personal_goal_formatter->format('updated_at', date_format::FORMAT_DATELONG),
            'plugin_name' => $personal_goal_formatter->format('plugin_name'),
        ];
    }

    /**
     * This method converts the 'decode_response' method json output to a reportbuilder display output for human-readable format
     *
     * @param array|null $data generated in the 'decode_response' method
     * @return string|null
     */
    public static function generate_display(?array $data = null): ?string {
        if (empty($data)) {
            return $data;
        }

        $html_string = '';
        foreach ($data as $item) {
            $goal = json_decode($item)->goal;
            $html_string .= html_writer::tag('li', $goal->name);
        }
        return html_writer::tag('ul', $html_string);
    }

    /**
     * @inheritDoc
     */
    public function get_sortorder(): int {
        return 95;
    }

    /**
     * @inheritDoc
     */
    public function get_element_usage(): base_element_usage {
        return new element_usage();
    }

    /**
     * @inheritDoc
     */
    public function get_participant_response_component(): string {
        return 'performelement_perform_goal_creation/components/PerformGoalCreationResponseDisplay';
    }

    /**
     * @inheritDoc
     */
    public function get_permissions(int $subject_user_id): ?string {
        $permissions = $this->get_personal_goal_permissions($subject_user_id);

        return json_encode([
            'can_manage' => $permissions['can_manage'],
        ]);
    }

    /**
     * @param int $subject_user_id
     * @return array
     */
    private function get_personal_goal_permissions(int $subject_user_id): array {
        if ($this->is_external_participant()) {
            return ['can_view' => false, 'can_manage' => false];
        }

        if (!array_key_exists($subject_user_id, self::$can_view) || !array_key_exists($subject_user_id, self::$can_manage)) {
            $goal_interactor = goal_interactor::for_user(new user($subject_user_id));

            self::$can_view[$subject_user_id] = $goal_interactor->can_view_personal_goals();
            self::$can_manage[$subject_user_id] = $goal_interactor->can_create_personal_goal();
        }

        return [
            'can_view' => self::$can_view[$subject_user_id],
            'can_manage' => self::$can_manage[$subject_user_id],
        ];
    }

    /**
     * @return void
     */
    public static function reset_permissions_cache(): void {
        self::$can_manage = [];
        self::$can_view = [];
    }

    /**
     * Indicates if the current user is an external participant ie not logged on
     * and there is a valid participant token in the session.
     *
     * @return bool true if this is an external participant.
     */
    private function is_external_participant(): bool {
        $token = external_participant_token_validator::find_token_in_session();
        if (!$token) {
            return false;
        }

        $validator = new external_participant_token_validator($token);
        return $validator->is_valid();
    }

    /**
     * Set section_element_response_id
     *
     * @param int|null $section_element_response_id
     * @return $this
     */
    public function set_section_element_response_id(?int $section_element_response_id): self {
        $this->section_element_response_id = $section_element_response_id;
        return $this;
    }
}
