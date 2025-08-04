<?php
/*
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package mod_hvp
 */

namespace mod_hvp\userdata;

use coding_exception;
use context;
use dml_exception;
use stdClass;
use totara_userdata\userdata\export;
use totara_userdata\userdata\item;
use totara_userdata\userdata\target_user;

/**
 * Class responses
 *
 * This item applies to users' activities and responses to hvps.
 *
 * @package mod_hvp
 */
class responses extends item {

    /**
     * This item allows counting of data.
     *
     * @return bool
     */
    public static function is_countable(): bool {
        return true;
    }

    /**
     * This item allows exporting of data.
     *
     * @return bool
     */
    public static function is_exportable(): bool {
        return true;
    }

    /**
     * This item allows purging of data.
     *
     * @param int $userstatus
     * @return bool
     */
    public static function is_purgeable(int $userstatus): bool {
        return true;
    }

    /**
     * Returns the context levels this item can be executed in.
     *
     * @return int[]
     */
    public static function get_compatible_context_levels(): array {
        return [CONTEXT_MODULE, CONTEXT_COURSE, CONTEXT_COURSECAT, CONTEXT_SYSTEM];
    }

    /**
     * Count user data for this item.
     *
     * @param target_user $user
     * @param context $context restriction for counting e.g. system context for everything and course context for course data
     * @return int amount of data
     * @throws dml_exception
     */
    protected static function count(target_user $user, context $context): int {
        global $DB;

        $content_user_data_sql = 'SELECT DISTINCT(records.hvp_id)
                         FROM {hvp_content_user_data} records '
            . self::get_activities_join($context, 'hvp', 'records.hvp_id')
            . ' WHERE records.user_id = :user_id';
        $content_user_data_hvp_ids = $DB->get_fieldset_sql($content_user_data_sql, ['user_id' => $user->id]);

        $events_sql = 'SELECT DISTINCT(records.content_id)
                         FROM {hvp_events} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $events_hvp_ids = $DB->get_fieldset_sql($events_sql, ['user_id' => $user->id]);

        $xapi_results_sql = 'SELECT DISTINCT(records.content_id)
                         FROM {hvp_xapi_results} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $xapi_results_hvp_ids = $DB->get_fieldset_sql($xapi_results_sql, ['user_id' => $user->id]);

        return count(array_unique(array_merge($content_user_data_hvp_ids, $events_hvp_ids, $xapi_results_hvp_ids)));
    }

    /**
     * Export user data from this item.
     *
     * @param target_user $user
     * @param context $context restriction for exporting e.g. system context for everything and course context for course export
     * @return export result object
     * @throws dml_exception
     */
    protected static function export(target_user $user, context $context): export {
        global $DB;

        // Calculate the list of hvps.
        $content_user_data_sql = 'SELECT DISTINCT(records.hvp_id)
                         FROM {hvp_content_user_data} records '
            . self::get_activities_join($context, 'hvp', 'records.hvp_id')
            . ' WHERE records.user_id = :user_id';
        $content_user_data_hvp_ids = $DB->get_fieldset_sql($content_user_data_sql, ['user_id' => $user->id]);

        $events_sql = 'SELECT DISTINCT(records.content_id)
                         FROM {hvp_events} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $events_hvp_ids = $DB->get_fieldset_sql($events_sql, ['user_id' => $user->id]);

        $xapi_results_sql = 'SELECT DISTINCT(records.content_id)
                         FROM {hvp_xapi_results} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $xapi_results_hvp_ids = $DB->get_fieldset_sql($xapi_results_sql, ['user_id' => $user->id]);

        $hvp_ids = array_unique(array_merge($content_user_data_hvp_ids, $events_hvp_ids, $xapi_results_hvp_ids));

        // Get all the data to export.
        $content_user_data_sql = 'SELECT records.*
                         FROM {hvp_content_user_data} records '
            . self::get_activities_join($context, 'hvp', 'records.hvp_id')
            . ' WHERE records.user_id = :user_id';
        $content_user_datas = $DB->get_records_sql($content_user_data_sql, ['user_id' => $user->id]);

        $events_sql = 'SELECT records.*
                         FROM {hvp_events} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $events = $DB->get_records_sql($events_sql, ['user_id' => $user->id]);

        $xapi_results_sql = 'SELECT records.*
                         FROM {hvp_xapi_results} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $xapi_results = $DB->get_records_sql($xapi_results_sql, ['user_id' => $user->id]);

        // Map the data onto the hvps.
        $export = new export();
        foreach ($hvp_ids as $hvp_id) {
            $export->data[$hvp_id] = [
                'content_user_data' => array_filter($content_user_datas, function (stdClass $content_user_data) use ($hvp_id) {
                    return $content_user_data->hvp_id == $hvp_id;
                }),
                'events' => array_filter($events, function (stdClass $event) use ($hvp_id) {
                    return $event->content_id == $hvp_id;
                }),
                'xapi_results' => array_filter($xapi_results, function (stdClass $xapi_result) use ($hvp_id) {
                    return $xapi_result->content_id == $hvp_id;
                }),
            ];
        }

        return $export;
    }

    /**
     * Purge user data for this item.
     *
     * NOTE: Remember that context record does not exist for deleted users any more,
     *       it is also possible that we do not know the original user context id.
     *
     * @param target_user $user
     * @param context $context restriction for purging e.g. system context for everything, course context for purging one course
     * @return int result self::RESULT_STATUS_SUCCESS, self::RESULT_STATUS_ERROR or self::RESULT_STATUS_SKIPPED
     * @throws coding_exception
     * @throws dml_exception
     */
    protected static function purge(target_user $user, context $context): int {
        global $DB;

        $content_user_data_sql = 'SELECT records.id FROM {hvp_content_user_data} records '
            . self::get_activities_join($context, 'hvp', 'records.hvp_id')
            . ' WHERE records.user_id = :user_id';
        $content_user_data_ids = $DB->get_fieldset_sql($content_user_data_sql, ['user_id' => $user->id]);

        $events_sql = 'SELECT records.id FROM {hvp_events} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $events_ids = $DB->get_fieldset_sql($events_sql, ['user_id' => $user->id]);

        $xapi_results_sql = 'SELECT records.id FROM {hvp_xapi_results} records '
            . self::get_activities_join($context, 'hvp', 'records.content_id')
            . ' WHERE records.user_id = :user_id';
        $xapi_results_ids = $DB->get_fieldset_sql($xapi_results_sql, ['user_id' => $user->id]);

        if (!empty($content_user_data_ids)) {
            [$sql, $params] = $DB->get_in_or_equal($content_user_data_ids);
            $DB->delete_records_select('hvp_content_user_data', 'id ' . $sql, $params);
        }

        if (!empty($events_ids)) {
            [$sql, $params] = $DB->get_in_or_equal($events_ids);
            $DB->delete_records_select('hvp_events', 'id ' . $sql, $params);
        }

        if (!empty($xapi_results_ids)) {
            [$sql, $params] = $DB->get_in_or_equal($xapi_results_ids);
            $DB->delete_records_select('hvp_xapi_results', 'id ' . $sql, $params);
        }

        return self::RESULT_STATUS_SUCCESS;
    }
}
