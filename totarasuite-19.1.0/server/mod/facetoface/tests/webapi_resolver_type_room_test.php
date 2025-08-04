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
 * @author Mark Metcalfe <mark.metcalfe@totara.com>
 * @package mod_facetoface
 */

use core\date_format;
use core\format;
use core\webapi\execution_context;
use core_phpunit\testcase;
use mod_facetoface\room;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_type_room_test extends testcase {

    use webapi_phpunit_helper;

    private const TYPE = 'mod_facetoface_room';

    public function test_resolve_with_incorrect_source() {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Only room instances are accepted. Instead, got: ');

        $this->resolve_graphql_type(
            static::TYPE,
            'myfield',
            (object) ['id' => 1]
        );
    }

    public function test_resolve_type(): void {
        global $CFG;
        $user_created = $this->getDataGenerator()->create_user(['username' => 'user_created']);
        $user_modified = $this->getDataGenerator()->create_user(['username' => 'user_modified']);
        $time_created = strtotime("16 April 2024");
        $time_modified = strtotime("17 April 2024");

        /** @var \mod_facetoface\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $room_record = $generator->add_site_wide_room([
            'name' => "Room <script>101</script>",
            'description' => "Room <img src=\"@@PLUGINFILE@@/image.png\" alt=\"image.png\" />",
            'capacity' => 12345,
            'allowconflicts' => 1,
            'url' => "Room <script>URL</script>",
            'usercreated' => $user_created->id,
            'timecreated' => $time_created,
        ]);
        $room_record->usermodified = $user_modified->id;
        $room_record->timemodified = $time_modified;
        $room = (new room())->from_record($room_record);

        $context_id = context_system::instance()->id;

        $this->assertEquals($room_record->id, $this->resolve_graphql_type(static::TYPE, 'id', $room));
        $this->assertEquals("Room 101", $this->resolve_graphql_type(static::TYPE, 'name', $room));
        $this->assertEquals(12345, $this->resolve_graphql_type(static::TYPE, 'capacity', $room));
        $this->assertTrue((bool) $this->resolve_graphql_type(static::TYPE, 'allow_conflicts', $room));
        $this->assertEquals(
            "Room <img src=\"$CFG->wwwroot/pluginfile.php/$context_id/mod_facetoface/room/$room_record->id/image.png\" alt=\"image.png\" />",
            $this->resolve_graphql_type(static::TYPE, 'description', $room, ['format' => format::FORMAT_HTML])
        );
        $this->assertEquals("Room URL", $this->resolve_graphql_type(static::TYPE, 'url', $room));
        $this->assertEquals('user_created', $this->resolve_graphql_type(static::TYPE, 'usercreated', $room)->username);
        $this->assertEquals('user_modified', $this->resolve_graphql_type(static::TYPE, 'usermodified', $room)->username);
        $this->assertEquals("16 April 2024", $this->resolve_graphql_type(static::TYPE, 'timecreated', $room, ['format' => date_format::FORMAT_DATE]));
        $this->assertEquals("17 April 2024", $this->resolve_graphql_type(static::TYPE, 'timemodified', $room, ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_custom_fields(): void {
        /** @var \mod_facetoface\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator("mod_facetoface");
        $room_record = $generator->add_site_wide_room([]);
        $room = (new room())->from_record($room_record);

        /** @var \totara_customfield\testing\generator $custom_field_generator */
        $custom_field_generator = $this->getDataGenerator()->get_plugin_generator('totara_customfield');
        $custom_fields = $custom_field_generator->create_text('facetoface_room', ['text_one', 'text_two']);
        $custom_field_generator->set_text($room_record, $custom_fields['text_one'], 'text', 'facetofaceroom', 'facetoface_room');

        // add the custom fields to the execution context for the room type resolver
        $ec = execution_context::create('dev');
        $ec->set_variable('custom_fields_facetofaceroom', [$room_record->id => $custom_fields]);

        $result = $this->resolve_graphql_type(static::TYPE, 'custom_fields', $room, [], null, $ec);
        $this->assertEquals($custom_fields, $result);
    }

}
