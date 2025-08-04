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
 * @author Andrew McGhie <andrew.mcghie@totaralearning.com>
 * @package block_totara_featured_links
 */

use block_totara_featured_links\tile\default_tile;

require_once('test_helper.php');


defined('MOODLE_INTERNAL') || die();

/**
 * Tests the methods on the block_totara_featured_links\tile\default_tile class
 */
class block_totara_featured_links_tile_default_tile_test extends test_helper {

    /**
     * The block generator instance for the test.
     * @var \block_totara_featured_links\testing\generator $generator
     */
    protected $blockgenerator;

    /**
     * Gets executed before every test case.
     */
    public function setUp(): void {
        parent::setUp();
        $this->blockgenerator = $this->getDataGenerator()->get_plugin_generator('block_totara_featured_links');
    }

    protected function tearDown(): void {
        parent::tearDown();
        $this->blockgenerator = null;
    }

    protected static function getMethod($name) {
        $class = new ReflectionClass(default_tile::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Tests the logic for the accessibility text
     */
    public function test_get_accessibility_text() {
        $this->setAdminUser();
        $blockinstance = $this->blockgenerator->create_instance();
        $tile1 = $this->blockgenerator->create_default_tile($blockinstance->id);
        $access_text = $tile1->get_accessibility_text();
        $this->assertTrue(isset($access_text['sr-only']));

        $data = $this->get_protected_property($tile1, 'data');
        $data->textbody = 'textbody';
        $this->set_protected_property($tile1, 'data', $data);
        $this->assertEquals('textbody', $tile1->get_accessibility_text()['sr-only']);
        $data->heading = 'heading';
        $this->set_protected_property($tile1, 'data', $data);
        $this->assertEquals('heading', $tile1->get_accessibility_text()['sr-only']);
        $data->alt_text = 'alt_text';
        $this->set_protected_property($tile1, 'data', $data);
        $this->assertEquals('alt_text', $tile1->get_accessibility_text()['sr-only']);
    }

    /**
     * Tests the custom save function for the default tile
     */
    public function test_tile_custom_save() {
        global $DB, $CFG;
        $this->setAdminUser();
        $instance = $this->blockgenerator->create_instance();
        $tile1 = $this->blockgenerator->create_default_tile($instance->id);
        $data = new \stdClass();
        $data->type = 'block_totara_featured_links-default_tile';
        $data->sortorder = 4;
        $data->url = 'https://www.example.com';
        $data->heading = 'some heading';
        $data->textbody = 'some textbody';
        $data->background_color = '#123abc';
        $data->alt_text = 'This is some alternative text';

        $tile1->save_content($data);
        $tile_real = new block_totara_featured_links\tile\default_tile($DB->get_record('block_totara_featured_links_tiles', ['id' => $tile1->id]));

        $this->assertSame('some heading', $this->get_protected_property($tile_real, 'data')->heading);
        $this->assertSame('some textbody', $this->get_protected_property($tile_real, 'data')->textbody);
        $this->assertSame('#123abc', $this->get_protected_property($tile_real, 'data')->background_color);
        $this->assertSame('This is some alternative text', $this->get_protected_property($tile_real, 'data')->alt_text);
        // Checks urls with out wwwroot, /, http:// or https:// get http:// appended to them.
        $this->assertSame('https://www.example.com', $this->get_protected_property($tile_real, 'data')->url);
        // Check wwwroot identification.
        $data->url = $CFG->wwwroot . '/';
        $tile1->save_content($data);
        $this->refresh_tiles($tile1);
        $this->assertSame('https://www.example.com/moodle/', $this->get_protected_property($tile1, 'data')->url);
        // Check urls that are to be left alone are.
        $data->url = '/www.example.com';
        $tile1->save_content($data);
        $this->refresh_tiles($tile1);
        $this->assertSame('/www.example.com', $this->get_protected_property($tile1, 'data')->url);
        // Makes sure urls with https:// do not get http:// appended to them.
        $data->url = 'https://www.example.com';
        $tile1->save_content($data);
        $this->refresh_tiles($tile1);
        $this->assertSame('https://www.example.com', $this->get_protected_property($tile1, 'data')->url);
        // Tests using part of the wwwroot.
        $data->url .= '/index.php';
        $tile1->save_content($data);
        $this->refresh_tiles($tile1);
        $this->assertSame('https://www.example.com/index.php', $this->get_protected_property($tile1, 'data')->url);
        // Tests catalogue-style url, like https://www.example.com/totara/catalog/index.php?catalog_learning_type_panel[]=certification
        $data->url = 'https://www.example.com/totara/catalog/index.php?catalog_learning_type_panel[]=certification';
        $tile1->save_content($data);
        $this->refresh_tiles($tile1);
        $this->assertSame('https://www.example.com/totara/catalog/index.php?catalog_learning_type_panel%5B%5D=certification', $this->get_protected_property($tile1, 'data')->url);
    }

    /**
     * renders the default tile with a background
     */
    public function test_tile_render_background() {
        global $CFG , $PAGE;
        $PAGE->set_url('/');
        $instance = $this->blockgenerator->create_instance();
        $tile1 = $this->blockgenerator->create_default_tile($instance->id);
        $context = \context_block::instance($instance->id);

        $file_url = $CFG->dirroot.'/blocks/totara_featured_links/tests/fixtures/test.png';
        $fs = get_file_storage();
        $file_record = ['contextid' => $context->__get('id'),
            'component' => 'block_totara_featured_links',
            'filearea' => 'tile_background',
            'itemid' => 123456,
            'filepath' => '/',
            'filename' => 'background.png',
            'timecreated' => time(),
            'timemodified' => time()];
        $fs->create_file_from_pathname($file_record, $file_url);

        $files = $fs->get_area_files($context->__get('id'),
            'block_totara_featured_links',
            'tile_background',
            123456,
            '',
            false);

        $file = reset($files);
        $data = $this->get_protected_property($tile1, 'data');
        $data->background_img = $file->get_filename();
        $this->set_protected_property($tile1, 'data', $data);

        $this->call_protected_method($tile1, 'encode_data');
        $this->call_protected_method($tile1, 'decode_data');

        $content = $tile1->render_content_wrapper($PAGE->get_renderer('core'), []);
        $this->assertStringContainsString($tile1->id, $content);
    }

    /**
     * Makes sure that the top class is added to the content when its selected.
     */
    public function test_render_content() {
        global $PAGE;
        $PAGE->set_url('/');
        $this->setAdminUser();
        $blockinstance = $this->blockgenerator->create_instance();
        $tile1 = $this->blockgenerator->create_default_tile($blockinstance->id);
        $data = new \stdClass();
        $data->type = 'block_totara_featured_links-default_tile';
        $data->sortorder = 1;
        $data->url = 'www.example.com';
        $data->heading = 'aih';
        $data->textbody = 'jdbgls';
        $data->heading_location = 'top';
        $tile1->save_content($data);

        $this->refresh_tiles($tile1);

        $content = $tile1->render_content_wrapper($PAGE->get_renderer('core'), []);
        $this->assertStringStartsWith('<div', $content);
        $this->assertStringEndsWith('</div>', $content);
        $this->assertStringContainsString('block-totara-featured-links-content-top', $content);
    }

    public function test_get_textbody_template_data() {
        $get_textbody_template_data = self::getMethod('get_textbody_template_data');

        // Empty textbody results in empty string
        $tile = new default_tile();
        $tile->data_filtered = new stdClass();
        $tile->data_filtered->textbody = '';

        $result = $get_textbody_template_data->invokeArgs($tile, []);
        $this->assertEquals('', $result);

        // Plain text results in a <p> tag wrapping
        $tile = new default_tile();
        $tile->data_filtered = new stdClass();
        $tile->data_filtered->textbody = 'This is plain text';

        $tile->data = $tile->data_filtered;

        $result = $get_textbody_template_data->invokeArgs($tile, []);
        $this->assertEquals('<p>This is plain text</p>', $result);

        // Empty JSON editor content results in empty string
        $tile = new default_tile();
        $tile->data_filtered = new stdClass();

        $content = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [],
                ],
            ],
        ];
        $tile->data_filtered->textbody = json_encode($content);
        $tile->data_filtered->textbodyformat = FORMAT_JSON_EDITOR;

