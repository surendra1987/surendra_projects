<?php
/**
 * This file is part of Totara Learn
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
 * @author Navjeet Singh <navjeet.singh@totara.com>
 * @package totara_tenant
 */

use core_phpunit\testcase;
use totara_tenant\hook\tenant_user_create_form_definition_complete;

class totara_tenant_tenant_form_user_create_test extends testcase {

    public function test_tenant_user_create_form_definition_hook(): void {
        global $PAGE;
        $generator = $this->getDataGenerator();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $tenant = $tenant_generator->create_tenant();

        $tenant = new core\entity\tenant($tenant);

        $user = new stdClass();
        $user->id = -1;
        $user->timezone = '99';
        $user->tenantid = $tenant->id;

        $hook_sink = $this->redirectHooks();
        $hook_sink->clear();
        $hooks = $hook_sink->get_hooks();
        $this->assertCount(0, $hooks);

        $PAGE->set_url('/totara/tenant/user_create.php', ['tenantid' => $tenant->id]);
        new totara_tenant\form\user_create(
            $PAGE->url, ['editoroptions' => [], 'filemanageroptions' => [], 'user' => $user]
        );

        $hook_sink->close();

        $hooks_triggered = array_map(function($h) {
            return get_class($h);
        }, $hook_sink->get_hooks());

        $this->assertContains(
            tenant_user_create_form_definition_complete::class,
            $hooks_triggered,
            "totara_tenant_form_user_create needs to trigger the tenant_user_create_form_definition_complete hook"
        );
    }
}