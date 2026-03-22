<?php
/*
 * This file is part of Totara Talent Experience Platform
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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package core_mfa
 */

use core_mfa\escalation\forgot_password;
use core_mfa\model\instance;
use core_phpunit\testcase;

/**
 * @coversDefaultClass core_mfa\escalation\forgot_password
 * @group core_mfa
 */
class core_mfa_escalation_forgot_password_test extends testcase {

    public function test_trigger_without_token(): void {
        $user = self::getDataGenerator()->create_user();
        $forgot_password_escalation = new forgot_password($user->id);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("Token missing");

        // Trigger MFA without Token
        $forgot_password_escalation->trigger();
    }

    public function test_trigger_verify_and_complete(): void {
        global $SESSION, $CFG, $USER;

        set_config('mfa_plugins', 'button');
        $user = self::getDataGenerator()->create_user();
        instance::create($user->id, 'button', 'My Button', []);

        $forgot_password_escalation = new forgot_password($user->id);

        // Trigger MFA
        $forgot_password_escalation->trigger(['token' => 'qwerty']);
        $this->assertEquals('qwerty', $SESSION->password_reset_token);
        $this->assertFalse($USER->mfa_completed);
        $this->assertEquals($user->id, $USER->pseudo_id);
        $this->assertEquals(forgot_password::class, $USER->mfa_action);

        // Verify MFA
        $forgot_password_escalation->verify('button', []);

        // Complete MFA
        try {
            $forgot_password_escalation->complete();
            $this->fail("Redirect exception not thrown");
        } catch (moodle_exception $exception) {
            $this->assertEquals("{$CFG->wwwroot}/login/forgot_password.php", $exception->link);
            $this->assertEquals('redirecterrordetected', $exception->errorcode);
        }
    }
}