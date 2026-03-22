<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2024 onwards Totara Learning Solutions LTD
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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_oauth2
 */

use auth_oauth2\linked_login;
use core\entity\tenant;
use core\entity\user;
use core\oauth2\client;
use core\orm\query\builder;
use core_phpunit\testcase;

/**
 * Test the auth class
 */
class auth_oauth2_auth_suspension_test extends testcase {

    private $auth = null;
    private $client = null;
    private $user = null;
    private $tenant = null;

    /**
     * Assert that a tenant user is suspended when logging in from a suspended tenant
     *
     * @return void
     */
    public function test_suspended_mapped_users(): void {
        // Linked login
        $record = new stdClass();
        $record->userid = $this->user->id;
        $record->issuerid = $this->client->get_issuer()->get('id');
        $record->username = $this->user->username;
        $record->username_type = linked_login::USERNAME_TYPE_USERNAME;
        $record->email = $this->user->email;
        $record->confirmed = 1;
        $linkedlogin = new linked_login(0, $record);
        $linkedlogin->create();

        // Assert that user who is suspended cannot login
        user::repository()->update_record(['id' => $this->user->id, 'suspended' => 1]);
        $this->assert_cannot_login(get_string('invalidlogin'));

        // Assert a user who is not suspended can login
        user::repository()->update_record(['id' => $this->user->id, 'suspended' => 0]);
        $this->assert_can_login();

        // Migrate the user to a suspended tenant
        $this->getDataGenerator()->get_plugin_generator('totara_tenant')->migrate_user_to_tenant($this->user->id, $this->tenant->id);

        // Assert that user who is suspended via a tenant cannot login
        $this->assert_cannot_login(get_string('invalidlogin'));

        // Assert the user can login once the tenant is unsuspended
        tenant::repository()->update_record(['id' => $this->tenant->id, 'suspended' => 0]);
        $this->assert_can_login();

        // Asser the user cannot login once suspended again
        user::repository()->update_record(['id' => $this->user->id, 'suspended' => 1]);
        $this->assert_cannot_login(get_string('invalidlogin'));

        // Assert the user still cannot login once the tenant is suspended
        tenant::repository()->update_record(['id' => $this->tenant->id, 'suspended' => 1]);
        $this->assert_cannot_login(get_string('invalidlogin'));

        // Assert the user still cannot login even once the tenant is unsuspended
        tenant::repository()->update_record(['id' => $this->tenant->id, 'suspended' => 0]);
        $this->assert_cannot_login(get_string('invalidlogin'));

        // Finally assert the user can login if they're unsuspended
        user::repository()->update_record(['id' => $this->user->id, 'suspended' => 0]);
        $this->assert_can_login();
    }

    /**
     * Assert candidate users cannot be created if the tenant is disabled.
     *
     * @return void
     */
    public function test_suspended_candidate_users(): void {
        // This user has no linked login, so is considered a candidate.
        $this->assert_linked_records(0);

        // Assert that user who is suspended cannot create a candidate
        user::repository()->update_record(['id' => $this->user->id, 'suspended' => 1]);
        $this->assert_cannot_login(get_string('invalidlogin'));
        $this->assert_linked_records(0);

        // Assert that user can create a candidate
        user::repository()->update_record(['id' => $this->user->id, 'suspended' => 0]);
        $this->assert_cannot_login(get_string('confirmationpending', 'auth_oauth2'));
        $this->assert_linked_records(1);

        // Reset the candidate back
        builder::get_db()->delete_records(linked_login::TABLE);
        $this->assert_linked_records(0);

        // Migrate the user to a suspended tenant
        $this->getDataGenerator()->get_plugin_generator('totara_tenant')->migrate_user_to_tenant($this->user->id, $this->tenant->id);

        // Assert that user who is suspended via a tenant cannot create a candidate
        $this->assert_cannot_login(get_string('invalidlogin'));
        $this->assert_linked_records(0);

        // Unsuspend the tenant
        tenant::repository()->update_record(['id' => $this->tenant->id, 'suspended' => 0]);

        // Assert that user can create a candidate
        $this->assert_cannot_login(get_string('confirmationpending', 'auth_oauth2'));
        $this->assert_linked_records(1);
    }

    protected function tearDown(): void {
        $this->auth = null;
        $this->client = null;
        $this->user = null;
        $this->tenant = null;

        parent::tearDown();
    }

    protected function setUp(): void {
        global $CFG;

        /** @var auth_plugin_oauth2 $auth */
        $this->auth = get_auth_plugin('oauth2');
        $CFG->auth = 'manual,oauth2';

        // Create the test user & linked login
        $this->setAdminUser();
        $user = $this->getDataGenerator()->create_user(['auth' => 'oauth2']);
        $issuer = core\oauth2\api::create_standard_issuer('google');

        $this->client = $this->createMock(client::class);
        $this->client->method('get_userinfo')->willReturn([
            'username' => $user->username,
            'email' => $user->email,
        ]);
        $this->client->method('get_issuer')->willReturn($issuer);

        $this->user = $user;

        // Create a tenant
        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $this->getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $this->tenant = $tenant_generator->create_tenant(['suspended' => 1]);

        parent::setUp();
    }

    /**
     * @param int $expected_count
     * @return void
     */
    private function assert_linked_records(int $expected_count): void {
        $count = linked_login::count_records(['userid' => $this->user->id]);
        $this->assertSame($expected_count, $count);
    }

    /**
     * Assert that a user cannot login and trips the following error message.
     *
     * @param string|null $login_error_string
     */
    private function assert_cannot_login(?string $login_error_string = null): void {
        global $SESSION;

        $this->assertNotSame('https://www.example.com/auth-success', $this->complete_oauth_login(), 'user should not be able to login');

        if ($login_error_string) {
            $this->assertEquals($login_error_string, $SESSION->loginerrormsg);
        }
    }

    /**
     * Assert that a user can login
     *
     * @return void
     */
    private function assert_can_login(): void {
        $this->assertSame('https://www.example.com/auth-success', $this->complete_oauth_login(), 'user should be able to login');
    }

    /**
     * Helper method to try to login, capture the redirect error and return the end result URL.
     *
     * @return string
     */
    private function complete_oauth_login(): string {
        try {
            /** @var client $client */
            $client = $this->client;
            $this->auth->complete_login($client, 'https://www.example.com/auth-success');
        } catch (moodle_exception $ex) {
            if (!str_contains($ex->getMessage(), "Unsupported redirect detected")) {
                throw $ex;
            }

            return $ex->link;
        }

        return 'auth-failure';
    }
}
