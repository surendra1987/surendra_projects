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

use auth_ssosaml\entity\session;
use auth_ssosaml\model\idp;
use auth_ssosaml\model\idp\config\nameid;
use auth_ssosaml\model\idp\metadata;
use auth_ssosaml\provider\data\authn_response;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * Tests for SSOsaml plugin class.
 *
 * @coversDefaultClass \auth_plugin_ssosaml
 * @group auth_ssosaml
 */
class auth_ssosaml_plugin_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_auth_plugin_instance(): void {
        $auth = get_auth_plugin('ssosaml');
        $this->assertSame('ssosaml', $auth->authtype);
    }

    /**
     * @return void
     */
    public function test_get_loginpage_idp_list(): void {
        global $CFG;
        // Create IdPs
        $idps = [];
        $idps[] = idp::create(['status' => true], []);
        $idps[] = idp::create(['status' => false], []);

        $plugin = get_auth_plugin('ssosaml');
        $idp_list = $plugin->loginpage_idp_list($CFG->wwwroot);

        $generated_urls = array_map(function ($idp) {
            global $CFG;
            return (new moodle_url("/auth/ssosaml/sp/sso.php", [
                'id' => $idp->id,
                'wantsurl' => $CFG->wwwroot,
            ]))->out(false);
        }, $idps);

        $expected_urls = [];
        foreach ($idp_list as $idp) {
            $expected_urls[] = $idp['url'];
        }

        foreach ($expected_urls as $url) {
            $this->assertContains($url, $generated_urls);
        }
        $this->assertCount(1, $idp_list);
    }

    /**
     * @return void
     */
    public function test_ignore_timeout_hook_deletes_ssosaml_sessions(): void {
        $idp = idp::create(
            [
                'status' => true,
                "metadata" => [
                    "source" => metadata::SOURCE_XML,
                    "xml" => file_get_contents(__DIR__ . '/fixtures/idp/sample_idp.xml'),
                ],
                'logout_idp' => false
            ],
            []
        );
        $user = $this->getDataGenerator()->create_user(['auth' => 'ssosaml']);

        $session = $idp->get_session_manager()->create_idp_initiated_session(
            $user->id,
            authn_response::make([
                'session_not_on_or_after' => time(),
                'status' => 'success',
                'issuer' => 'idp',
                'name_id' => 'test_name_id',
                'name_id_format' => nameid::FORMAT_PERSISTENT,
            ])
        );
        $session_id = md5('hokus');
        $session->session_id = $session_id;
        $session->save();

        $plugin = get_auth_plugin('ssosaml');
        $plugin->ignore_timeout_hook($user, $session_id, time(), time());
        $this->assertCount(0, session::repository()->where('session_id', $session_id)->get());
    }
}