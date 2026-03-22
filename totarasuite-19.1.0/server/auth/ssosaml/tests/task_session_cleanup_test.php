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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\model\idp;
use auth_ssosaml\task\session_cleanup_task;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\task\session_cleanup_task
 * @group auth_ssosaml
 */
class auth_ssosaml_task_session_cleanup_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_name(): void {
        $obj = new session_cleanup_task();
        $this->assertSame('SSO SAML Session clean up', $obj->get_name());
    }

    /**
     * @return void
     */
    public function test_plugin_disabled_execute(): void {
        $this->ssosaml_generator->disable_plugin();

        global $DB;
        $idp = idp::create(['status' => false], []);
        $DB->insert_record('auth_ssosaml_session',
            (object) [
                'idp_id' => $idp->id,
                'request_id' => 'test',
                'status' => 1,
                'created_at' => time(),
                'updated_at' => time()
            ]
        );
        $obj = new session_cleanup_task();
        $obj->execute();
        $this->assertCount(0, $DB->get_records('auth_ssosaml_session'));
    }

    /**
     * @return void
     */
    public function test_plugin_enabled_execute(): void {
        global $DB;
        global $CFG;

        $CFG->auth = 'manual,ssosaml';
        $idp = idp::create(['status' => false], []);

        for ($i = 0; $i < 3; $i++) {
            $time = time() - DAYSECS * 2;
            $DB->insert_record('auth_ssosaml_session',
                (object) [
                    'idp_id' => $idp->id,
                    'request_id' => 'test',
                    'status' => ($i == 1) ? 1 : 0,
                    'created_at' => $time,
                    'updated_at' => $time
                ]
            );
        }

        $DB->insert_record('auth_ssosaml_session',
            (object) [
                'idp_id' => $idp->id,
                'request_id' => 'test',
                'session_id' => 'test_session',
                'status' => 1,
                'created_at' => time(),
                'updated_at' => time()
            ]
        );

        $obj = new session_cleanup_task();
        $obj->execute();
        $data = $DB->get_records('auth_ssosaml_session');
        $session_data = [];
        foreach ($data as $item) {
            $session_data[] = $item->session_id;
        }
        $this->assertContains('test_session', $session_data);
        $this->assertCount(2, $data);
    }
}
