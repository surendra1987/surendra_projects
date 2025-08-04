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

use core\login\complete_login_callback;
use core_mfa\escalation\login;
use core_mfa\model\instance;
use core_phpunit\testcase;

require_once __DIR__ . '/fixtures/mock_complete_user_login_callback.php';

/**
 * @coversDefaultClass core_mfa\escalation\login
 * @group core_mfa
 */
class core_mfa_escalation_login_test extends testcase {

    public function test_trigger_verify_and_complete(): void {
        global $USER;

        set_config('mfa_plugins', 'button');
        $user = self::getDataGenerator()->create_user();
        instance::create($user->id, 'button', 'My Button', []);

        $login_escalation = new login($user->id);

        // Trigger MFA
        $login_escalation->trigger([
            'callback' => complete_login_callback::create(
                [mock_mfa_callback::class, 'execute']
            )
        ]);
        $this->assertFalse($USER->mfa_completed);
        $this->assertEquals($user->id, $USER->pseudo_id);
        $this->assertEquals(login::class, $USER->mfa_action);

        // Verify MFA
        $login_escalation->verify('button', []);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Mock callback executed');

        // Complete MFA
        $login_escalation->complete();
    }
}
