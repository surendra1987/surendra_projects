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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

defined('MOODLE_INTERNAL') || die();

use core\date_format;
use core\format;
use core\webapi\execution_context as execution_context;
use core_phpunit\testcase;
use mod_facetoface\seminar_event;
use mod_facetoface\seminar_session;
use mod_facetoface\signup;
use mod_facetoface\signup\state\booked;
use mod_facetoface\signup_status;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_type_event_test extends testcase {

    use webapi_phpunit_helper;

    private function create_event(array $data, bool $manual_properties = true, bool $manual_seminar = false): seminar_event {
        /** @var \mod_facetoface\testing\generator $generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $event = new seminar_event();
        $event->save();
        $course = $this->getDataGenerator()->create_course();

        if (!$manual_properties) {
            return $facetoface_generator->create_session_for_course($course);
        }

        $seminar = $facetoface_generator->create_instance([
            'course' => $course->id,
            'intro' => '',
            'description' => '',
            'capacity' => 100,
            'name' => "blah",
            'shortname' => "bah",
            'sessionattendance' => 2,
            'attendancetime' => 2,
            'eventgradingmanual' => 1,
            'completionpass' => 1,
        ]);
        $seminar_id = $seminar->id;

        if ($manual_seminar) {
            $times = [
                (object) [
                    'sessiontimezone' => '99',
                    'timestart' => 3,
                    'timefinish' => 3600,
                ],
                (object) [
                    'sessiontimezone' => '99',
                    'timestart' => 999,
                    'timefinish' => 126331876,
                ],
            ];

            $event_id = $facetoface_generator->add_session([
                'facetoface' => $seminar_id,
                'sessiondates' => $times,
            ]);

            return new seminar_event($event_id);
        } else {
            $event = $event->to_record();
            foreach ($data as $column => $value) {
                $event->$column = $value;
            }

            $event->facetoface = $seminar_id;
            return (new seminar_event())->from_record($event);
        }
    }

    private function create_attendee(seminar_event $seminarevent, int $userid) {
        $signup = signup::create($userid, $seminarevent, 0);
        $signup->save();
        $booking = new booked($signup);
        $signup_status = signup_status::create($signup, $booking);
        $signup_status->save();
    }

    private function resolve($field, seminar_event $event, array $args = [], execution_context $ec = null) {
        $this->setAdminUser();
        if (!$ec) {
            $ec = execution_context::create('dev');
            $ec->set_relevant_context($event->get_seminar()->get_context());
        }
        return $this->resolve_graphql_type('mod_facetoface_event', $field, $event, $args, null, $ec);
    }

    public function test_resolve_with_incorrect_source() {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Only seminar_event instances are accepted. Instead, got: ');

        $this->resolve_graphql_type(
            'mod_facetoface_event',
            'myfield',
            (object) ['id' => 1]
        );
    }

    public function test_resolve_id(): void {
        $this->assertSame(7, $this->resolve('id', $this->create_event(['id' => 7])));
        $this->assertSame('7', $this->resolve('id', $this->create_event(['id' => '7'])));
        $this->assertSame(0, $this->resolve('id', $this->create_event(['id' => 0])));
        $this->assertSame('0', $this->resolve('id', $this->create_event(['id' => '0'])));
        $this->assertSame(-10, $this->resolve('id', $this->create_event(['id' => -10])));
        $this->assertSame('-10', $this->resolve('id', $this->create_event(['id' => '-10'])));
    }

    public function test_resolve_timecreated(): void {
        $this->assertSame('126331876', $this->resolve('timecreated', $this->create_event(['id' => 1, 'timecreated' => '126331876']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('7', $this->resolve('timecreated', $this->create_event(['id' => 1, 'timecreated' => '7']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970', $this->resolve('timecreated', $this->create_event(['id' => 1, 'timecreated' => '3']), ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_timemodified(): void {
        $this->assertSame('126331876',
            $this->resolve('timemodified', $this->create_event(['id' => 1, 'timemodified' => '126331876']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('7', $this->resolve('timemodified', $this->create_event(['id' => 1, 'timemodified' => '7']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970', $this->resolve('timemodified', $this->create_event(['id' => 1, 'timemodified' => '3']), ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_seminar_id(): void {
        $our_event = $this->create_event([]);
        $this->assertEquals($our_event->get_facetoface(), $this->resolve('seminar_id', $our_event));
    }

    public function test_resolve_allow_cancellations(): void {
        $this->assertSame('NEVER', $this->resolve('allow_cancellations', $this->create_event(['id' => 1, 'allowcancellations' => 0])));
        $this->assertSame('ANY_TIME', $this->resolve('allow_cancellations', $this->create_event(['id' => 1, 'allowcancellations' => 1])));
        $this->assertSame('CUT_OFF', $this->resolve('allow_cancellations', $this->create_event(['id' => 1, 'allowcancellations' => 2])));

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Invalid allow cancellations option");
        $this->resolve('allow_cancellations', $this->create_event(['id' => 1, 'allowcancellations' => 3]));
    }

    public function test_resolve_cancellation_cutoff(): void {
        $this->assertSame(7, $this->resolve('cancellation_cutoff', $this->create_event(['id' => 1, 'cancellationcutoff' => 7])));
        $this->assertSame('7', $this->resolve('cancellation_cutoff', $this->create_event(['id' => 1, 'cancellationcutoff' => '7'])));
        $this->assertSame(0, $this->resolve('cancellation_cutoff', $this->create_event(['id' => 1, 'cancellationcutoff' => 0])));
        $this->assertSame('0', $this->resolve('cancellation_cutoff', $this->create_event(['id' => 1, 'cancellationcutoff' => '0'])));
        $this->assertSame(-10, $this->resolve('cancellation_cutoff', $this->create_event(['id' => 1, 'cancellationcutoff' => -10])));
        $this->assertSame('-10', $this->resolve('cancellation_cutoff', $this->create_event(['id' => 1, 'cancellationcutoff' => '-10'])));
    }

    public function test_resolve_details(): void {
        global $CFG;
        $this->assertSame("you are my fire", $this->resolve('details', $this->create_event(['id' => 1, 'details' => "you are my fire"])));
        $this->assertSame("my one desire", $this->resolve('details', $this->create_event(['id' => 1, 'details' => "my one desire"])));
        $this->assertSame("believe when I say", $this->resolve('details', $this->create_event(['id' => 1, 'details' => "believe when I say"]), ['format' => format::FORMAT_HTML]));
        $this->assertSame("I want it that way", $this->resolve('details', $this->create_event(['id' => 1, 'details' => "I want it that way"]), ['format' => format::FORMAT_HTML]));

        // Ensure files are handled correctly
        $pluginfile_event = $this->create_event(['id' => 1, 'details' => '<img src="@@PLUGINFILE@@/image.jpg" alt="image" />']);
        $context_id = $pluginfile_event->get_seminar()->get_context()->id;
        $this->assertSame(
            "<img src=\"$CFG->wwwroot/pluginfile.php/$context_id/mod_facetoface/session/{$pluginfile_event->get_id()}/image.jpg\" alt=\"image\" />",
            $this->resolve('details', $pluginfile_event, ['format' => format::FORMAT_HTML])
        );
    }

    public function test_resolve_cost_normal(): void {
        $this->assertSame('$100', $this->resolve('cost_normal', $this->create_event(['id' => 1, 'normalcost' => '$100'])));
        $this->assertSame('15 potatoes', $this->resolve('cost_normal', $this->create_event(['id' => 1, 'normalcost' => '15 potatoes'])));
        $this->assertSame('your must sacrifice your soul as payment',
            $this->resolve('cost_normal', $this->create_event(['id' => 1, 'normalcost' => 'your must sacrifice your soul as payment'])));
    }

    public function test_resolve_cost_discount(): void {
        $this->assertSame('$100', $this->resolve('cost_discount', $this->create_event(['id' => 1, 'discountcost' => '$100'])));
        $this->assertSame('15 potatoes', $this->resolve('cost_discount', $this->create_event(['id' => 1, 'discountcost' => '15 potatoes'])));
        $this->assertSame('your must sacrifice your soul as payment',
            $this->resolve('cost_discount', $this->create_event(['id' => 1, 'discountcost' => 'your must sacrifice your soul as payment'])));
    }

    public function test_resolve_registration_time_start(): void {
        $this->assertSame('126331876',
            $this->resolve('registration_time_start', $this->create_event(['id' => 1, 'registrationtimestart' => '126331876']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('7',
            $this->resolve('registration_time_start', $this->create_event(['id' => 1, 'registrationtimestart' => '7']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970',
            $this->resolve('registration_time_start', $this->create_event(['id' => 1, 'registrationtimestart' => '3']), ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_registration_time_end(): void {
        $this->assertSame('126331876',
            $this->resolve('registration_time_end', $this->create_event(['id' => 1, 'registrationtimefinish' => '126331876']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('7',
            $this->resolve('registration_time_end', $this->create_event(['id' => 1, 'registrationtimefinish' => '7']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970',
            $this->resolve('registration_time_end', $this->create_event(['id' => 1, 'registrationtimefinish' => '3']), ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_seats_total(): void {
        $this->assertSame(7, $this->resolve('seats_total', $this->create_event(['id' => 1, 'capacity' => 7])));
        $this->assertSame('7', $this->resolve('seats_total', $this->create_event(['id' => 1, 'capacity' => '7'])));
        $this->assertSame(0, $this->resolve('seats_total', $this->create_event(['id' => 1, 'capacity' => 0])));
        $this->assertSame('0', $this->resolve('seats_total', $this->create_event(['id' => 1, 'capacity' => '0'])));
        $this->assertSame(-10, $this->resolve('seats_total', $this->create_event(['id' => 1, 'capacity' => -10])));
        $this->assertSame('-10', $this->resolve('seats_total', $this->create_event(['id' => 1, 'capacity' => '-10'])));
    }

    public function test_resolve_seats_booked(): void {
        $seminar_event = $this->create_event([], false);

        $this->assertSame(0, $this->resolve('seats_booked', $seminar_event));
        $this->create_attendee($seminar_event, 1);
        $this->assertSame(1, $this->resolve('seats_booked', $seminar_event));
        $this->create_attendee($seminar_event, 2);
        $this->assertSame(2, $this->resolve('seats_booked', $seminar_event));
    }

    public function test_resolve_seats_available(): void {
        $seminar_event = $this->create_event([], false);

        $this->assertSame(10, $this->resolve('seats_available', $seminar_event));
        $this->create_attendee($seminar_event, 1);
        $this->assertSame(9, $this->resolve('seats_available', $seminar_event));
        $this->create_attendee($seminar_event, 2);
        $this->assertSame(8, $this->resolve('seats_available', $seminar_event));
    }

    public function test_resolve_sessions(): void {
        $seminar_event = $this->create_event([], true, true);

        /** @var seminar_session[] $result */
        $result = $this->resolve('sessions', $seminar_event);
        $this->assertCount(2, $result);
        $first_session = array_shift($result);
        $second_session = array_pop($result);

        $this->assertEquals(3, $first_session->get_timestart());
        $this->assertEquals(3600, $first_session->get_timefinish());
        $this->assertEquals(999, $second_session->get_timestart());
        $this->assertEquals(126331876, $second_session->get_timefinish());
    }

    public function test_resolve_custom_fields(): void {
        $seminar_event = $this->create_event([], false);

        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_session', ['text_one', 'text_two']);
        $custom_field_generator->set_text($seminar_event->to_record(), $custom_fields['text_one'], 'text', 'facetofacesession', 'facetoface_session');

        // add the custom fields to the execution context for the course type resolver
        $ec = execution_context::create('dev');
        $activity_context = $seminar_event->get_seminar()->get_context();
        $ec->set_relevant_context($activity_context);

        $ec->set_variable('custom_fields_facetofacesession', [$seminar_event->to_record()->id => $custom_fields]);

        $result = $this->resolve('custom_fields', $seminar_event, [], $ec);
        $this->assertCount(2, $result);
    }

    public function test_resolve_custom_fields_not_set(): void {
        $seminar_event = $this->create_event([], true, true);
        $this->assertNull($this->resolve('custom_fields', $seminar_event, ['format' => date_format::FORMAT_TIMESTAMP]));
    }

    public function test_resolve_start_date(): void {
        $seminar_event = $this->create_event([], true, true);
        $this->assertEquals(3, $this->resolve('start_date', $seminar_event, ['format' => date_format::FORMAT_TIMESTAMP]));
    }

    public function test_resolve_finish_date(): void {
        $seminar_event = $this->create_event([], true, true);
        $this->assertEquals(126331876, $this->resolve('finish_date', $seminar_event, ['format' => date_format::FORMAT_TIMESTAMP]));
    }

    public function test_resolve_seminar(): void {
        /** @var \mod_facetoface\testing\generator $facetoface_generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');
        $course = $this->getDataGenerator()->create_course();
        $seminar = $this->getDataGenerator()->create_module('facetoface', ['course' => $course->id]);
        $event_id = $facetoface_generator->add_session(['facetoface' => $seminar->id, 'sessiondates' => [
            (object) [
                'timestart' => time() + DAYSECS,
                'timefinish' => time() + DAYSECS * 2,
                'sessiontimezone' => 'Pacific/Auckland',
            ]
        ]]);
        $event = new seminar_event($event_id);

        $result = $this->resolve('seminar', $event);
        $this->assertEquals($seminar->id, $result->get_id());
    }

    public function test_resolve_event_not_instanceof_stdclass_integer(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Only seminar_event instances are accepted. Instead, got: integer");
        $this->resolve_graphql_type('mod_facetoface_event', 'finish_date', 123);
    }

    public function test_resolve_event_not_instanceof_object(): void {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Only seminar_event instances are accepted. Instead, got: object");
        $this->resolve_graphql_type('mod_facetoface_event', 'finish_date', (object) [123, 'will smith']);
    }
}
