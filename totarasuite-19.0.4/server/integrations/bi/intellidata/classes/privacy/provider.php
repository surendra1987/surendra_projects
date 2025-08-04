<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy Subsystem implementation for bi_intellidata
 *
 * @package    bi_intellidata
 * @copyright  2021 IntelliBoard, Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @website    http://intelliboard.net/
 */

namespace bi_intellidata\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;

defined('MOODLE_INTERNAL') || die();

if (interface_exists('\core_privacy\local\request\core_userlist_provider')) {
    interface id_userlist_provider extends \core_privacy\local\request\core_userlist_provider {
    }
} else {
    interface id_userlist_provider {
    }
}

/**
 * Implementation of the privacy subsystem plugin provider for the intelliboard activity module.
 *
 * @copyright  2018 Andrew Nicols <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\subsystem\provider,
        \core_privacy\local\request\subsystem\plugin_provider,
        id_userlist_provider {

    /**
     * Returns meta data about this system.
     *
     * @param   collection     $items The initialised collection to add items to.
     * @return  collection     A listing of user data stored through this system.
     */
    public static function get_metadata(collection $items) : collection {
        // The 'bi_intellidata_tracking' table stores the metadata about what [managers] can see in the reports.
        $items->add_database_table('bi_intellidata_tracking', [
            'userid' => 'privacy:metadata:bi_intellidata_tracking:userid',
            'rel' => 'privacy:metadata:bi_intellidata_tracking:rel',
            'type' => 'privacy:metadata:bi_intellidata_tracking:type',
            'instance' => 'privacy:metadata:bi_intellidata_tracking:instance',
            'timecreated' => 'privacy:metadata:bi_intellidata_tracking:timecreated'
        ], 'privacy:metadata:bi_intellidata_tracking');

        // The 'bi_intellidata_details' table stores the metadata about timespent per-hour.
        $items->add_database_table('bi_intellidata_trdetails', [
            'logid' => 'privacy:metadata:bi_intellidata_details:logid',
            'visits' => 'privacy:metadata:bi_intellidata_details:visits',
            'timespend' => 'privacy:metadata:bi_intellidata_details:timespend',
            'timepoint' => 'privacy:metadata:bi_intellidata_details:timepoint'
        ], 'privacy:metadata:bi_intellidata_details');

        // The 'bi_intellidata_logs' table stores information about timespent per-day.
        $items->add_database_table('bi_intellidata_trlogs', [
            'trackid' => 'privacy:metadata:bi_intellidata_logs:trackid',
            'visits' => 'privacy:metadata:bi_intellidata_logs:visits',
            'timespend' => 'privacy:metadata:bi_intellidata_logs:timespend',
            'timepoint' => 'privacy:metadata:bi_intellidata_logs:timepoint'
        ], 'privacy:metadata:bi_intellidata_logs');

        // The 'bi_intellidata_config' table stores information about plugin configuration.
        $items->add_database_table('bi_intellidata_config', [
            'tabletype' => 'privacy:metadata:bi_intellidata_config:tabletype',
            'datatype' => 'privacy:metadata:bi_intellidata_config:datatype',
            'status' => 'privacy:metadata:bi_intellidata_config:status',
            'timemodified_field' => 'privacy:metadata:bi_intellidata_config:timemodified_field',
            'rewritable' => 'privacy:metadata:bi_intellidata_config:rewritable',
            'filterbyid' => 'privacy:metadata:bi_intellidata_config:filterbyid',
            'events_tracking' => 'privacy:metadata:bi_intellidata_config:events_tracking',
            'usermodified' => 'privacy:metadata:bi_intellidata_config:usermodified',
            'timecreated' => 'privacy:metadata:bi_intellidata_config:timecreated',
            'timemodified' => 'privacy:metadata:bi_intellidata_config:timemodified'
        ], 'privacy:metadata:bi_intellidata_config');

        return $items;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * In the case of intelliboard, that is any intelliboard where the user has made any post,
     * rated any content, or has any preferences.
     *
     * @param   int         $userid     The user to search.
     * @return  contextlist   $contextlist  The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : \core_privacy\local\request\contextlist {
        return new contextlist();
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        $records = $DB->get_records_sql("SELECT (CASE
                WHEN d.id > 0 THEN d.id*l.id*t.id
                WHEN l.id > 0 THEN l.id*t.id*t.id
                    ELSE t.id
            END) AS unid, t.*,
            l.timepoint AS day_time,
            l.visits AS day_visits,
            l.timespend AS day_timespent,
            l.timepoint AS hour_time,
            d.visits AS hour_visits,
            d.timespend AS hour_timespent
            FROM {bi_intellidata_tracking} t
            LEFT JOIN {bi_intellidata_trlogs} l ON l.trackid = t.id
            LEFT JOIN {bi_intellidata_trdetails} d ON d.logid = l.id
            WHERE t.userid = :userid", ['userid' => $user->id]);

        if (!empty($records)) {
            \core_privacy\local\request\writer::with_context($context)
                    ->export_data([], (object) [
                        'records' => $records,
                    ]);
        }
    }


    /**
     * Delete all data for all users in the specified context.
     *
     * @param   context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_COURSE) {
            $params = [
            'courseid' => $context->instanceid
            ];
            $items = $DB->get_records("bi_intellidata_tracking", $params);

            foreach ($items as $item) {
                $logs = $DB->get_records("bi_intellidata_trlogs", ['trackid' => $item->id]);

                foreach ($logs as $log) {
                    $DB->delete_records('bi_intellidata_trdetails', [
                      'logid' => $log->id,
                    ]);
                }
                $DB->delete_records('bi_intellidata_trlogs', [
                  'trackid' => $item->id,
                ]);
            }
            $DB->delete_records('bi_intellidata_tracking', $params);
        } else if ($context->contextlevel == CONTEXT_MODULE) {
            $params = [
                'page' => 'module',
                'param' => $context->instanceid
            ];

            $items = $DB->get_records("bi_intellidata_tracking", $params);

            foreach ($items as $item) {
                $logs = $DB->get_records("bi_intellidata_trlogs", ['trackid' => $item->id]);

                foreach ($logs as $log) {
                    $DB->delete_records('bi_intellidata_trdetails', [
                      'logid' => $log->id,
                    ]);
                }
                $DB->delete_records('bi_intellidata_trlogs', [
                  'trackid' => $item->id,
                ]);
            }
            $DB->delete_records('bi_intellidata_tracking', $params);
        }
        return;
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param   approved_contextlist    $contextlist    The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();
        $userid = $user->id;

        $items = $DB->get_records("bi_intellidata_tracking", ['userid' => $userid]);

        foreach ($items as $item) {
            $logs = $DB->get_records("bi_intellidata_logs", ['trackid' => $item->id]);

            foreach ($logs as $log) {
                $DB->delete_records('bi_intellidata_trdetails', [
                    'logid' => $log->id,
                ]);
            }
            $DB->delete_records('bi_intellidata_trlogs', [
                'trackid' => $item->id,
            ]);
        }
        $DB->delete_records('bi_intellidata_tracking', [
            'userid' => $userid,
        ]);
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if ($context->contextlevel == CONTEXT_COURSE) {
            $params = [
                'courseid' => $context->instanceid
            ];
            $sql = "SELECT userid FROM {bi_intellidata_tracking} WHERE courseid = :courseid";
            $userlist->add_from_sql('userid', $sql, $params);
        } else if ($context->contextlevel == CONTEXT_MODULE) {
            $params = [
                'cmid' => $context->instanceid
            ];
            $sql = "SELECT userid FROM {bi_intellidata_tracking} WHERE page = 'module' AND param = :cmid";
            $userlist->add_from_sql('userid', $sql, $params);
        }
    }
    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $users = $userlist->get_userids();

        foreach ($users as $userid) {
            $items = $DB->get_records("bi_intellidata_tracking", ['userid' => $userid]);

            foreach ($items as $item) {
                $logs = $DB->get_records("bi_intellidata_trlogs", ['trackid' => $item->id]);

                foreach ($logs as $log) {
                    $DB->delete_records('bi_intellidata_trdetails', [
                      'logid' => $log->id,
                    ]);
                }
                $DB->delete_records('bi_intellidata_trlogs', [
                  'trackid' => $item->id,
                ]);
            }
            $DB->delete_records('bi_intellidata_tracking', [
              'userid' => $userid,
            ]);
        }
    }
}