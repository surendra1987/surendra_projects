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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package bi_intellidata
 */

namespace bi_intellidata\testing;


use bi_intellidata\helpers\SettingsHelper;
use bi_intellidata\persistent\datatypeconfig;
use bi_intellidata\persistent\tracking;
use bi_intellidata\helpers\PageParamsHelper;
use bi_intellidata\repositories\export_log_repository;
use bi_intellidata\services\encryption_service;
use core\testing\component_generator;
use core_phpunit\testcase;
use moodle_exception;
use stdClass;
use phpunit_util;

class generator extends component_generator {
    /**
     * @var number of created tracking.
     */
    protected $trackingcount = 0;

    /**
     * Data generator.
     *
     * @return \testing_data_generator|null
     */
    private static function data_generator() {
        if (test_helper::is_new_phpunit()) {
            return testcase::getDataGenerator();
        }

        return phpunit_util::get_data_generator();
    }

    /**
     * Generate a user.
     *
     * @param array $data
     * @return \stdClass
     * @throws \moodle_exception
     */
    public static function create_user(array $data = []): \stdClass {
        global $CFG;

        require_once($CFG->dirroot . '/user/lib.php');

        if (test_helper::is_new_phpunit()) {
            return self::data_generator()->create_user($data);
        }

        $data['id'] = user_create_user($data);
        return (object)$data;
    }

    /**
     * Generate users.
     *
     * @param int $num
     * @return \stdClass
     * @throws \moodle_exception
     */
    public static function create_users(int $num = 0): array {

        $users = [];

        for ($i = 1; $i <= $num; $i++) {
            $newuser = self::create_user();
            $users[$newuser->id] = $newuser;
        }

        return $users;
    }

    /**
     * Generate a cohort.
     *
     * @param array $data
     * @return \stdClass
     */
    public static function create_cohort(array $data = []): \stdClass {
        return self::data_generator()->create_cohort($data);
    }

    /**
     * Generate cohorts.
     *
     * @param int $num
     * @return \stdClass
     * @throws \moodle_exception
     */
    public static function create_cohorts(int $num = 0): array {

        $cohorts = [];

        for ($i = 1; $i <= $num; $i++) {
            $newcohort = self::create_cohort();
            $cohorts[$newcohort->id] = $newcohort;
        }

        return $cohorts;
    }

    /**
     * Generate a category.
     *
     * @param array $data
     * @return \core_course_category
     */
    public static function create_category(array $data = []) {
        return self::data_generator()->create_category($data);
    }

    /**
     * Generate categories.
     *
     * @param int $num
     * @return \stdClass
     * @throws \moodle_exception
     */
    public static function create_categories(int $num = 0): array {

        $categories = [];

        for ($i = 1; $i <= $num; $i++) {
            $newcategory = self::create_category();
            $categories[$newcategory->id] = $newcategory;
        }

        return $categories;
    }

    /**
     * Get category.
     *
     * @param int $id
     * @return \core_course_category|false|null
     * @throws \moodle_exception
     */
    public static function get_category(int $id) {
        if (test_helper::is_new_phpunit()) {
            return \core_course_category::get($id);
        }

        return \coursecat::get($id);
    }

    /**
     * Generate a group.
     *
     * @param array $data
     * @return \stdClass
     * @throws \coding_exception
     */
    public static function create_group(array $data) {
        return self::data_generator()->create_group($data);
    }

    /**
     * Generate group member.
     *
     * @param array $data
     */
    public static function create_group_member(array $data) {
        return self::data_generator()->create_group_member($data);
    }

    /**
     * Generate a course.
     *
     * @param array $data
     * @return \stdClass
     */
    public static function create_course(array $data = []) {
        return self::data_generator()->create_course($data);
    }

    /**
     * Generate courses.
     *
     * @param int $num
     * @return \stdClass
     * @throws \moodle_exception
     */
    public static function create_courses(int $num = 0): array {

        $courses = [];

        for ($i = 1; $i <= $num; $i++) {
            $newcourse = self::create_course();
            $courses[$newcourse->id] = $newcourse;
        }

        return $courses;
    }

    /**
     * Enrol user.
     *
     * @param array $data
     * @return bool
     */
    public static function enrol_user(array $data) {
        return self::data_generator()->enrol_user($data['userid'], $data['courseid']);
    }

