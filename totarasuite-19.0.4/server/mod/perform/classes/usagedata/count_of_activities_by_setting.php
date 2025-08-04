<?php
/**
 * This file is part of Totara Core
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
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package mod_perform
 */

namespace mod_perform\usagedata;

use core\orm\query\builder;
use mod_perform\entity\activity\activity;
use mod_perform\entity\activity\activity_setting as activity_setting_entity;
use mod_perform\models\activity\activity_setting;
use mod_perform\models\activity\settings\controls\sync_participant_instance_closure_option;
use mod_perform\state\activity\draft;
use tool_usagedata\export;

/**
 * Usage data export class counting activities for a list of specific settings.
 */
class count_of_activities_by_setting implements export {

    public const RESULT_KEY_MULTIPLE_CLOSURE_METHODS = 'multiple_closure_methods';

    /**
     * @inheritDoc
     */
    public function get_summary(): string {
        return get_string('count_of_activities_by_setting_summary', 'mod_perform');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * Result looks like this:
     *
     * [
     *    // closure settings
     *    'manual_close' => 11,
     *    'close_on_completion' => 22,
     *    'close_on_due_date' => 33,
     *    'multiple_closure_methods' => 4, // activities that have two or more of the above settings enabled
     *
     *    // sync closure settings (taking global settings and activity-specific overrides into account)
     *    'sync_participant_instance_closure_disabled' => 55,
     *    'sync_participant_instance_closure_close_not_started_only' => 66,
     *    'sync_participant_instance_closure_close_all' => 77,
     * ]
     *
     *
     *
     * @inheritDoc
     */
    public function export(): array {
        $count_closure_setting_enabled = [
            activity_setting::MANUAL_CLOSE => 0,
            activity_setting::CLOSE_ON_COMPLETION => 0,
            activity_setting::CLOSE_ON_DUE_DATE => 0,
        ];
        $count_closure_methods_per_activity = [];
        $override_sync_closure_activity_ids = [];
        $override_sync_closure = [];

        $fetch_enabled_only = array_keys($count_closure_setting_enabled);
        $fetch_enabled_only[] = activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS;

        /*
         * The required logic can't be covered by a single SQL query easily,
         * so let's just fetch all relevant records and do the logic in PHP.
         * We don't expect huge amount of records here, so looping through
         * the results should be fine.
         */
        $activity_settings = activity_setting_entity::repository()
            ->join([activity::TABLE, 'activity'], 'activity_id', 'id')
            ->where('activity.status', '<>', draft::get_code())
            ->where(
                fn (builder $builder) => $builder
                    ->where(
                        fn (builder $builder) => $builder
                            ->where_in('name', $fetch_enabled_only)
                            ->where('value', 1)
                    )
                    ->or_where('name', activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE)
            )
            ->get();

        foreach ($activity_settings as $activity_setting) {
            $activity_id = $activity_setting->activity_id;

            if (array_key_exists($activity_setting->name, $count_closure_setting_enabled)) {
                if (!isset($count_closure_methods_per_activity[$activity_id])) {
                    $count_closure_methods_per_activity[$activity_id] = 0;
                }

                $count_closure_setting_enabled[$activity_setting->name] ++;
                $count_closure_methods_per_activity[$activity_id] ++;
            } elseif ($activity_setting->name === activity_setting::OVERRIDE_GLOBAL_PARTICIPATION_SETTINGS) {
                $override_sync_closure_activity_ids[] = $activity_setting->activity_id;
            } elseif ($activity_setting->name === activity_setting::SYNC_PARTICIPANT_INSTANCE_CLOSURE) {
                $override_sync_closure[(int)$activity_setting->activity_id] = $activity_setting->value;
            }
        }

        // How many activities have more than one closure setting enabled?
        $count_closure_setting_enabled[self::RESULT_KEY_MULTIPLE_CLOSURE_METHODS] = count(array_filter(
            $count_closure_methods_per_activity,
            static fn ($count): bool => $count > 1
        ));

        return array_merge(
            $count_closure_setting_enabled,
            $this->get_sync_closure_counts($override_sync_closure_activity_ids, $override_sync_closure)
        );
    }

    /**
     * From the given activity-specific overrides data (fetched from the activity setting table),
     * calculate the total counts for the individual sync closure setting values.
     * We also have to take the global setting into account because activities without overrides
     * adopt it.
     *
     *
     * @param array $override_sync_closure_activity_ids
     * @param array $override_sync_closure
     * @return int[]
     */
    private function get_sync_closure_counts(array $override_sync_closure_activity_ids, array $override_sync_closure): array {
        $activities_count_overridden = 0;

        $sync_settings_count = [
            sync_participant_instance_closure_option::CLOSURE_DISABLED->value => 0,
            sync_participant_instance_closure_option::CLOSE_NOT_STARTED_ONLY->value => 0,
            sync_participant_instance_closure_option::CLOSE_ALL->value => 0,
        ];

        foreach ($override_sync_closure_activity_ids as $activity_id) {
            if (isset($override_sync_closure[$activity_id])) {
                $activities_count_overridden ++;
                $sync_settings_count[$override_sync_closure[$activity_id]] ++;
            }
        }

        // Activities without overrides also have to be counted (they get the global setting)
        $activities_count_total = activity::repository()->filter_by_not_draft()->count();
        $global_setting = (int)get_config(null, 'perform_sync_participant_instance_closure');
        // Total count of activities minus the number of overrides get the global setting.
        $sync_settings_count[$global_setting] += $activities_count_total - $activities_count_overridden;

        return [
            'sync_participant_instance_closure_disabled' => $sync_settings_count[sync_participant_instance_closure_option::CLOSURE_DISABLED->value],
            'sync_participant_instance_closure_close_not_started_only' => $sync_settings_count[sync_participant_instance_closure_option::CLOSE_NOT_STARTED_ONLY->value],
            'sync_participant_instance_closure_close_all' => $sync_settings_count[sync_participant_instance_closure_option::CLOSE_ALL->value],
        ];
    }
}
