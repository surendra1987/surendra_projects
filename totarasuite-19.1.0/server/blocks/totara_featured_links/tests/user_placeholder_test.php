<?php
/**
 * This file is part of Totara LMS
 *
 * Copyright (C) 2017 onwards Totara Learning Solutions LTD
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
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package block_totara_featured_links
 */
require_once('test_helper.php');


defined('MOODLE_INTERNAL') || die();

/**
 * Tests the methods on the block_totara_featured_links\tile\default_tile class
 */
class block_totara_featured_links_user_placeholder_test extends test_helper {
    /**
     * Tests the logic for the accessibility text
     */
    public function test_get_notification_available_placeholder_options() {
        $user = $this->getDataGenerator()->create_user();
        $user_placeholder = new \block_totara_featured_links\placeholders\featured_links_placeholders(['user' => $user]);
        $placeholder_options = $user_placeholder::get_available_placeholder_options();
        $user_placeholder_option = $placeholder_options[0];
        $this->assertSame('user', $user_placeholder_option->get_group_key());

        $expected_rendered_content = 'welcome ' . $user->firstname . '--' . $user->lastname . '!!';

        // check that placeholder renderers work
        $engine = totara_placeholder\template_engine\square_bracket\engine::create(\block_totara_featured_links\placeholders\featured_links_placeholders::class, ['user' => $user]);
        $rendered_content = $engine->render_for_user('welcome [user:first_name]--[user:last_name]!!', $user->id);
        $this->assertSame($expected_rendered_content, $rendered_content);

        $mustache_engine = totara_placeholder\template_engine\mustache\engine::create(\block_totara_featured_links\placeholders\featured_links_placeholders::class, ['user' => $user]);
        $mustache_rendered_content = $mustache_engine->render_for_user('welcome {{#user}}{{first_name}}--{{last_name}}{{/user}}!!', $user->id);
        $this->assertSame($expected_rendered_content, $mustache_rendered_content);

        // check that centralised notification renderers work with placeholders
        $engine = totara_notification\placeholder\template_engine\square_bracket\engine::create(\block_totara_featured_links\placeholders\featured_links_placeholders::class, ['user' => $user]);
        $rendered_content = $engine->render_for_user('welcome [user:first_name]--[user:last_name]!!', $user->id);
        $this->assertSame($expected_rendered_content, $rendered_content);

        $mustache_engine = \totara_notification\placeholder\template_engine\mustache\engine::create(\block_totara_featured_links\placeholders\featured_links_placeholders::class, ['user' => $user]);
        $mustache_rendered_content = $mustache_engine->render_for_user('welcome {{#user}}{{first_name}}--{{last_name}}{{/user}}!!', $user->id);
        $this->assertSame($expected_rendered_content, $mustache_rendered_content);
    }
}
