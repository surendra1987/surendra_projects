<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @author Simon Chester <simon.chester@totara.com>
 * @package totara_tui
 */
defined('MOODLE_INTERNAL') || die();

use core\json_editor\node\link_media;
use totara_tui\json_editor\output_node\link_media as link_media_output;

class totara_tui_json_editor_link_media_node_test extends \core_phpunit\testcase {
    /**
     * @return void
     */
    public function test_output_component(): void {
        $output = new link_media_output(link_media::from_node(['type' => 'link_media', 'attrs' => ['url' => 'http://example.com', 'title' => 'example']]));
        $this->assertSame(
            '<div class="tui-rendered__block"><a href="http://example.com">example</a></div>',
            $output->render_tui_component_content()
        );

        $output = new link_media_output(link_media::from_node(['type' => 'link_media', 'attrs' => ['url' => 'http://example.com', 'title' => 'hello <b>world</b>']]));
        $this->assertSame(
            '<div class="tui-rendered__block"><a href="http://example.com">hello &lt;b&gt;world&lt;/b&gt;</a></div>',
            $output->render_tui_component_content()
        );

        $output = new link_media_output(link_media::from_node(['type' => 'link_media', 'attrs' => ['url' => 'javascript:alert(1)', 'title' => 'example']]));
        $this->assertSame(
            '<div class="tui-rendered__block"><a href="">example</a></div>',
            $output->render_tui_component_content()
        );
    }
}
