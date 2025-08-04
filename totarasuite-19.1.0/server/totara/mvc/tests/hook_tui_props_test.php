<?php
/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2025 onwards Totara Learning Solutions LTD
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
 * @package totara_mvc
 */

use totara_mvc\hook\tui_props;

defined('MOODLE_INTERNAL') || die();

class totara_mvc_hook_tui_props_test extends \core_phpunit\testcase {
    public function test_hook() {
        // Create hook from props.
        $props = [
            'lang' => 'en',
            'override_me' => ['one', 'two'],
        ];
        $hook = new tui_props('foo', 'bar', $props);

        // Get the hook info and props.
        $this->assertEquals('foo', $hook->get_controller_class());
        $this->assertEquals('bar', $hook->get_tui_component());
        $this->assertEqualsCanonicalizing($props, $hook->get_props());

        // Customise the props.
        $new_props = array_merge($props, ['new_prop' => 'pizza', 'override_me' => ['two', 'three']]);
        $hook->set_props($new_props);

        // Get the customised props.
        $this->assertEqualsCanonicalizing($new_props, $hook->get_props());
    }
}
