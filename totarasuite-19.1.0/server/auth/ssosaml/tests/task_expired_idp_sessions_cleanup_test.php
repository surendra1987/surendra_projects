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
use auth_ssosaml\model\idp\config\nameid;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\provider\data\authn_response;
use auth_ssosaml\task\expired_idp_sessions_cleanup;
use core\session\manager;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\task\expired_idp_sessions_cleanup
 * @group auth_ssosaml
 */
class auth_ssosaml_task_expired_idp_sessions_cleanup_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_expired_idp_session_is_removed(): void {
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => [
                    "source" => metadata::SOURCE_XML,
                    "xml" => file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml')
                ],
                'logout_idp' => false
            ],
            []
        );
        $user = $this->getDataGenerator()->create_user();

        $session = $idp->get_session_manager()->create_idp_initiated_session(
            $user->id,
            authn_response::make([
                'session_not_on_or_after' => time() - HOURMINS, // Set the session_not_on_or_after to an hour earlier
                'status' => 'success',
                'issuer' => 'idp',
                'name_id' => 'test_name_id',
                'name_id_format' => nameid::FORMAT_PERSISTENT,
            ])
        );
        $session_id = md5('hokus');
        $session->session_id = $session_id;
        $session->save();

        // create an associated core session record
        $this->create_core_session($session_id, $user->id);

        // Check the session exists
        $this->assertTrue(manager::session_exists($session_id));
        // Execute the task
        $task = new expired_idp_sessions_cleanup();
        $task->execute();

        $this->assertCount(0, session::repository()->where('session_id', $session_id)->get());
        $this->assertFalse(manager::session_exists($session_id));
    }

    /**
     * Create a core session record for the user with the specified session id
     *
     * @param string $session_id
     * @param int $user_id
     * @return void
     */
    private function create_core_session(string $session_id, int $user_id): void {
        global $DB, $CFG;
        // The file handler is used by default, so let's fake the data somehow.
        mkdir("$CFG->dataroot/sessions/", $CFG->directorypermissions, true);
        touch("$CFG->dataroot/sessions/sess_$session_id");
        $DB->insert_record(
            'sessions',
            (object) [
                'state' => 0,
                'sid' => $session_id,
                'sessdata' => null,
                'userid' => $user_id,
                'timecreated' => time(),
                'timemodified' => time(),
                'firstip' => getremoteaddr(),
                'lastip' => getremoteaddr(),
            ]
        );
    }
}