    /**
     * Enrol users.
     *
     * @param array $data
     * @return bool
     */
    public static function enrol_users(int $courseid, array $users) {

        $enrols = [];

        foreach ($users as $user) {
            $enrols = self::data_generator()->enrol_user($user->id, $courseid);
        }

        return $enrols;
    }

    /**
     * Get generator.
     *
     * @param $component
     * @return \component_generator_base|\default_block_generator
     */
    public static function get_plugin_generator($component) {
        return self::data_generator()->get_plugin_generator($component);
    }

    /**
     * Generate a role.
     *
     * @param array $data
     * @return int
     * @throws \coding_exception
     */
    public static function create_role(array $data) {
        return self::data_generator()->create_role($data);
    }

    /**
     * Generate a module.
     *
     * @param string $modulename
     * @param array $data
     * @return \stdClass
     */
    public static function create_module(string $modulename, array $data) {
        return self::data_generator()->create_module($modulename, $data);
    }

    /**
     * Data plugin generator.
     *
     * @return \testing_data_generator|null
     */
    public static function data_plugin_generator() {
        if (test_helper::is_new_phpunit()) {
            return testcase::getDataGenerator()->get_plugin_generator('bi_intellidata');
        }

        return phpunit_util::get_data_generator()->get_plugin_generator('bi_intellidata');
    }

    /**
     * Create record.
     *
     * @param $record
     * @return tracking
     */
    public function create_create_tracking_record($record = null) {

        $record = (object)$record;

        if (!isset($record->page)) {
            $record->page = PageParamsHelper::PAGETYPE_SITE;
        }
        if (!isset($record->param)) {
            $record->param = PageParamsHelper::PAGEPARAM_SYSTEM;
        }
        if (!isset($record->visits)) {
            $record->visits = 1;
        }
        if (!isset($record->timespend)) {
            $record->timespend = 1;
        }
        if (!isset($record->firstaccess)) {
            $record->firstaccess = time();
        }
        if (!isset($record->lastaccess)) {
            $record->lastaccess = time();
        }
        if (!isset($record->timemodified)) {
            $record->timemodified = time();
        }
        if (!isset($record->useragent)) {
            $record->useragent = '';
        }
        if (!isset($record->ip)) {
            $record->ip = '';
        }

        $tracking = new tracking(0, $record);
        $tracking->save();

        return $tracking;
    }

    /**
     * To be called from data reset code only,
     * do not use in tests.
     * @return void
     */
    public function reset() {
        $this->trackingcount = 0;
    }

    /**
     * Create tracking record.
     *
     * @param $data
     * @return stdClass
     */
    public function create_tracking($data = null): stdClass {
        $record = $this->create_create_tracking_record($data);

        if ($record->get('id')) {
            $this->trackingcount++;
        }

        return $record->to_record();
    }

    /**
     * @param array $data
     * @return stdClass
     */
    public function create_user_with_event_trigger(array $data = []): stdClass {
        global $CFG;

        require_once($CFG->dirroot . '/user/lib.php');

        $data['id'] = user_create_user($data);
        return (object)$data;
    }

    /**
     * @param string|null $encryptionkey
     * @param string|null $clientidentifier
     * @return string[]
     */
    public function create_credentials(string $encryptionkey = null, string $clientidentifier = null): array {
        $encryptionkey = $encryptionkey ?? random_string(32);
        $clientidentifier = $clientidentifier ?? random_string(32);

        SettingsHelper::set_setting('encryptionkey', $encryptionkey);
        SettingsHelper::set_setting('clientidentifier', $clientidentifier);

        return [$encryptionkey, $clientidentifier];
    }

    /**
     * @param int $num_export_logs
     * @return array
     */
    public function create_export_logs(int $num_export_logs = 3): array {
        global $DB;
        $num_export_logs = 3;
        $ib_config_records = $DB->get_records('bi_intellidata_config', ['status' => datatypeconfig::STATUS_ENABLED], '',
            'datatype', 0, $num_export_logs);

        $export_log_repository = new export_log_repository();
        foreach ($ib_config_records as $record) {
            $export_log_repository->insert_datatype($record->datatype);
        }

        return $DB->get_records('bi_intellidata_export_log');
    }
}
