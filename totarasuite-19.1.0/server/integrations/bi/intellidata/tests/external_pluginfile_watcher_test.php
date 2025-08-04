<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @package bi_intellidata
 */


use bi_intellidata\watcher\external_pluginfile_watcher;
use core_phpunit\testcase;
use totara_webapi\hook\external_pluginfile_allowed_hook;

class bi_intellidata_external_pluginfile_watcher_test extends testcase {

    /**
     * @return void
     */
    public function test_external_pluginfile_watcher(): void {
        $hook = new external_pluginfile_allowed_hook('bi_intellidata');

        self::assertFalse($hook->allowed());
        self::assertFalse($hook->has_error());

        external_pluginfile_watcher::allow_external_pluginfile($hook);
        self::assertEquals('bi_intellidata', $hook->get_component());
        self::assertTrue($hook->allowed());
    }

}