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
 * @author Angela Kuznetsova <angela.kuznetsova@totara.com>
 * @package engage_course
 */

defined('MOODLE_INTERNAL') || die();

use container_workspace\workspace;
use core\orm\query\exceptions\record_not_found_exception;
use core_phpunit\testcase;
use engage_course\totara_engage\resource\course;
use totara_playlist\playlist;
use totara_webapi\phpunit\webapi_phpunit_helper;

class engage_course_webapi_add_to_playlist_test extends testcase {
    use webapi_phpunit_helper;

    public function test_mutation_add_course_to_playlist() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);
        $course = $this->create_course(['userid' => $user->id]);

        $result = $this->execute_mutation(['playlist_id' => $playlist->get_id(), 'ids' => [$course->get_instanceid()]]);
        $this->assertIsBool($result);
        $this->assertNotEmpty($result);
        $this->assertTrue($result);
    }

    /**
     * Assert that course ids provided are correctly filtered for visibility.
     *
     * @return void
     */
    public function test_mutation_add_course_to_playlist_validates_course_ids() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);
        $course = $this->getDataGenerator()->create_course();
        $workspace = $this->create_workspace();
        $playlist_id = $playlist->get_id();

        // This should fail - there are no valid course ids provided
        try {
            $this->execute_mutation(['playlist_id' => $playlist_id, 'ids' => [$workspace->get_id()]]);
        } catch (\coding_exception $exception) {
            $this->assertStringContainsString('Invalid course_ids provided to add_to_playlist mutation', $exception->getMessage());
        }
        // Confirm that the workspace isn't in the resource table
        $exists = \totara_engage\entity\engage_resource::repository()
            ->where('instanceid', $workspace->get_id())
            ->where('resourcetype', course::get_resource_type())
            ->exists();
        $this->assertFalse($exists);

        // Now try adding a workspace + a course
        try {
            $this->execute_mutation(['playlist_id' => $playlist_id, 'ids' => [$workspace->get_id(), $course->id]]);
        } catch (\coding_exception $exception) {
            $this->assertStringContainsString('Invalid course_ids provided to add_to_playlist mutation', $exception->getMessage());
        }
        // Confirm that the workspace isn't in the resource table
        $exists = \totara_engage\entity\engage_resource::repository()
            ->where('instanceid', $workspace->get_id())
            ->where('resourcetype', course::get_resource_type())
            ->exists();
        $this->assertFalse($exists);

        // Run a course alone
        $this->execute_mutation(['playlist_id' => $playlist_id, 'ids' => [$course->id]]);
        // Confirm that the course is in the resource table
        $exists = \totara_engage\entity\engage_resource::repository()
            ->where('instanceid', $course->id)
            ->where('resourcetype', course::get_resource_type())
            ->exists();
        $this->assertTrue($exists);
    }

    public function test_mutation_add_course_to_playlist_with_tenants(): void {
        $generator = $this->getDataGenerator();

        /** @var \totara_tenant\testing\generator $tenant_generator */
        $tenant_generator = $generator->get_plugin_generator('totara_tenant');
        $tenant_generator->enable_tenants();

        $t1 = $tenant_generator->create_tenant();
        $t2 = $tenant_generator->create_tenant();

        $user1 = $generator->create_user();
        $user2 = $generator->create_user();

        // Move each user into their own tenant
        $tenant_generator->migrate_user_to_tenant($user1->id, $t1->id);
        $tenant_generator->migrate_user_to_tenant($user2->id, $t2->id);

        // Create a course in each tenant
        $c1 = $generator->create_course(['category' => $t1->categoryid]);
        $c2 = $generator->create_course(['category' => $t2->categoryid]);

        // Create the playlist
        $playlist = $this->create_playlist(['userid' => $user1->id]);

        // Confirm that user 1 can add course 1 to the playlist
        $this->setUser($user1);

        $playlist_id = $playlist->get_id();

        try {
            $this->execute_mutation(['playlist_id' => $playlist_id, 'ids' => [$c2->id]]);
        } catch (\coding_exception $exception) {
            $this->assertStringContainsString('Invalid course_ids provided to add_to_playlist mutation', $exception->getMessage());
        }

        // Confirm the course was not added
        $exists = \totara_engage\entity\engage_resource::repository()
            ->where('instanceid', $c2->id)
            ->where('resourcetype', course::get_resource_type())
            ->exists();
        $this->assertFalse($exists);

        // Confirm that user 1 can add course 1 to the playlist
        $result = $this->execute_mutation(['playlist_id' => $playlist->get_id(), 'ids' => [$c1->id]]);
        $this->assertIsBool($result);
        $this->assertTrue($result);

        // Confirm the course was added
        $exists = \totara_engage\entity\engage_resource::repository()
            ->where('instanceid', $c1->id)
            ->where('resourcetype', course::get_resource_type())
            ->exists();
        $this->assertTrue($exists);
    }

    public function test_add_course_to_playlist_with_invalid_id() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage('Invalid course_ids provided to add_to_playlist mutation');
        $this->execute_mutation(['playlist_id' => $playlist->get_id(), 'ids' => [11]]);
    }

    private function setup_user() {
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);
        return $user;
    }

    private function create_playlist($parameters = []): playlist {
        /** @var \totara_playlist\testing\generator $playlistgen */
        $playlistgen = $this->getDataGenerator()->get_plugin_generator('totara_playlist');
        return $playlistgen->create_playlist($parameters);
    }

    private function execute_mutation(array $args) {
        return $this->resolve_graphql_mutation('engage_course_add_to_playlist', $args);
    }

    private function create_course($params = []): course {
        /** @var \engage_course\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('engage_course');
        return $generator->create_course_resource($params);
    }

    private function create_workspace(): workspace {
        /** @var \container_workspace\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('container_workspace');
        return $generator->create_workspace();
    }
}
