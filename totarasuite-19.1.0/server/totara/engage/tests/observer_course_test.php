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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package engage_course
 */

use core\orm\query\builder;
use totara_engage\entity\engage_resource;
use engage_course\totara_engage\resource\course;
use core\entity\course as course_entity;
use core_phpunit\testcase;

/**
 * @coversDefaultClass \engage_course\observer\course_observer
 * @group engage_course
 */
class totara_engage_observer_course_test extends testcase {

    /**
     * @return void
     */
    public function test_course_deleted_observer(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $user2 = $generator->create_user();
        $course = $this->getDataGenerator()->create_course();

        $data = new stdClass();
        $data->instanceid = $course->id;
        $data->name = 'Test Course';
        $data->resourcetype = course::get_resource_type();
        $data->userid = $user->id;
        $data->access = 0;
        $data->timecreated = 0;
        $data->timemodified = 0;

        $DB->insert_record(engage_resource::TABLE, $data);

        // Add second record with different user id
        $data->userid = $user2->id;
        $DB->insert_record(engage_resource::TABLE, $data);

        $context = context_system::instance();
        $event = \core\event\course_deleted::create(
            array(
                'objectid' => $course->id,
                'contextid' => $context->id,
                'other' => array('fullname' => $course->fullname)
            )
        );

        // Check record or records exist before triggering event
        $query = builder::table(engage_resource::TABLE);
        $query->where('resourcetype', course::get_resource_type());
        $query->where('instanceid', $course->id);

        $this->assertEquals(2, $query->count());

        $event->trigger();

        $query = builder::table(engage_resource::TABLE);
        $query->where('resourcetype', course::get_resource_type());
        $query->where('instanceid', $course->id);

        $this->assertEquals(0, $query->count());
    }

    /**
     * @return void
     */
    public function test_user_deleted_observer(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $data = new stdClass();
        $data->instanceid = $course->id;
        $data->name = 'Test Course';
        $data->resourcetype = course::get_resource_type();
        $data->userid = $user->id;
        $data->access = 0;
        $data->timecreated = 0;
        $data->timemodified = 0;

        $DB->insert_record(engage_resource::TABLE, $data);

        // Add second record with different course id
        $data->instanceid = $course2->id;
        $DB->insert_record(engage_resource::TABLE, $data);

        $context = context_system::instance();
        $event = \core\event\user_deleted::create(
            array(
                'relateduserid' => $user->id,
                'objectid' => $user->id,
                'contextid' => $context->id,
                'other' => array(
                    'username' => $user->username,
                    'email' => $user->email,
                    'idnumber' => $user->idnumber,
                    'picture' => $user->picture,
                )
            )
        );

        // Check record or records exist before triggering event
        $query = builder::table(engage_resource::TABLE);
        $query->where('resourcetype', course::get_resource_type());
        $query->where('userid', $user->id);

        $this->assertEquals(2, $query->count());

        $event->trigger();

        $query = builder::table(engage_resource::TABLE);
        $query->where('resourcetype', course::get_resource_type());
        $query->where('userid', $user->id);

        $this->assertEquals(0, $query->count());
    }

    /**
     * @return void
     */
    public function test_course_updated_observer(): void {
        global $DB;

        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $this->getDataGenerator()->create_course();
        $course2 = $this->getDataGenerator()->create_course();

        $data = new stdClass();
        $data->instanceid = $course->id;
        $data->name = 'Test Course';
        $data->resourcetype = course::get_resource_type();
        $data->userid = $user->id;
        $data->access = 0;
        $data->timecreated = 0;
        $data->timemodified = 0;

        $DB->insert_record(engage_resource::TABLE, $data);
        $first_resource_id = $DB->get_field(engage_resource::TABLE, 'id', ['instanceid' => $course->id]);

        // Add second record with different course id
        $data->instanceid = $course2->id;
        $DB->insert_record(engage_resource::TABLE, $data);

        $data = new stdClass();
        $data->id = $course->id;
        $data->fullname = 'New Test Course';
        $DB->update_record(course_entity::TABLE, $data);
        $updated_course = $DB->get_record(course_entity::TABLE, ['id' => $course->id]);

        $event = \core\event\course_updated::create(array(
            'objectid' => $updated_course->id,
            'context' => context_course::instance($course->id)
        ));

        $event->trigger();

        $resource = builder::table(engage_resource::TABLE)
            ->where('resourcetype', course::get_resource_type())
            ->where('instanceid', $course->id)
            ->find($first_resource_id);

        $this->assertEquals($resource->name, $updated_course->fullname);
    }
}