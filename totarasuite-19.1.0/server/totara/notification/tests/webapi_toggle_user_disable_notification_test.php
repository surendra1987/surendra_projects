<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @package totara_notification
 */

use core_phpunit\testcase;
use totara_notification\webapi\resolver\mutation\toggle_user_disable_notifications;
use totara_webapi\phpunit\webapi_phpunit_helper;

class totara_notification_webapi_toggle_user_disable_notification_test extends testcase {
    use webapi_phpunit_helper;

    /**
     * Assert the email stop field is updated via the GraphQL call.
     *
     * @return void
     */
    public function test_toggle_user_disable_notifications(): void {
        global $DB;

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertFalse($this->call_endpoint(false));
        $stop = $DB->record_exists('user', ['id' => $user->id, 'emailstop' => 0]);
        $this->assertTrue($stop);

        $this->assertTrue($this->call_endpoint(true));
        $stop = $DB->record_exists('user', ['id' => $user->id, 'emailstop' => 1]);
        $this->assertTrue($stop);

        $this->assertFalse($this->call_endpoint(false));
        $stop = $DB->record_exists('user', ['id' => $user->id, 'emailstop' => 0]);
        $this->assertTrue($stop);
    }

    /**
     * @param bool $state
     * @return mixed
     */
    private function call_endpoint(bool $state) {
        $result = $this->resolve_graphql_mutation(
            $this->get_graphql_name(toggle_user_disable_notifications::class),
            [
                'input' => [
                    'new_state' => $state
                ]
            ]
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('state', $result);

        return $result['state'];
    }
}