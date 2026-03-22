<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTDvs
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * @author Sam Hemelryk <sam.hemelryk@totara.com>
 * @package core
 */

use core\session\redis5;
use core_phpunit\testcase;

defined('MOODLE_INTERNAL') || die();

class core_session_redis5_test extends testcase {

    private static function get_connection(redis5 $redis): \Redis {
        $reflection = new ReflectionMethod($redis, 'get_aux_connection');
        $reflection->setAccessible(true);
        return $reflection->invoke($redis);
    }

    private static function ensure_configured_for_testing(): void {
        if (!defined('TEST_SESSION_REDIS5_HOST')) {
            self::markTestSkipped('Redis5 host not provided for testing (TEST_SESSION_REDIS5_HOST)');
        }
    }

    public function test_session_exists() {
        self::ensure_configured_for_testing();

        $redis = new redis5();
        self::assertFalse($redis->session_exists('test'));

        $connection = self::get_connection($redis);
        $connection->set('test', 'value');

        self::assertTrue($redis->session_exists('test'));
    }

    public function test_kill_session() {
        self::ensure_configured_for_testing();

        $redis = new redis5();

        $connection = self::get_connection($redis);
        $connection->set('test', 'value');

        $redis->kill_session('test');

        self::assertSame(0, $connection->exists('test'));
    }

    public function test_kill_all_sessions() {
        global $DB;

        self::ensure_configured_for_testing();

        $DB->insert_record('sessions', [
            'state' => 1,
            'sid' => 'test',
            'userid' => 1,
            'sessdata' => 'test',
            'timecreated' => time() - 3600,
            'timemodified' => time() - 1800,
        ]);

        self::assertSame(1, $DB->count_records('sessions'));

        $redis = new redis5();
        $connection = self::get_connection($redis);
        $connection->set('test', 'test');
        self::assertSame(1, $connection->exists('test'));

        $redis->kill_all_sessions();

        self::assertSame(1, $DB->count_records('sessions'));
        self::assertSame(0, $connection->exists('test'));
    }

    public function test_is_locking_configurable() {
        self::ensure_configured_for_testing();
        $redis = new redis5();
        self::assertTrue($redis->is_locking_configurable());
    }

}