        $tile->data = $tile->data_filtered;

        $result = $get_textbody_template_data->invokeArgs($tile, []);
        $this->assertEquals('', $result);

        // Non-empty editor content correctly formats
        $tile = new default_tile();
        $tile->data_filtered = new stdClass();

        $content = [
            'type' => 'doc',
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'This is some content!'
                        ]
                    ],
                ],
            ],
        ];
        $tile->data_filtered->textbody = json_encode($content);
        $tile->data_filtered->textbodyformat = FORMAT_JSON_EDITOR;

        $tile->data = $tile->data_filtered;

        $result = $get_textbody_template_data->invokeArgs($tile, []);
        $this->assertEquals('<p>This is some content!</p>', $result);
    }

    /**
     * Test that the current logged in her details are replace with the user placeholders in the textbody
     *
     * @return void
     * @throws coding_exception
     */
    public function test_render_content_with_valid_placeholders(): void {
        global $PAGE;
        $PAGE->set_url('/');
        $this->setAdminUser();
        $blockinstance = $this->blockgenerator->create_instance();
        $tile1 = $this->blockgenerator->create_default_tile($blockinstance->id);
        $data = new \stdClass();
        $data->type = 'block_totara_featured_links-default_tile';
        $data->sortorder = 1;
        $data->url = 'www.example.com';
        $data->heading = 'aih';
        $data->textbody = 'Welcome [user:first_name]--[user:last_name]--[user:full_name]!!!';
        $data->heading_location = 'top';
        $tile1->save_content($data);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->refresh_tiles($tile1);

        $content = $tile1->render_content_wrapper($PAGE->get_renderer('core'), []);
        $this->assertStringStartsWith('<div', $content);
        $this->assertStringEndsWith('</div>', $content);
        $this->assertStringContainsString('block-totara-featured-links-content-top', $content);
        $this->assertStringContainsString('Welcome '.$user->firstname.'--'.$user->lastname.'--'.fullname($user).'!!!', $content);
    }

    /**
     * test invalid placeholders included in the tile body - the tile should still render the known
     * placeholders, however add debugging statement for unknown placeholders
     *
     * @return void
     * @throws coding_exception
     */
    public function test_render_content_with_invalid_placeholders(): void {
        global $PAGE;
        $PAGE->set_url('/');
        $this->setAdminUser();
        $blockinstance = $this->blockgenerator->create_instance();
        $tile1 = $this->blockgenerator->create_default_tile($blockinstance->id);
        $data = new \stdClass();
        $data->type = 'block_totara_featured_links-default_tile';
        $data->sortorder = 1;
        $data->url = 'www.example.com';
        $data->heading = 'aih';
        $data->textbody = 'Welcome [user:first_name]--[user:last_name]--[user:full_name]!!!-[user:invalid_placeholder]';
        $data->heading_location = 'top';
        $tile1->save_content($data);

        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->refresh_tiles($tile1);

        $content = $tile1->render_content_wrapper($PAGE->get_renderer('core'), []);
        $this->assertDebuggingCalled("The placeholder key '[user:invalid_placeholder]' is not a valid placeholder key provided by the options list");
        $this->assertStringStartsWith('<div', $content);
        $this->assertStringEndsWith('</div>', $content);
        $this->assertStringContainsString('block-totara-featured-links-content-top', $content);
        $this->assertStringContainsString('Welcome '.$user->firstname.'--'.$user->lastname.'--'.fullname($user).'!!!', $content);
    }
}
