<?php
/**
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
 * @author Simon Chester <simon.chester@totara.com>
 * @package core_mfa
 */

use core_mfa\framework;
use core_phpunit\testcase;
use core_mfa\model\instance as instance_model;

/**
 * @coversDefaultClass \core_mfa\framework
 * @group core_mfa
 */
class core_mfa_framework_test extends testcase {

    /** @inheritDoc */
    protected function setUp(): void {
        set_config('mfa_plugins', 'button');
    }

    /**
     * Create user and set required properties on $USER.
     *
     * @return object
     */
    protected function setup_user() {
        global $USER;
        $user = self::getDataGenerator()->create_user();
        $this->setUser($user);

        $USER->pseudo_id = (int)$user->id;

        return $user;
    }

    /**
     * Test has registered factors.
     *
     * @return void
     */
    public function test_has_registered_factors(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();

        set_config('mfa_plugins', 'totp');

        $framework = new framework($user->id);
        $this->assertFalse($framework->has_registered_factors());

        instance_model::create($user->id, 'totp', 'Samsung Refrigerator', ['secret' => 'abcdef']);
        $this->assertTrue($framework->has_registered_factors());
    }

    /**
     * Test revoking user factors and the events emitted.
     * @return void
     */
    public function test_revoke_factors(): void {
        $generator = self::getDataGenerator();
        $user = $generator->create_user();

        set_config('mfa_plugins', 'totp');

        $framework = new framework($user->id);
        instance_model::create($user->id, 'totp', 'Samsung Refrigerator', ['secret' => 'abcdef']);
        instance_model::create($user->id, 'totp', 'Samsung Refrigerator', ['secret' => 'ghijkl']);

        $sink = $this->redirectEvents();
        $framework->revoke_factors();

        $events = $sink->get_events();

        $cli_event = array_pop($events);
        $this->assertInstanceOf(\core\event\mfa_factors_revoked::class, $cli_event);
        $this->assertEquals(
            "The registered MFA factors for user with id '$user->id' has been revoked via CLI",
            $cli_event->get_description()
        );

        // User no longer has registered factors.
        $this->assertFalse($framework->has_registered_factors());

        // Set a user and test to see the event description changes
        $this->setAdminUser();
        global $USER;
        $framework->revoke_factors();

        $events = $sink->get_events();
        $admin_event = array_pop($events);
        $sink->close();

        $this->assertEquals(
            "The registered MFA factors for user with id '$user->id' has been revoked by user with id '$USER->id'",
            $admin_event->get_description()
        );
    }

    /**
     * @return void
     */
    public function test_is_mfa_required(): void {
        global $CFG, $USER;

        $original_guest = $CFG->siteguest;

        // Setup MFA for a user
        $generator = self::getDataGenerator();
        $user = $generator->create_user();
        set_config('mfa_plugins', 'totp');

        $framework = new framework($user->id);

        // Assert: Already logged in (false)
        $this->setUser($user);
        $this->assertFalse($framework->is_mfa_required());

        // Assert: Is Guest user (false)
        $CFG->siteguest = $user->id; // Trick to make our user the guest user
        $this->setUser(null); // No user logged in
        $this->assertFalse($framework->is_mfa_required());
        $CFG->siteguest = $original_guest; // Reset the guest back to normal

        // Assert: MFA was previously completed (false)
        $USER->mfa_completed = true;
        $this->assertFalse($framework->is_mfa_required());
        $USER->mfa_completed = false;

        // Assert: MFA user has no instances (false)
        $this->assertFalse($framework->is_mfa_required());

        // Assert: User has MFA instances (true)
        instance_model::create($user->id, 'totp', 'Samsung Refrigerator', ['secret' => 'abcdef']);
        $this->assertTrue($framework->is_mfa_required());
    }
}
