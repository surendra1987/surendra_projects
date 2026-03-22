<?php

/**
 *  This file is part of Totara TXP
 *
 *  Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.

 * @author ben fesili <ben.fesili@totara.com>
 */

use core_phpunit\testcase;
use totara_webhook\interactor\totara_webhook_interactor;
use totara_webhook\model\totara_webhook as totara_webhook_model;

defined('MOODLE_INTERNAL') || die();

/**
 * @group totara_webhook
 */
class totara_webhook_totara_webhook_interactor_test extends testcase {

    public function test_create_interactor_by_model() {
        $model = totara_webhook_model::create(
            'abc',
            'abc'
        );
        $interactor = totara_webhook_interactor::from_totara_webhook($model);
        $this->assertInstanceOf(totara_webhook_interactor::class, $interactor);
    }

    public function test_create_interactor_by_id() {
        $model = totara_webhook_model::create(
        'abc',
        'abc'
        );
        $interactor = totara_webhook_interactor::from_totara_webhook_id($model->id);
        $this->assertInstanceOf(totara_webhook_interactor::class, $interactor);
    }

    public function test_can_view(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $model = totara_webhook_model::create(
            'abc',
            'abc'
        );
        $interactor = totara_webhook_interactor::from_totara_webhook_id($model->id);
        $this->assertFalse($interactor->can_view());
        $role_id = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:viewtotara_webhooks', CAP_ALLOW, $role_id, $context);
        role_assign($role_id, $user->id, $context);
        $this->assertTrue($interactor->can_view());
    }

    public function test_can_manage(): void {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        $model = totara_webhook_model::create(
            'abc',
            'abc'
        );
        $interactor = totara_webhook_interactor::from_totara_webhook_id($model->id);
        $this->assertFalse($interactor->can_manage());
        $role_id = $this->getDataGenerator()->create_role();
        $context = context_system::instance();
        assign_capability('totara/webhook:managetotara_webhooks', CAP_ALLOW, $role_id, $context);
        role_assign($role_id, $user->id, $context);
        $this->assertTrue($interactor->can_manage());
    }

}