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

use core\date_format;
use core\format;
use core\webapi\execution_context;
use core_phpunit\testcase;
use mod_facetoface\seminar;
use mod_facetoface\seminar_event;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_type_seminar_test extends testcase {
    use webapi_phpunit_helper;

    private function create_seminar(array $data): seminar {
        /** @var \mod_facetoface\testing\generator $generator */
        $facetoface_generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        if (!array_key_exists('course_id', $data)) {
            $course = $this->getDataGenerator()->create_course();
            $course_id = $course->id;
        } else {
            $course_id = $data['course_id'];
        }

        $facetofacedata = [
            'name' => 'facetoface1',
            'course' => $course_id,
            ...$data,
        ];

        $facetoface = $facetoface_generator->create_instance($facetofacedata);

        return new seminar($facetoface->id);
    }

    private function resolve($field, seminar $seminar, array $args = [], execution_context $ec = null) {
        $this->setAdminUser();
        if (!$ec) {
            $ec = execution_context::create('dev');
            $ec->set_relevant_context($seminar->get_context());
        }
        return $this->resolve_graphql_type('mod_facetoface_seminar', $field, $seminar, $args, null, $ec);
    }

    public function test_resolve_with_incorrect_source() {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Only seminar instances are accepted. Instead, got: ');

        $this->resolve_graphql_type(
            'mod_facetoface_seminar',
            'myfield',
            (object) ['id' => 1]
        );
    }

    public function test_resolve_id(): void {
        $seminar = $this->create_seminar([]);
        $this->assertSame($seminar->get_id(), $this->resolve('id', $seminar));
    }

    public function test_resolve_timecreated(): void {
        $this->assertSame('126331876', $this->resolve('timecreated', $this->create_seminar(['timecreated' => '126331876']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('7', $this->resolve('timecreated', $this->create_seminar(['timecreated' => '7']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970', $this->resolve('timecreated', $this->create_seminar(['timecreated' => '3']), ['format' => date_format::FORMAT_DATE]));
        $this->assertNull($this->resolve('timecreated', $this->create_seminar(['timecreated' => '0']), ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_timemodified(): void {
        $this->assertSame('126331876', $this->resolve('timemodified', $this->create_seminar(['timemodified' => '126331876']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('7', $this->resolve('timemodified', $this->create_seminar(['timemodified' => '7']), ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970', $this->resolve('timemodified', $this->create_seminar(['timemodified' => '3']), ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_name(): void {
        $this->assertSame("youre mod_facetoface with greatness", $this->resolve('name', $this->create_seminar(['name' => "youre mod_facetoface with greatness"])));
        $this->assertSame("and its strange", $this->resolve('name', $this->create_seminar(['name' => "and its strange"])));
        $this->assertSame("I know its a lot", $this->resolve('name', $this->create_seminar(['name' => "I know its a lot"])));
        $this->assertSame("the HAIR the BOD~", $this->resolve('name', $this->create_seminar(['name' => "the HAIR the BOD~"])));
    }

    public function test_resolve_shortname(): void {
        $this->assertSame("dem poggers tables", $this->resolve('shortname', $this->create_seminar(['shortname' => "dem poggers tables"])));
        $this->assertSame("yehehehehe", $this->resolve('shortname', $this->create_seminar(['shortname' => "yehehehehe"])));
        $this->assertSame("except", $this->resolve('shortname', $this->create_seminar(['shortname' => "except"])));
        $this->assertSame("face", $this->resolve('shortname', $this->create_seminar(['shortname' => "face"])));
    }

    public function test_resolve_description(): void {
        global $CFG;
        $this->assertSame("when you stare at the Totara codebase", $this->resolve('description', $this->create_seminar(['intro' => "when you stare at the Totara codebase"])));
        $this->assertSame("what can I say", $this->resolve('description', $this->create_seminar(['intro' => "what can I say"])));
        $this->assertSame("except", $this->resolve('description', $this->create_seminar(['intro' => "except"])));
        $this->assertSame("YER WELCOME", $this->resolve('description', $this->create_seminar(['intro' => "YER WELCOME"])));

        // Ensure files are handled correctly
        $pluginfile_event = $this->create_seminar(['id' => 1, 'intro' => '<img src="@@PLUGINFILE@@/image.jpg" alt="image" />']);
        $context_id = $pluginfile_event->get_context()->id;
        $this->assertSame(
            "<div class=\"text_to_html\"><img src=\"$CFG->wwwroot/pluginfile.php/$context_id/mod_facetoface/intro/image.jpg\" alt=\"image\" /></div>",
            $this->resolve('description', $pluginfile_event, ['format' => format::FORMAT_HTML])
        );
    }

    public function test_resolve_course_id(): void {
        $course = $this->getDataGenerator()->create_course();
        $seminar = $this->create_seminar(['course_id' => $course->id]);
        $this->assertEquals($course->id, $this->resolve('course_id', $seminar));
    }

    public function test_resolve_events(): void {
        $seminar = $this->create_seminar([]);
        $event1 = new seminar_event();
        $event1->set_facetoface($seminar->get_id());
        $event1->save();
        $event2 = new seminar_event();
        $event2->set_facetoface($seminar->get_id());
        $event2->save();
        /** @var \mod_facetoface\seminar_event_list $resolved */
        $resolved = $this->resolve('events', $seminar);
        $this->assertSame(2, $resolved->count());

        $resolved = $resolved->to_records();
        $this->assertEquals($event1->get_id(), reset($resolved)->id);
        $this->assertEquals($event2->get_id(), end($resolved)->id);
    }
}
