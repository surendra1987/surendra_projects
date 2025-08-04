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
 * @author Cody Finegan <cody.finegan@totara.com>
 * @package auth_ssosaml
 */

use auth_ssosaml\model\saml_log_entry;
use auth_ssosaml\entity\saml_log_entry as saml_log_entry_entity;
use auth_ssosaml\provider\logging\contract;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @covers \auth_ssosaml\model\saml_log_entry
 * @covers \auth_ssosaml\entity\saml_log_entry
 * @group auth_ssosaml
 */
class auth_ssosaml_model_saml_log_entry_test extends base_saml_testcase {
    /**
     * Assert we can fetch an existing log entry
     *
     * @return void
     */
    public function test_get(): void {
        global $DB;

        $idp = $this->ssosaml_generator->create_idp();
        $logger = $this->ssosaml_generator->create_db_logger($idp);

        $log_id = $logger->log_request('abcd', contract::TYPE_LOGOUT, 'abcd');

        $log = saml_log_entry::load_by_id($log_id);

        $context = $log->context;
        $this->assertInstanceOf(context::class, $context);

        // Assert we have a label for each status item
        $prop = new ReflectionProperty(saml_log_entry::class, 'entity');
        $prop->setAccessible(true);
        /** @var \auth_ssosaml\entity\saml_log_entry $entity */
        $entity = $prop->getValue($log);

        $entity->status = contract::STATUS_ERROR;
        $this->assertSame('Failed', $log->status_label);

        $entity->status = contract::STATUS_SUCCESS;
        $this->assertSame('Success', $log->status_label);

        $entity->status = contract::STATUS_INCOMPLETE;
        $this->assertSame('Incomplete', $log->status_label);

        $user = $this->getDataGenerator()->create_user();
        $log->update_user_id($user->id);

        $this->assertEquals($user->id, $entity->user_id);

        // Now make an error
        $log->add_error('This is a triumph');
        $this->assertEquals(contract::STATUS_ERROR, $entity->status);
        $this->assertEquals('This is a triumph', $entity->error);

        // Finally make sure we're saving the test attribute properly
        $entity->test = true;
        $entity->save();

        $result = $DB->get_record(saml_log_entry_entity::TABLE, ['id' => $log->id], 'test');
        $this->assertIsNumeric($result->test);
        $this->assertEquals(1, $result->test);
        $this->assertTrue($entity->test);

        $entity->test = false;
        $entity->save();

        $result = $DB->get_record(saml_log_entry_entity::TABLE, ['id' => $log->id], 'test');
        $this->assertIsNumeric($result->test);
        $this->assertEquals(0, $result->test);
        $this->assertFalse($entity->test);
    }
}
