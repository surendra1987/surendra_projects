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
 * @author Nathan Lewis <nathan.lewis@totara.com>
 * @package container_workspace
 */

use container_workspace\testing\generator as workspace_generator;
use container_workspace\webapi\resolver\type\workspace as workspace_type_resolver;
use core\webapi\execution_context;
use core_phpunit\testcase;
use totara_topic\topic;

/**
 * @group container_workspace
 */
class container_workspace_webapi_type_workspace_test extends testcase {

    /**
     * Test that the correct tags are returned.
     *
     * @return void
     */
    public function test_tags(): void {
        self::setAdminUser();

        $workspace_generator = workspace_generator::instance();
        $workspace1 = $workspace_generator->create_workspace();
        $workspace2 = $workspace_generator->create_workspace();

        $tag1 = topic::create('one');
        $tag2 = topic::create('two');
        $tag3 = topic::create('three');
        $tag4 = topic::create('four');

        $workspace1->add_tags_by_ids([$tag1->get_id(), $tag2->get_id()]);
        $workspace2->add_tags_by_ids([$tag2->get_id(), $tag3->get_id(), $tag4->get_id()]);

        // Check workspace1 returns 2 tags.
        /** @var topic[] $tags */
        $tags = workspace_type_resolver::resolve('tags', $workspace1, [], $this->createMock(execution_context::class));
        $this->assertCount(2, $tags);

        // Check workspace2 returns 3 tag.
        /** @var topic[] $tags */
        $tags = workspace_type_resolver::resolve('tags', $workspace2, [], $this->createMock(execution_context::class));
        $this->assertCount(3, $tags);
        $tag_names = array_map(function (topic $tag) {
            return $tag->get_display_name();
        }, $tags);
        $this->assertEqualsCanonicalizing(['two', 'three', 'four'], $tag_names);
    }
}
