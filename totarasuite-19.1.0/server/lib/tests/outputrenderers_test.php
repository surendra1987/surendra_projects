<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2021 onwards Totara Learning Solutions LTD
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
 * @author Tatsuhiro Kirihara <tatsuhiro.kirihara@totaralearning.com>
 * @package core
 */

use core_phpunit\testcase;

/**
  * @coversDefaultClass core_renderer
  */
class core_outputrenderers_test extends testcase {
    public static $favicon_url = '';

    public static function data_favicon() {
        return [
            'absolute http' => ['http://[wwwroot]/test.ico', 'http://[wwwroot]/test.ico'],
            'absolute https' => ['https://[wwwroot]/test.ico', 'https://[wwwroot]/test.ico'],
            'relative scheme' => ['//test.ico', 'totara://test.ico'],
            'relative wwwroot' => ['/test.ico', 'totara://[wwwroot]/test.ico'],
            'relative current' => ['test.ico', 'test.ico'],
        ];
    }

    /**
     * @param string $url
     * @param string $expected
     * @dataProvider data_favicon
     * @covers ::favicon
     */
    public function test_favicon(string $url, string $expected) {
        global $OUTPUT, $PAGE, $CFG;
        $CFG->wwwroot = 'totara://example.com/learn';
        $url = str_replace('[wwwroot]', 'example.com/learn', $url);
        $expected = str_replace('[wwwroot]', 'example.com/learn', $expected);
        /** @var moodle_page */
        $page = $PAGE;
        $theme = $page->theme;
        $theme->resolvefaviconcallback = 'test_core_renderer_favicon_resolver';
        /** @var core_renderer */
        $renderer = $OUTPUT;
        self::$favicon_url = $url;
        $actual = $renderer->favicon();
        $this->assertInstanceOf(moodle_url::class, $actual);
        $this->assertEquals($expected, $actual->out(false));
        self::$favicon_url = null;
    }

    public function test_render_single_button(): void {
        global $OUTPUT;
        // Pass to the output renderer an array of catalog search panel filters.
        $output = $OUTPUT->single_button(new moodle_url('abc.com', [
            'catalog_learning_type_panel[0]' => 'course',
            'catalog_learning_type_panel[1]' => 'playlist',
        ]), get_string('continue'), 'get');
        // Check that rendered button HTML handled the array of filters.
        $this->assertTrue(stripos($output, '<input type="hidden" name="catalog_learning_type_panel[0]" value="course" />') !== false);
        $this->assertTrue(stripos($output, '<input type="hidden" name="catalog_learning_type_panel[1]" value="playlist" />') !== false);
    }
}

/**
 * Used by core_outputrenderers_test::test_favicon
 * @return string
 */
function test_core_renderer_favicon_resolver() {
    return core_outputrenderers_test::$favicon_url;
}
