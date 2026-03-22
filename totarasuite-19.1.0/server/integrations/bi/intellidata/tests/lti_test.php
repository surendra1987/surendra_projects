<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\services\lti_service;
use core_phpunit\testcase;

/**
 * User migration test case.
 * @group bi_intellidata
 *
 * @package    bi
 * @subpackage intellidata
 * @copyright  2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 */
class bi_intellidata_lti_test extends testcase {

    private $endpoint;
    private $key;
    private $secret;
    private $debug;

    public function setUp():void {
        $this->endpoint = 'http://bihost/lti';
        $this->key = 'lticonsumerkey';
        $this->secret = 'ltisharedsecret';
        $this->debug = true;
    }

    protected function tearDown(): void {
        $this->endpoint = null;
        $this->key = null;
        $this->secret = null;
        $this->debug = null;
        parent::tearDown();
    }

    /**
     * @covers \bi_intellidata\services\lti_service
     */
    public function test_save_last_processed_data() {
        self::setAdminUser();

        set_config('ltitoolurl', $this->endpoint, 'bi_intellidata');
        set_config('lticonsumerkey', $this->key, 'bi_intellidata');
        set_config('ltisharedsecret', $this->secret,  'bi_intellidata');
        set_config('ltidebug', $this->debug, 'bi_intellidata');

        $ltiservice = new lti_service();

        list($endpoint, $parms, $debug) = $ltiservice->lti_get_launch_data();

        $this->assertEquals($endpoint, $this->endpoint);
        $this->assertEquals($debug, $this->debug);
        $this->assertEquals($parms['oauth_consumer_key'], $this->key);

        // Check for required params.
        $this->assertArrayHasKey('oauth_version', $parms);
        $this->assertArrayHasKey('oauth_nonce', $parms);
        $this->assertArrayHasKey('user_id', $parms);
        $this->assertArrayHasKey('lis_person_contact_email_primary', $parms);
        $this->assertArrayHasKey('lti_version', $parms);
        $this->assertArrayHasKey('oauth_signature', $parms);
        $this->assertArrayHasKey('oauth_signature_method', $parms);
    }
}