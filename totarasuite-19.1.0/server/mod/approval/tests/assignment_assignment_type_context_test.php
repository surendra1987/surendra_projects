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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Chris Snyder <chris.snyder@totara.com>
 */

use core\entity\context as context_entity;
use core\orm\query\exceptions\record_not_found_exception;
use core_phpunit\testcase;
use mod_approval\model\assignment\assignment_type\context;
use totara_tenant\testing\generator as tenant_generator;

/**
 * @group approval_workflow
 * @covers \mod_approval\model\assignment\assignment_type\context
 */
class mod_approval_assignment_assignment_type_context_test extends testcase {

    public function test_get_label() {
        $this->assertEquals(
            get_string('model_assignment_type_context', 'mod_approval'),
            context::get_label()
        );
    }

    public function test_get_code() {
        $this->assertEquals(
            4,
            context::get_code()
        );
    }

    public function test_get_enum() {
        $this->assertEquals(
            'CONTEXT',
            context::get_enum()
        );
    }

    public function test_get_sort_order() {
        $this->assertEquals(
            40,
            context::get_sort_order()
        );
    }

    public function test_instance_with_fullname_and_shortname() {
        $system_context = context_system::instance();

        $assignment_type = context::instance($system_context->id);
        $this->assertInstanceOf(context::class, $assignment_type);
        $this->assertInstanceOf(context_entity::class, $assignment_type->get_entity());
        $this->assertEquals('System', $assignment_type->get_name());
        $this->assertEquals('context_' . $system_context->id, $assignment_type->get_id_number());
    }

    public function test_tenant_instance_with_fullname_and_shortname() {
        $tenant_generator = tenant_generator::instance();
        $tenant_generator->enable_tenants();
        $tenant_record = $tenant_generator->create_tenant();
        $tenant_context = context_tenant::instance($tenant_record->id);

        $assignment_type = context::instance($tenant_context->id);
        $this->assertInstanceOf(context::class, $assignment_type);
        $this->assertInstanceOf(context_entity::class, $assignment_type->get_entity());
        $this->assertEquals('Tenant_' . $tenant_record->name, $assignment_type->get_name());
        $this->assertEquals('context_' . $tenant_context->id, $assignment_type->get_id_number());
    }

    public function test_invalid_instance() {
        $this->expectException(record_not_found_exception::class);
        context::instance(-1);
    }
}
