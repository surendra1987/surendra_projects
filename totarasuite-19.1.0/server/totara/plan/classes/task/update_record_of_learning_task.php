<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Fabian Derschatta <fabian.derschatta@totaralearning.com>
 * @package totara_plan
 */

namespace totara_plan\task;

use container_course\course;
use container_site\site;
use core\task\scheduled_task;
use totara_plan\record_of_learning;
use xmldb_table;

/**
 * Update the record of learning table with the latest records
 */
class update_record_of_learning_task extends scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('update_record_of_learning_task', 'totara_plan');
    }

    /**
     * Do the job.
     * Throw exceptions on errors (the job will be retried).
     * At the moment all this does is update plans that are set
     * to auto complete after the end date
     */
    public function execute() {
        global $DB;
        raise_memory_limit(MEMORY_HUGE);

        $table_name = 'dp_record_of_learning_temp';
        $this->create_temp_table($table_name);

        $this->insert_records($table_name);

        $sql = "
                INSERT INTO {dp_record_of_learning} (userid, instanceid, type)
                SELECT tmp.userid, tmp.instanceid, tmp.type
                FROM {{$table_name}} tmp
                LEFT JOIN {dp_record_of_learning} rol
                    ON tmp.userid = rol.userid
                        AND tmp.instanceid = rol.instanceid
                        AND tmp.type = rol.type
                WHERE rol.id IS NULL
                GROUP BY tmp.userid, tmp.instanceid, tmp.type
        ";

        $DB->execute($sql);

        // Delete the records which are not in the temporary table
        $sql = "
            DELETE FROM {dp_record_of_learning} 
            WHERE NOT EXISTS (
                SELECT tmp.id 
                FROM {{$table_name}} tmp
                WHERE tmp.userid = {dp_record_of_learning}.userid 
                    AND tmp.instanceid = {dp_record_of_learning}.instanceid 
                    AND tmp.type = {dp_record_of_learning}.type 
            )
        ";

        $DB->execute($sql);
        $this->drop_temp_table($table_name);
    }

    private function insert_records(string $table_name): void {
        global $DB;

        $course_type = course::get_type();
        $site_type = site::get_type();
        $params = [
            'container_course' => $course_type,
            'container_site' => $site_type,
        ];

        $DB->transaction(function () use ($DB, $table_name, $params) {
            $user_enrol_sql = "
                INSERT INTO {{$table_name}} (userid, instanceid) 
                SELECT ue.userid, e.courseid as instanceid
                FROM {user_enrolments} ue
                JOIN {enrol} e ON ue.enrolid = e.id
                JOIN {course} c ON e.courseid = c.id 
                WHERE (c.containertype = :container_course OR c.containertype = :container_site)
                  AND ue.status != :status_pending
            ";

            $DB->execute($user_enrol_sql, $params + ['status_pending' => ENROL_USER_PENDING_APPLICATION]);

            $course_complete_sql = "
                INSERT INTO {{$table_name}} (userid, instanceid)
                SELECT cc.userid, cc.course as instanceid
                FROM {course_completions} cc
                JOIN {course} c ON cc.course = c.id
                WHERE cc.status > 10
            ";

            $DB->execute($course_complete_sql);

            $plan_course_sql = "
                INSERT INTO {{$table_name}} (userid, instanceid)
                SELECT p1.userid, pca1.courseid as instanceid
                FROM {dp_plan_course_assign} pca1
                JOIN {dp_plan} p1 ON pca1.planid = p1.id
                JOIN {course} c ON pca1.courseid = c.id
            ";

            $DB->execute($plan_course_sql);
        });
    }

    /**
     * @param string $table_name
     * @return void
     */
    private function create_temp_table(string $table_name): void {
        global $DB;
        $dbman = $DB->get_manager();

        // Define table dp_record_of_learning to be created.
        $table = new xmldb_table($table_name);

        // Adding fields to table dp_record_of_learning.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('instanceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_INTEGER, '1', null, null, null, record_of_learning::TYPE_COURSE);

        // Adding keys to table dp_record_of_learning.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Adding indexes to table dp_record_of_learning.
        $table->add_index('role_non_unique', XMLDB_INDEX_NOTUNIQUE, array('userid', 'instanceid'));

        // Conditionally launch create table for dp_record_of_learning.
        if (!$dbman->table_exists($table)) {
            $dbman->create_temp_table($table);
        } else {
            $DB->execute("DELETE FROM {{$table_name}}");
        }
    }

    /**
     * @param string $table_name
     * @return void
     */
    private function drop_temp_table(string $table_name): void {
        global $DB;

        $dbman = $DB->get_manager();

        $table = new xmldb_table($table_name);

        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
    }
}

