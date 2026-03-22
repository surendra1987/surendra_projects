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

use auth_ssosaml\entity\idp_user_map as idp_user_map_entity;
use auth_ssosaml\model\idp as idp_model;
use auth_ssosaml\model\idp_user_map as idp_user_map_model;

require_once __DIR__ . '/base_saml_testcase.php';

/**
 * @coversDefaultClass \auth_ssosaml\model\idp_user_map
 * @group auth_ssosaml
 */
class auth_ssosaml_model_idp_user_map_test extends base_saml_testcase {
    /**
     * @return void
     * @covers ::create_and_invalidate
     * @covers ::is_confirmed
     */
    public function test_create_and_invalidate(): void {
        global $DB;

        $idp = idp_model::create(['status' => 1], []);
        $user = $this->getDataGenerator()->create_user();
        $statuses = [
            idp_user_map_model::STATUS_INVALID,
            idp_user_map_model::STATUS_CONFIRMED,
            idp_user_map_model::STATUS_UNCONFIRMED,
            null,
        ];

        $now = time();

        // Create some dummy map records
        for ($i = 0; $i < 5; $i++) {
            (new idp_user_map_entity([
                'idp_id' => $idp->id,
                'user_id' => $user->id,
                'status' => $statuses[array_rand($statuses, 1)] ?? idp_user_map_model::STATUS_UNCONFIRMED,
                'code' => random_string(32),
                'code_expiry' => $now,
            ]))->save();
        }

        $this->assert_map_count(5, $user->id, $idp->id);

        // Create the new map, twice
        foreach ($statuses as $status) {
            $map = idp_user_map_model::create_and_invalidate($idp->id, $user->id, $status);

            $this->assert_map_count(1, $user->id, $idp->id);

            $this->assertInstanceOf(idp_user_map_model::class, $map);
            $this->assertEquals($user->id, $map->user_id);
            $this->assertEquals($idp->id, $map->idp_id);
            $this->assertEquals($status ?? idp_user_map_model::STATUS_UNCONFIRMED, $map->status);

            $this->assertEquals($status === idp_user_map_model::STATUS_CONFIRMED, $map->is_confirmed());

            $record = $DB->get_record(idp_user_map_entity::TABLE, ['user_id' => $user->id, 'idp_id' => $idp->id], 'id', MUST_EXIST);
            $this->assertEquals($record->id, $map->id);
        }
    }

    /**
     * @return void
     * @covers ::send_confirmation_email
     */
    public function test_send_confirmation_email(): void {
        $idp = idp_model::create(['status' => 1, 'label' => 'ABC'], []);
        $user = $this->getDataGenerator()->create_user();

        $idp_map = idp_user_map_model::create_and_invalidate($idp->id, $user->id, idp_user_map_model::STATUS_UNCONFIRMED);

        $sink = $this->redirectEmails();
        $idp_map->send_confirmation_email('abc123', $user, $idp->label);
        $messages = $sink->get_messages();
        $sink->close();

        $this->assertCount(1, $messages);

        // Check the message includes the confirmation link
        $message = current($messages);
        $this->assertMatchesRegularExpression("#ssosaml/verify\.php#", $message->body);
        $this->assertSame($user->email, $message->to); // We don't check more because of email wrapping breaks the lookup
    }

    /**
     * @return void
     * @covers ::validate_user_with_code
     */
    public function test_validate_user_with_code(): void {
        global $DB;

        $idp = idp_model::create(['status' => 1], []);
        $user = $this->getDataGenerator()->create_user();
        $now = time();
        $entity = new idp_user_map_entity([
            'idp_id' => $idp->id,
            'user_id' => $user->id,
            'status' => idp_user_map_model::STATUS_CONFIRMED,
            'code' => null,
            'code_expiry' => $now - 1000000,
        ]);
        $entity->save();
        $map = new idp_user_map_model($entity);

        $this->assertNull($map->validate_user_with_code('abc')); // Length
        $this->assertNull($map->validate_user_with_code(null)); // Null
        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123459')); // Expiry

        $entity->code_expiry = $now + 10000;
        $entity->save(); // Push the expiry out to get past this step

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123459')); // Invalid status (confirmed)

        $entity->status = idp_user_map_model::STATUS_INVALID;
        $entity->save();

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123459')); // Invalid status (invalid)

        $entity->status = idp_user_map_model::STATUS_UNCONFIRMED;
        $entity->save();

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123459')); // Code is missing

        $entity->code = 'abcdefghijklmnopqrstuvwxyz123456';
        $entity->save();

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123459')); // Code does not match

        $user->confirmed = 0;
        $DB->update_record('user', $user);

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123456')); // User is not valid

        $user->confirmed = 1;
        $user->suspended = 1;
        $DB->update_record('user', $user);

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123456')); // User is suspended

        $user->suspended = 0;
        $auth = $user->auth;
        $user->auth = 'nologin';
        $DB->update_record('user', $user);

        $this->assertNull($map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123456')); // User cannot login

        $user->auth = $auth;
        $DB->update_record('user', $user);

        $user_result = $map->validate_user_with_code('abcdefghijklmnopqrstuvwxyz123456');
        $this->assertNotNull($user_result);

        $this->assertNull($map->code);
        $this->assertNull($map->code_expiry);
        $this->assertEquals(idp_user_map_model::STATUS_CONFIRMED, $map->status);
    }

    /**
     * @param int $expected
     * @param int $user_id
     * @param int $idp_id
     * @return void
     */
    private function assert_map_count(int $expected, int $user_id, int $idp_id): void {
        global $DB;
        $count = $DB->count_records(idp_user_map_entity::TABLE, compact('user_id', 'idp_id'));
        $this->assertSame($expected, $count);
    }
}
