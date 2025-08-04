<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package mod_facetoface
 */

use core_phpunit\testcase;
use mod_facetoface\query\event\filter_factory;

defined('MOODLE_INTERNAL') || die();

class mod_facetoface_query_event_filter_factory_test extends testcase {

    public function test_base_query_sessions_and_dates() {
        $core_generator = $this->getDataGenerator();
        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $course = $core_generator->create_course();
        $event = $facetoface_generator->create_session_for_course($course);

        $query = filter_factory::base_query_sessions_and_dates();

        $result = $query->get();
        $this->assertCount(1, $result->all());
        $this->assertEquals($event->get_id(), $result->pop()->id);

        $event_2 = $facetoface_generator->create_session_for_course($course);

        $result = $query->get();
        $this->assertCount(2, $result->all());
        $id_list = $result->keys();
        $this->assertContains($event->get_id(), $id_list);
        $this->assertContains($event_2->get_id(), $id_list);
    }

    public function test_event_future() {
        $core_generator = $this->getDataGenerator();
        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $course = $core_generator->create_course();

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_future($query, $future_event->get_mintimestart() - 100);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($future_event->get_id(), $result->pop()->id);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_future($query, $past_event->get_mintimestart() - 100);
        $result = $query->get();

        $this->assertEquals(2, $result->count());

        $id_list = $result->keys();
        $this->assertContains($past_event->get_id(), $id_list);
        $this->assertContains($future_event->get_id(), $id_list);

        $future_event->cancel();

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_future($query, $past_event->get_mintimestart() - 100);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($past_event->get_id(), $result->pop()->id);
    }

    public function test_event_past() {
        $core_generator = $this->getDataGenerator();
        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $course = $core_generator->create_course();

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_past($query, $past_event->get_maxtimefinish() + 100);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($past_event->get_id(), $result->pop()->id);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_past($query, $future_event->get_maxtimefinish() + 100);
        $result = $query->get();

        $this->assertEquals(2, $result->count());
        $id_list = $result->keys();
        $this->assertContains($past_event->get_id(), $id_list);
        $this->assertContains($future_event->get_id(), $id_list);

        $future_event->cancel();

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_past($query, $future_event->get_maxtimefinish() + 100);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($past_event->get_id(), $result->pop()->id);
    }

    public function test_event_starts_after() {
        $core_generator = $this->getDataGenerator();
        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $course = $core_generator->create_course();

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_starts_after($query, $future_event->get_mintimestart() - 100);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($future_event->get_id(), $result->pop()->id);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_starts_after($query, $past_event->get_mintimestart() - 100);
        $result = $query->get();

        $this->assertEquals(2, $result->count());

        $id_list = $result->keys();
        $this->assertContains($past_event->get_id(), $id_list);
        $this->assertContains($future_event->get_id(), $id_list);
    }

    public function test_event_finishes_on_or_before() {
        $core_generator = $this->getDataGenerator();
        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $course = $core_generator->create_course();

        $past_event = $facetoface_generator->create_session_for_course($course, -100);
        $future_event = $facetoface_generator->create_session_for_course($course, 200);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_finishes_on_or_before($query, $past_event->get_maxtimefinish() + 100);
        $result = $query->get();

        $this->assertEquals(1, $result->count());
        $this->assertEquals($past_event->get_id(), $result->pop()->id);

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::event_finishes_on_or_before($query, $future_event->get_maxtimefinish() + 100);
        $result = $query->get();

        $this->assertEquals(2, $result->count());
        $id_list = $result->keys();
        $this->assertContains($past_event->get_id(), $id_list);
        $this->assertContains($future_event->get_id(), $id_list);
    }

    public function test_minimum_capacity_left() {
        $this->setAdminUser();

        $core_generator = $this->getDataGenerator();
        $course = $core_generator->create_course();

        /** @var mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        $event_with_5_seats_available = $facetoface_generator->create_session_for_course($course);
        $amount_of_users_to_signup = 5;
        for ($users_signed_up = 0; $users_signed_up < $amount_of_users_to_signup; $users_signed_up++) {
            $user = $core_generator->create_user();
            $core_generator->enrol_user($user->id, $course->id);
            $facetoface_generator->create_signup($user, $event_with_5_seats_available);
        }

        $event_with_1_seat_available = $facetoface_generator->create_session_for_course($course);
        $amount_of_users_to_signup = 9;
        for ($users_signed_up = 0; $users_signed_up < $amount_of_users_to_signup; $users_signed_up++) {
            $user = $core_generator->create_user();
            $core_generator->enrol_user($user->id, $course->id);
            $facetoface_generator->create_signup($user, $event_with_1_seat_available);
        }

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::minimum_capacity_left($query, 10);

        $this->assertEquals(0, $query->get()->count());

        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::minimum_capacity_left($query, 5);

        $result = $query->get();
        $this->assertEquals(1, $result->count());
        $this->assertEquals($event_with_5_seats_available->get_id(), $result->pop()->id);


        $query = filter_factory::base_query_sessions_and_dates();
        $query = filter_factory::minimum_capacity_left($query, 1);

        $result = $query->get();
        $this->assertEquals(2, $result->count());
        foreach($result->keys() as $event_id) {
            $this->assertContains($event_id, [$event_with_5_seats_available->get_id(), $event_with_1_seat_available->get_id()]);
        }
    }
}
