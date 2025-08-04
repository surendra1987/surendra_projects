<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Kian Nguyen <kian.nguyen@totaralearning.com>
 * @package core_container
 */
defined('MOODLE_INTERNAL') || die();

use core_container\container_category_helper;
use core_phpunit\testcase;

class core_container_category_helper_test extends testcase {

    /**
     * @return void
     */
    public function test_create_category(): void {
        global $DB;

        $course_cat = container_category_helper::create_container_category(
            'container_course',
            0,
            null,
            null
        );

        $this->assertTrue(
            $DB->record_exists('course_categories', ['id' => $course_cat->id])
        );

        $this->assertSame('container_course-0', $course_cat->idnumber);

        // Confirm that we see this container in the IDs
        $is_container = (bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $course_cat->id]);
        $this->assertTrue($is_container);

        // Confirm a tenant container is created correctly
        /** @var totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $this->getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $tenant = $tenant_generator->create_tenant();

        $tenant_cat = container_category_helper::create_container_category('container_course', $tenant->categoryid);
        $is_container = (bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $tenant_cat->id]);
        $this->assertTrue($is_container);
    }

    /**
     * Test the ability to migrate container categories.
     *
     * @return void
     */
    public function test_migrate_container_categories_task(): void {
        global $DB;
        // Create a regular course category, a container course category, migrate and confirm
        // only the container was migrated.

        $regular_cat = coursecat::create(['name' => 'Test', 'idnumber' => 'ABC']);
        $container_cat = container_category_helper::create_container_category('container_course', 0);
        $DB->set_field('course_categories', 'iscontainer', 0, ['id' => $container_cat->id]);

        // All should be false
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $container_cat->id]));
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $regular_cat->id]));

        // Run the migration function
        $this->expectOutputString("Migrated 1 categories to containers\n");
        \core\task\manager::queue_adhoc_task(new \core_container\task\migrate_container_categories());
        $this->executeAdhocTasks();

        // Only the container category should have changed
        $this->assertTrue((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $container_cat->id]));
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $regular_cat->id]));
    }

    public function test_migrate_container_categories_with_tenants_task(): void {
        global $DB;
        // Create a regular course category, a container course category, a tenant container course category, migrate and confirm
        // only the container was migrated.

        /** @var totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $this->getDataGenerator()->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();
        $tenant = $tenant_generator->create_tenant();

        $regular_cat = coursecat::create(['name' => 'Test', 'idnumber' => 'ABC']);
        $container_cat = container_category_helper::create_container_category('container_course', 0);
        $tenant_cat = container_category_helper::create_container_category('container_course', $tenant->categoryid);
        $DB->set_field('course_categories', 'iscontainer', 0, ['id' => $container_cat->id]);
        $DB->set_field('course_categories', 'iscontainer', 0, ['id' => $tenant_cat->id]);

        // All should be false
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $container_cat->id]));
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $tenant_cat->id]));
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $regular_cat->id]));

        // Run the migration function
        \core\task\manager::queue_adhoc_task(new \core_container\task\migrate_container_categories());
        $this->executeAdhocTasks();

        // Only the container categories should have changed
        $this->assertTrue((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $container_cat->id]));
        $this->assertTrue((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $tenant_cat->id]));
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $regular_cat->id]));

        // Run the migration task again to confirm the same results
        \core\task\manager::queue_adhoc_task(new \core_container\task\migrate_container_categories());
        $this->executeAdhocTasks();

        $this->assertTrue((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $container_cat->id]));
        $this->assertTrue((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $tenant_cat->id]));
        $this->assertFalse((bool) $DB->get_field('course_categories', 'iscontainer', ['id' => $regular_cat->id]));

        // Check output of both migrations
        $this->expectOutputString("Migrated 2 categories to containers\nMigrated 0 categories to containers\n");
    }
}