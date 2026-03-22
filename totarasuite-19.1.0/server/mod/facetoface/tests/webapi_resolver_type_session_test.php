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
use core_phpunit\testcase;
use mod_facetoface\seminar_session;
use totara_webapi\phpunit\webapi_phpunit_helper;

class mod_facetoface_webapi_resolver_type_session_test extends testcase {

    use webapi_phpunit_helper;

    private function resolve($field, $session, array $args = []) {
        global $USER;
        $this->setAdminUser();
        $context = \context_user::instance($USER->id);
        if (!$session instanceof seminar_session) {
            $session = (new seminar_session())->from_record((object) $session);
        }

        return $this->resolve_graphql_type('mod_facetoface_session', $field, $session, $args, $context);
    }

    public function test_resolve_with_incorrect_source() {
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Only session records from the database are accepted. Instead, got: ');

        $this->resolve_graphql_type(
            'mod_facetoface_session',
            'myfield',
            (object) ['id' => 1]
        );
    }

    public function test_resolve_id(): void {
        // given that I have a seminar session
        // when I request the session and the id field
        // then I should receive the id session fields with the correct values

        $this->assertSame(7, $this->resolve('id', ['id' => 7]));
        $this->assertSame('7', $this->resolve('id', ['id' => '7']));
        $this->assertSame(0, $this->resolve('id', ['id' => 0]));
        $this->assertSame('0', $this->resolve('id', ['id' => '0']));
        $this->assertSame(-10, $this->resolve('id', ['id' => -10]));
        $this->assertSame('-10', $this->resolve('id', ['id' => '-10']));
        $this->assertSame('', $this->resolve('id', ['id' => '']));
    }

    public function test_resolve_timestart(): void {
        // given that I have a seminar session
        // when I request the session and the timestart field
        // then I should receive the timestart session fields with the correct values

        try {
            $value = $this->resolve('timestart', ['timestart' => '123456789']);
            $this->fail('Expected failure on null $format');
        } catch (\coding_exception $ex) {
            $this->assertSame(
                'Coding error detected, it must be fixed by a programmer: Invalid format given',
                $ex->getMessage()
            );
        }

        $this->assertSame('123456789', $this->resolve('timestart', ['timestart' => '123456789'], ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970', $this->resolve('timestart', ['timestart' => '3'], ['format' => date_format::FORMAT_DATE]));
    }

    public function test_resolve_timefinish(): void {
        // given that I have a seminar session
        // when I request the session and the timefinish field
        // then I should receive the timefinish session fields with the correct values

        try {
            $value = $this->resolve('timefinish', ['timefinish' => '123456789']);
            $this->fail('Expected failure on null $format');
        } catch (\coding_exception $ex) {
            $this->assertSame(
                'Coding error detected, it must be fixed by a programmer: Invalid format given',
                $ex->getMessage()
            );
        }

        $this->assertSame('123456789', $this->resolve('timefinish', ['timefinish' => '123456789'], ['format' => date_format::FORMAT_TIMESTAMP]));
        $this->assertSame('1 January 1970', $this->resolve('timefinish', ['timefinish' => '3'], ['format' => date_format::FORMAT_DATE]));
    }
}
