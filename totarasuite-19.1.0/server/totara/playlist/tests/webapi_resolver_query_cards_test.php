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
 * @author Qingyang Liu <qingyang.liu@totaralearning.com>
 * @package totara_playlist
 */

defined('MOODLE_INTERNAL') || die();

use engage_article\totara_engage\resource\article;
use engage_course\totara_engage\resource\course;
use totara_playlist\playlist;
use totara_webapi\phpunit\webapi_phpunit_helper;
use totara_engage\access\access;

class totara_playlist_webapi_resolver_query_cards_test extends \core_phpunit\testcase {
    use webapi_phpunit_helper;

    public static function td_visibility_map(): array {
        return [
            'use legacy visibility map' => [true],
            'use new visibility map' => [false]
        ];
    }

    /**
     * @dataProvider td_visibility_map
     */
    public function test_query_cards(bool $disable_visibility_map) {
        global $CFG;
        $CFG->disable_visibility_maps = $disable_visibility_map;

        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $article1 = $this->create_article(['userid' => $user->id, 'access' => access::RESTRICTED]);
        $article2 = $this->create_article(['userid' => $user->id, 'access' => access::PUBLIC]);
        $article3 = $this->create_article(['userid' => $user->id]);
        $course = $this->create_course(['userid' => $user->id]);

        $playlist->add_resource($article1);
        $playlist->add_resource($article2);
        $playlist->add_resource($article3);
        $playlist->add_resource($course);

        $result = $this->execute_query(['id' => $playlist->get_id()]);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result['cursor']);
        $this->assertNotEmpty($result['cards']);
        $this->assertCount(4, $result['cards']);

        $ids = array_map(
            function ($resource): int {
                return $resource->get_instanceid();
            },
            $result['cards']
        );
        $this->assertContainsEquals($article1->get_id(), $ids);
        $this->assertContainsEquals($article2->get_id(), $ids);
        $this->assertContainsEquals($article3->get_id(), $ids);
        $this->assertContainsEquals($course->get_id(), $ids);

        //Course is newly created, so it has to the first element of the array.
        $this->assertEquals($course->get_id(), $ids[0]);

        unset($CFG->disable_visibility_maps);
    }

    public function test_query_cards_with_different_logged_user() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $article1 = $this->create_article(['userid' => $user->id, 'access' => access::RESTRICTED]);
        $article2 = $this->create_article(['userid' => $user->id, 'access' => access::PUBLIC]);
        $article3 = $this->create_article(['userid' => $user->id]);

        $playlist->add_resource($article1);
        $playlist->add_resource($article2);
        $playlist->add_resource($article3);

        $user = $this->getDataGenerator()->create_user();
        // Login as other user
        $this->setUser($user);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Permission denied');
        $this->execute_query(['id' => $playlist->get_id()]);
    }

    public function test_query_cards_with_admin() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $article1 = $this->create_article(['userid' => $user->id, 'access' => access::RESTRICTED]);
        $article2 = $this->create_article(['userid' => $user->id, 'access' => access::PUBLIC]);
        $article3 = $this->create_article(['userid' => $user->id]);
        $course = $this->create_course(['userid' => $user->id]);

        $playlist->add_resource($article1);
        $playlist->add_resource($article2);
        $playlist->add_resource($article3);
        $playlist->add_resource($course);

        // Login as other user
        $this->setAdminUser();

        $result = $this->execute_query(['id' => $playlist->get_id()]);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result['cursor']);
        $this->assertNotEmpty($result['cards']);
        $this->assertCount(4, $result['cards']);

        $ids = array_map(
            function ($resource): int {
                return $resource->get_instanceid();
            },
            $result['cards']
        );
        $this->assertContainsEquals($article1->get_id(), $ids);
        $this->assertContainsEquals($article2->get_id(), $ids);
        $this->assertContainsEquals($article3->get_id(), $ids);
        $this->assertContainsEquals($course->get_id(), $ids);
    }

    public function test_query_cards_with_invalid_id() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $article1 = $this->create_article(['userid' => $user->id, 'access' => access::RESTRICTED]);
        $article2 = $this->create_article(['userid' => $user->id, 'access' => access::PUBLIC]);
        $article3 = $this->create_article(['userid' => $user->id]);

        $playlist->add_resource($article1);
        $playlist->add_resource($article2);
        $playlist->add_resource($article3);

        $this->expectException(moodle_exception::class);
        $this->expectExceptionMessage('Permission denied');
        $this->execute_query(['id' => 11]);
    }

    public function test_query_cards_with_invalid_footnotes_id() {
        $this->execute_query_with_invalid_footnotes(['item_id' => 111], 'Footnotes are not from playlist with 111');
    }

    public function test_query_cards_with_invalid_footnotes_type() {
        $this->execute_query_with_invalid_footnotes(['type' => 'resource'], 'Footnotes type is invalid');
    }

    public function test_query_cards_with_invalid_footnotes_area() {
        $this->execute_query_with_invalid_footnotes(['area' => 'resource'], 'Footnotes area is invalid');
    }

    public function test_query_cards_with_invalid_footnotes_component() {
        $this->execute_query_with_invalid_footnotes(['component' => 'engage_resource'], 'Footnotes component is invalid');
    }

    private function execute_query_with_invalid_footnotes(array $footnotes, string $message) {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $article1 = $this->create_article(['userid' => $user->id, 'access' => access::RESTRICTED]);
        $article2 = $this->create_article(['userid' => $user->id, 'access' => access::PUBLIC]);
        $article3 = $this->create_article(['userid' => $user->id]);

        $playlist->add_resource($article1);
        $playlist->add_resource($article2);
        $playlist->add_resource($article3);

        $this->expectException(coding_exception::class);
        $this->expectExceptionMessage($message);
        $params = ['id' => $playlist->get_id(), 'footnotes' => $footnotes];
        $this->execute_query($params);
    }

    public function test_query_cards_with_valid_footnotes() {
        $user = $this->setup_user();
        $playlist = $this->create_playlist(['userid' => $user->id]);

        $article1 = $this->create_article(['userid' => $user->id, 'access' => access::RESTRICTED]);
        $article2 = $this->create_article(['userid' => $user->id, 'access' => access::PUBLIC]);
        $article3 = $this->create_article(['userid' => $user->id]);

        $playlist->add_resource($article1);
        $playlist->add_resource($article2);
        $playlist->add_resource($article3);

        $params = ['id' => $playlist->get_id(), 'footnotes' => ['item_id' => $playlist->get_id(), 'type' => 'playlist']];
        $result = $this->execute_query($params);
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertNotEmpty($result['cursor']);
        $this->assertNotEmpty($result['cards']);
        $this->assertCount(3, $result['cards']);

        $ids = array_map(
            function ($resource): int {
                return $resource->get_instanceid();
            },
            $result['cards']
        );
        $this->assertContainsEquals($article1->get_id(), $ids);
        $this->assertContainsEquals($article2->get_id(), $ids);
        $this->assertContainsEquals($article3->get_id(), $ids);
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

    private function execute_query(array $args) {
        return $this->resolve_graphql_query('totara_playlist_cards', $args);
    }

    private function create_article($params = []): article {
        /** @var \engage_article\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('engage_article');
        return $generator->create_article($params);
    }

    private function create_course($params = []): course {
        /** @var \engage_course\testing\generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('engage_course');
        return $generator->create_course_resource($params);
    }
}