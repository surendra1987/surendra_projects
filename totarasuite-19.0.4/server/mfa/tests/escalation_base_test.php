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

use core_mfa\escalation\base;
use core_phpunit\testcase;
use core_mfa\model\instance as instance_model;

/**
 * @coversDefaultClass core_mfa\escalation\base
 * @group core_mfa
 */
class core_mfa_escalation_base_test extends testcase {
    /**
     * @return void
     */
    public function test_trigger_verify_and_complete(): void {
        global $USER;

        set_config('mfa_plugins', 'button');
        $user = self::getDataGenerator()->create_user();
        instance_model::create($user->id, 'button', 'My Button', []);

        $mock_escalation = $this->getMockForAbstractClass(base::class, [$user->id]);
        $mock_escalation->expects($this->once())->method('on_trigger');
        $mock_escalation->expects($this->once())->method('on_complete');

        $mock_escalation->trigger();
        $this->assertFalse($USER->mfa_completed);
        $this->assertEquals($user->id, $USER->pseudo_id);
        $this->assertEquals(get_class($mock_escalation), $USER->mfa_action);

        $this->assertTrue($mock_escalation->verify('button', []));
        $this->assertTrue($USER->mfa_completed);

        $mock_escalation->complete();
    }

    /**
     * Test complete mfa throws exception on failed verification
     *
     * @return void
     */
    public function test_complete_fails_on_failed_verification(): void {
        global $USER;

        set_config('mfa_plugins', 'button');
        $user = self::getDataGenerator()->create_user();

        $mock_escalation = $this->getMockForAbstractClass(base::class, [$user->id]);

        // Trigger MFA
        $mock_escalation->trigger();
        $this->assertFalse($USER->mfa_completed);
        $this->assertEquals($user->id, $USER->pseudo_id);
        $this->assertEquals(get_class($mock_escalation), $USER->mfa_action);

        instance_model::create($user->id, 'button', 'My Button', []);

        // Verify MFA
        $this->assertFalse($mock_escalation->verify('button', ['invalid' => true]));
        $this->assertFalse($USER->mfa_completed);

        // Complete MFA
        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage("MFA verification not completed");
        $mock_escalation->complete();
    }

    /**
     * @return void
     */
    public function test_lockout(): void {
        global $CFG;
        require_once("$CFG->libdir/authlib.php");

        set_config('lockoutthreshold', 1);

        set_config('mfa_plugins', 'button');
        $user = self::getDataGenerator()->create_user();
        instance_model::create($user->id, 'button', 'My Button', []);

        $mock_escalation = $this->getMockForAbstractClass(base::class, [$user->id]);
        // Trigger MFA
        $mock_escalation->trigger();

        // Verify MFA -- should trigger lockout
        $this->assertFalse(login_is_lockedout($user));
        $this->assertFalse($mock_escalation->verify('button', ['invalid' => true]));
        $this->assertTrue(login_is_lockedout(core_user::get_user($user->id)));
    }
}
