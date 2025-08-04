<?php
/**
 * This file is part of Totara Talent Experience Platform
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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package user
 * @category usagedata
 */

use core_phpunit\testcase;
use core_user\usagedata\count_of_preferred_editor;

class core_user_usagedata_count_of_preferred_editor_test extends testcase {
    public function test_export(): void {
        $generator = $this->getDataGenerator();

        // 3 users with Weka editor and impeccable taste
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'weka', $user);
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'weka', $user);
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'weka', $user);

        // 2 users with textarea
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'textarea', $user);
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'textarea', $user);

        // 4 users with atto edtior
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'atto', $user);
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'atto', $user);
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'atto', $user);
        $user = $generator->create_user();
        set_user_preference('htmleditor', 'atto', $user);

        $results = (new count_of_preferred_editor())->export();

        $this->assertEquals(3, $results['weka']);
        $this->assertEquals(2, $results['textarea']);
        $this->assertEquals(4, $results['atto']);
    }
}