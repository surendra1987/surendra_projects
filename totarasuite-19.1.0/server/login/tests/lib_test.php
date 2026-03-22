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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author matthias.bonk@totaralearning.com
 * @package core
 */

defined('MOODLE_INTERNAL') || die();

class core_login_lib_test extends \core_phpunit\testcase {
    /**
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();

        global $CFG;
        require_once($CFG->dirroot . '/login/lib.php');
    }


    public function test_core_login_email_exists_multiple_times(): void {
        global $CFG;

        $CFG->allowaccountssameemail = 1;

        self::getDataGenerator()->create_user([
            'email' => 'unique.email@example.com',
        ]);
        self::getDataGenerator()->create_user([
            'email' => 'duplicate.email@example.com',
        ]);
        self::getDataGenerator()->create_user([
            'email' => 'DupLiCATE.email@example.com',
        ]);

        self::assertTrue(core_login_email_exists_multiple_times('duplicate.email@example.com'));
        self::assertTrue(core_login_email_exists_multiple_times('DUPLICATE.EMAIL@EXAMPLE.COM'));
        self::assertFalse(core_login_email_exists_multiple_times('unique.email@example.com'));
        self::assertFalse(core_login_email_exists_multiple_times('nonexistent.email@example.com'));
        self::assertFalse(core_login_email_exists_multiple_times(''));
        self::assertFalse(core_login_email_exists_multiple_times('   '));
    }

    /**
     * Assert that core_login_set_wants_url only sets
     * the wants url when one hasn't been set already.
     *
     * @return void
     */
    public function test_set_wants_url(): void {
        global $SESSION, $CFG;
        $original = $SESSION->wantsurl ?? null;

        // Confirm it isn't change on related
        $SESSION->wantsurl = 'ABCD';
        core_login_set_wants_url('DEFG');
        $this->assertSame('ABCD', $SESSION->wantsurl);

        // Confirm we can set it
        $SESSION->wantsurl = null;
        core_login_set_wants_url('DEFG');
        $this->assertSame('DEFG', $SESSION->wantsurl);

        // Confirm that we filter our login links
        $invalid = [
            $CFG->wwwroot,
            $CFG->wwwroot . '/login/',
            $CFG->wwwroot . '/login/?abcd=2',
            $CFG->wwwroot . '/login/index.php?abcd=2',
        ];
        foreach ($invalid as $testcase) {
            $SESSION->wantsurl = null;
            core_login_set_wants_url($testcase);
            $this->assertEmpty($SESSION->wantsurl);
        }

        $SESSION->wantsurl = $original;
    }

    /**
     * Assert that external links are caught in the login return URL
     *
     * @return void
     */
    public function test_core_login_get_return_url() {
        global $CFG, $SESSION;

        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        // If external, return homepage
        $SESSION->wantsurl = 'https://external.example.com/path/index.php';
        $this->assertEquals($CFG->wwwroot . '/totara/dashboard/', core_login_get_return_url());

        // If internal, show the link
        $SESSION->wantsurl = $CFG->wwwroot . '/path/index.php';
        $this->assertEquals($CFG->wwwroot . '/path/index.php', core_login_get_return_url());
    }
}
