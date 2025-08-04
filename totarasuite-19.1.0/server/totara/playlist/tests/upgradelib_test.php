<?php
/**
 * This file is part of Totara Core
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
 * @package totara_playlist
 */

defined('MOODLE_INTERNAL') || die();

use core_phpunit\testcase;
use totara_engage\entity\engage_resource;
use totara_engage\resource\resource_factory;
use totara_playlist\entity\playlist_resource;
use totara_playlist\totara_engage\card\playlist_card;

class totara_playlist_upgradelib_test extends testcase {
    /**
     * Assert the totara_playlist_fix_course_card_counts correctly resync
     * playlist resource card counts for those incorrectly applied.
     *
     * @return void
     */
    public function test_fix_playlist_card_counts(): void {
        global $DB, $CFG;
        require_once $CFG->dirroot . '/totara/playlist/db/upgradelib.php';

        // Create a playlist and a resource
        $gen = $this->getDataGenerator();
        $user = $gen->create_user();
        $this->setUser($user);

        /** @var \totara_playlist\testing\generator $playlistgen */
        $playlistgen = $gen->get_plugin_generator('totara_playlist');
        $playlist = $playlistgen->create_playlist();
        $card = new playlist_card($playlist->get_id(), 'totara_playlist', $user->id, $playlist->get_access());

        /** @var \engage_article\testing\generator $articlegen */
        $articlegen = $gen->get_plugin_generator('engage_article');
        $article = $articlegen->create_article();
        $normal_article = $articlegen->create_article();

        $resource_item = resource_factory::create_instance_from_id($article->get_id());
        $playlist->add_resource($normal_article);

        // Link this resource manually twice
        $DB->insert_record(playlist_resource::TABLE, [
            'playlistid' => $playlist->get_id(),
            'resourceid' => $resource_item->get_id(),
            'userid' => $user->id,
            'sortorder' => 99,
            'timecreated' => time(),
        ]);
        $DB->insert_record(playlist_resource::TABLE, [
            'playlistid' => $playlist->get_id(),
            'resourceid' => $resource_item->get_id(),
            'userid' => $user->id,
            'sortorder' => 99,
            'timecreated' => time(),
        ]);

        // Confirm there are three resources on this playlist
        $extra = $card->get_extra_data();
        $this->assertEquals(3, $extra['resources']);

        // Mess up the engage resource count
        $resource_item->increase_resource_usage();
        $resource_item->increase_resource_usage();
        $resource_item->increase_resource_usage();

        $count = $DB->get_field(engage_resource::TABLE, 'countusage', ['id' => $resource_item->get_id()]);
        $this->assertEquals(3, $count);

        // Run the cleanup function
        totara_playlist_remove_duplicate_resources();

        // Confirm we only see two resources
        $extra = $card->get_extra_data();
        $this->assertEquals(2, $extra['resources']);

        $count = $DB->get_field(engage_resource::TABLE, 'countusage', ['id' => $resource_item->get_id()]);
        $this->assertEquals(1, $count);

        // Run the cleanup function again
        totara_playlist_remove_duplicate_resources();

        // Confirm we still only see two resources
        $extra = $card->get_extra_data();
        $this->assertEquals(2, $extra['resources']);

        $count = $DB->get_field(engage_resource::TABLE, 'countusage', ['id' => $resource_item->get_id()]);
        $this->assertEquals(1, $count);
    }
}