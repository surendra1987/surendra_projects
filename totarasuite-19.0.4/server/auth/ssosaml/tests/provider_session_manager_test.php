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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\entity\session;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\provider\session_manager;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\session_manager
 * @covers \auth_ssosaml\entity\session
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_session_manager_test extends base_saml_testcase {
    /**
     * @var idp
     */
    private $idp;

    /**
     * @var session_manager
     */
    private $session_manager;

    /**
     * @param bool $test_mode
     * @return void
     * @testWith [true]
     *           [false]
     */
    public function test_create_sp_initiated_session(bool $test_mode): void {
        global $DB;

        $this->session_manager->set_test_mode($test_mode);
        $request_id = $this->session_manager->create_sp_initiated_session();

        $request_id_prefix = $test_mode ? 'ttp_' : 'sso_';
        $this->assertNotEmpty($request_id);
        $this->assertEquals($request_id_prefix, substr($request_id, 0, 4));

        $initiated_session = session::repository()
            ->where('request_id', $request_id)
            ->order_by('id')
            ->first_or_fail();

        $this->assertInstanceOf(session::class, $initiated_session);
        $this->assertEquals(session_id(), $initiated_session->session_id);
        $this->assertEquals($this->idp->id, $initiated_session->idp_id);
        $this->assertEquals(session::STATUS_INITIATED, $initiated_session->status);
        $this->assertNotNull($initiated_session->request_id);
        $this->assertNull($initiated_session->session_index);
        $this->assertNull($initiated_session->name_id);
        $this->assertNull($initiated_session->name_id_format);
        $this->assertEquals($test_mode, $initiated_session->test);
    }

    /**
     * @return void
     */
    public function test_verify_request_was_initiated(): void {
        $request_id = $this->session_manager->create_sp_initiated_session();

        $this->assertFalse($this->session_manager->verify_sp_initiated_request('a_random_string'));
        $this->assertTrue($this->session_manager->verify_sp_initiated_request($request_id));
    }

    /**
     * @return void
     */
    public function test_complete_sp_initiated_session(): void {
        // Create a user
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        // Initiate session from sp
        $request_id = $this->session_manager->create_sp_initiated_session();

        $initiated_session = session::repository()
            ->where('request_id', $request_id)
            ->order_by('id')
            ->first_or_fail();

        $completed_session = $this->session_manager->complete_sp_initiated_session(
            $user->id,
            authn_response::make([
                'in_response_to' => $request_id,
                'name_id' => 'a_random_nameid',
                'name_id_format' => idp\config\nameid::FORMAT_PERSISTENT,
                'session_index' => 'a_random_index',
                'issuer' => $this->idp->id,
                'status' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
            ])
        );

        $this->assertEquals($initiated_session->id, $completed_session->id);
        $this->assertEquals($initiated_session->idp_id, $completed_session->idp_id);
        $this->assertEquals($user->id, $completed_session->user_id);
        $this->assertEquals('a_random_nameid', $completed_session->name_id);
        $this->assertEquals(idp\config\nameid::FORMAT_PERSISTENT, $completed_session->name_id_format);
        // unable to mock the session_id
        $this->assertEquals('a_random_index', $completed_session->session_index);
        $this->assertEquals(session::STATUS_COMPLETED, $completed_session->status);
    }

    /**
     * @return void
     */
    public function test_create_idp_initiated_session(): void {
        // Create a user
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        // Check there are no sessions
        $this->assertEquals(0, session::repository()->count());

        // Initiate session from idp
        $completed_session = $this->session_manager->create_idp_initiated_session(
            $user->id,
            authn_response::make([
                'name_id' => 'a_random_nameid',
                'name_id_format' => idp\config\nameid::FORMAT_PERSISTENT,
                'session_index' => 'a_random_index',
                'issuer' => $this->idp->id,
                'status' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
            ])
        );

        $this->assertEquals($this->idp->id, $completed_session->idp_id);
        $this->assertEquals($user->id, $completed_session->user_id);
        $this->assertEquals('a_random_nameid', $completed_session->name_id);
        $this->assertEquals(idp\config\nameid::FORMAT_PERSISTENT, $completed_session->name_id_format);
        $this->assertEquals('a_random_index', $completed_session->session_index);
        $this->assertEquals(session::STATUS_COMPLETED, $completed_session->status);

        // Check there is one session created
        $this->assertEquals(1, session::repository()->count());
    }

    /**
     * Assert get session.
     *
     * @return void
     */
    public function test_get_session_by_nameid(): void {
        // Create a user
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        // Check there are no sessions
        $this->assertEquals(0, session::repository()->count());

        /** @var session[] $created_sessions */
        $created_sessions = [
            $this->session_manager->create_idp_initiated_session(
                $user->id,
                authn_response::make([
                    'name_id' => 'a_random_nameid',
                    'name_id_format' => idp\config\nameid::FORMAT_PERSISTENT,
                    'session_index' => 'a_random_index',
                    'issuer' => $this->idp->id,
                    'status' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
                ])
            ),
            $this->session_manager->create_idp_initiated_session(
                $user->id,
                authn_response::make([
                    'name_id' => 'a_random_nameid',
                    'name_id_format' => idp\config\nameid::FORMAT_PERSISTENT,
                    'session_index' => 'a_random_index2',
                    'issuer' => $this->idp->id,
                    'status' => 'urn:oasis:names:tc:SAML:2.0:status:Success',
                ])
            )
        ];

        foreach ($created_sessions as $created_session) {
            $loaded_sessions = $this->session_manager->get_sessions($created_session->name_id, $created_session->session_index);
            $this->assertCount(1, $loaded_sessions);
            $session = current($loaded_sessions);

            $this->assertEquals($this->idp->id, $session->idp_id);
            $this->assertEquals($user->id, $session->user_id);
            $this->assertEquals($created_session->name_id, $session->name_id);
            $this->assertEquals(idp\config\nameid::FORMAT_PERSISTENT, $session->name_id_format);
            $this->assertEquals($created_session->session_index, $session->session_index);
            $this->assertEquals(session::STATUS_COMPLETED, $session->status);
        }

        $loaded_sessions = $this->session_manager->get_sessions($created_session->name_id);
        $this->assertCount(2, $loaded_sessions);

        // Check there are two sessions created
        $this->assertEquals(2, session::repository()->count());
    }

    /**
     * Assert a test session is created when the IdP is in test mode
     *
     * @return void
     */
    public function test_create_test_session(): void {
        $this->session_manager->set_test_mode(true);
        $session = $this->session_manager->create_sp_initiated_session();
    }

    /**
     * @return void
     */
    protected function setUp(): void {
        $this->idp = idp::create(['status' => false], []);
        $this->session_manager = new session_manager($this->idp->id);
        parent::setUp();
    }

    /**
     * @return void
     */
    protected function tearDown(): void {
        $this->idp = $this->session_manager = null;
        parent::tearDown();
    }
}
