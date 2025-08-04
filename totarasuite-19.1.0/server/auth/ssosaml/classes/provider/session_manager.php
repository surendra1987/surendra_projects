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

namespace auth_ssosaml\provider;

use auth_ssosaml\entity\session;
use auth_ssosaml\provider\data\authn_response;

/**
 * Session manager that managers SAML sessions between the Identity Provider(IdP) and Service Provider(SP)
 */
class session_manager {

    /**
     * IdP's id.
     *
     * @var int
     */
    private int $idp_id;

    /**
     * @var bool
     */
    private bool $test_mode = false;

    /**
     * @param int $idp_id
     */
    public function __construct(int $idp_id) {
        $this->idp_id = $idp_id;
    }

    /**
     * Creates an SP initiated session (They must have request_ids).
     * Returns the unique request id.
     *
     * @return string
     */
    public function create_sp_initiated_session(): string {
        // Random prefixes to keep test & regular requests apart
        $request_id_prefix = $this->test_mode ? 'ttp_' : 'sso_';

        $session = (new session([
            'idp_id' => $this->idp_id,
            'request_id' => $request_id_prefix . random_string(20),
            'session_id' => session_id(),
            'test' => $this->test_mode,
            'status' => session::STATUS_INITIATED,
        ]))->save();

        return $session->request_id;
    }

    /**
     * Verify a session was initiated by the SP
     *
     * @param string $request_id
     * @return bool
     * @throws \coding_exception
     */
    public function verify_sp_initiated_request(string $request_id): bool {
        $session = session::repository()
            ->where('idp_id', $this->idp_id)
            ->where('request_id', $request_id)
            ->where('session_id', session_id())
            ->where('status', session::STATUS_INITIATED)
            ->get()->first();

        return !empty($session);
    }

    /**
     * @param string $request_id
     * @return bool
     */
    public function is_test_session(string $request_id): bool {
        $count = session::repository()
            ->where('idp_id', $this->idp_id)
            ->where('request_id', $request_id)
            ->where('session_id', session_id())
            ->where('test', true)
            ->where('status', session::STATUS_INITIATED)
            ->count();

        return $count === 1;
    }


    /**
     * Mark an SP initiated session as completed.
     *
     * @param int $user_id
     * @param authn_response $response
     * @return session
     */
    public function complete_sp_initiated_session(int $user_id, authn_response $response): session {
        /** @var session $session */
        $session = session::repository()
            ->where('idp_id', $this->idp_id)
            ->where('request_id', $response->in_response_to)
            ->where('status', session::STATUS_INITIATED)
            ->order_by('id')
            ->first_or_fail();

        $session->user_id = $user_id;
        $session->name_id = $response->name_id;
        $session->name_id_format = $response->name_id_format;
        $session->session_id = session_id();
        $session->session_index = $response->session_index;
        $session->session_not_on_or_after = $response->session_not_on_or_after;
        $session->status = session::STATUS_COMPLETED;

        return $session->update();
    }

    /**
     * Create an IdP initiated session. (They do not have a request_id)
     *
     * @param int $user_id
     * @param authn_response $response
     * @return session
     */
    public function create_idp_initiated_session(int $user_id, authn_response $response): session {
        return (new session([
            'idp_id' => $this->idp_id,
            'session_id' => session_id(),
            'user_id' => $user_id,
            'name_id' => $response->name_id,
            'name_id_format' => $response->name_id_format,
            'session_index' => $response->session_index,
            'session_not_on_or_after' => $response->session_not_on_or_after,
            'status' => session::STATUS_COMPLETED,
        ]))->save();
    }

    /**
     * Get active session
     *
     * @return session|null
     */
    public static function get_active_session(): ?session {
        /** @var session $session */
        $session = session::repository()
            ->where('session_id', session_id())
            ->where('status', session::STATUS_COMPLETED)
            ->order_by('id', 'DESC')
            ->first();

        return $session;
    }

    /**
     * Delete test sessions for this session id.
     *
     * @param string|null $session_id
     * @param int|null $idp_id Limit to only this IdP
     * @return void
     */
    public static function clear_test_sessions(?string $session_id, ?int $idp_id = null): void {
        session::repository()
            ->where('session_id', $session_id ?? session_id())
            ->where('test', 1)
            ->when(!empty($idp_id), function ($repo) use ($idp_id) {
                $repo->where('idp_id', $idp_id);
            })
            ->delete();
    }

    /**
     * Get the test session for the user.
     *
     * @param string|null $request_id
     * @param string|null $session_id
     * @return session|null
     */
    public function get_test_session(?string $request_id = null, ?string $session_id = null): ?session {
        global $USER;

        $session_repo = session::repository()
            ->where('session_id', $session_id ?? session_id())
            ->where('status', 1)
            ->where('test', 1)
            ->where('user_id', $USER->id);

        if ($request_id) {
            $session_repo->where('request_id', $request_id);
        }

        /** @var session $session */
        $session =  $session_repo
            ->order_by('id', 'DESC')
            ->first();

        return $session;
    }

    /**
     * @param bool $test_mode
     * @return $this
     */
    public function set_test_mode(bool $test_mode = false): self {
        $this->test_mode = $test_mode;
        return $this;
    }

    /**
     * Get the known sessions based on the nameid and IdP. If $session_index is provided then only return that entry.
     * @param string $nameid
     * @param string|null $session_index
     * @return session[]
     */
    public function get_sessions(string $nameid, ?string $session_index = null) : array {
        /** @var session[] $idp_session */
        $idp_session = session::repository()
            ->where('idp_id', $this->idp_id)
            ->where('name_id', $nameid)
            ->where('status', session::STATUS_COMPLETED)
            ->when($session_index !== null, function ($builder) use ($session_index) {
                $builder->where('session_index', $session_index);
            })
            ->order_by('id', 'DESC')
            ->get()
            ->all();

        return $idp_session;
    }
}