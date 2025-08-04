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
 * @package auth_ssosaml
 */

use auth_ssosaml\entity\saml_log_entry;
use auth_ssosaml\model\idp;
use auth_ssosaml\provider\logging\db_logger;
use auth_ssosaml\provider\logging\log_context;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\provider\logging\db_logger
 * @group auth_ssosaml
 */
class auth_ssosaml_provider_logging_db_logger_test extends base_saml_testcase {
    /**
     * @return void
     */
    public function test_get_logger(): void {
        $idp = idp::create(['status' => true], []);
        $logger = new db_logger($idp->id);

        $logger->log_request('abc123', 'foo', 'bar');
        $logger->log_response('baz', 'qux', $logger::STATUS_SUCCESS, log_context::create('abc123'));

        $logger->log_request('123456', 'baz', 'bar2');
        $logger->log_response('bob', 'qux', $logger::STATUS_SUCCESS, log_context::create('123456'));

        $id = $logger->log_response('bill', 'yep', $logger::STATUS_SUCCESS);
        $logger->log_error($id, 'my hovercraft is full of eels');

        /** @var saml_log_entry[] $entries */
        $entries = saml_log_entry::repository()->get()->all();
        $this->assertCount(3, $entries);

        $this->assertEquals($idp->id, $entries[0]->idp_id);
        $this->assertEquals('foo', $entries[0]->type);
        $this->assertEquals('bar', $entries[0]->content_request);
        $this->assertEquals('qux', $entries[0]->content_response);
        $this->assertEquals('abc123', $entries[0]->request_id);

        $this->assertEquals($idp->id, $entries[1]->idp_id);
        $this->assertEquals('baz', $entries[1]->type);
        $this->assertEquals('bar2', $entries[1]->content_request);
        $this->assertEquals('qux', $entries[1]->content_response);
        $this->assertEquals('123456', $entries[1]->request_id);

        $this->assertEquals($idp->id, $entries[2]->idp_id);
        $this->assertEquals('bill', $entries[2]->type);
        $this->assertNull($entries[2]->content_request);
        $this->assertEquals('yep', $entries[2]->content_response);
        $this->assertNull($entries[2]->request_id);
        $this->assertEquals('my hovercraft is full of eels', $entries[2]->error);
    }
}
