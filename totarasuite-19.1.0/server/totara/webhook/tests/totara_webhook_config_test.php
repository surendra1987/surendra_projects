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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package totara_webhook
 */

use core_phpunit\testcase;
use totara_webhook\config;

defined('MOODLE_INTERNAL') || die();

class totara_webhook_totara_webhook_config_test extends testcase {

    /**
     * @return void
     */
    public function test_get_queue_purge_threshold_default(): void {
        $threshold = config::get_queue_purge_threshold();
        $this->assertSame(14 * 24 * 60 * 60, $threshold);
    }

    /**
     * @return void
     */
    public function test_get_dead_letter_purge_threshold_default(): void {
        $threshold = config::get_dead_letter_purge_threshold();
        $this->assertSame(28 * 24 * 60 * 60, $threshold);
    }

    /**
     * @return void
     */
    public function test_get_queue_purge_threshold_override(): void {
        global $CFG;

        $one_day = 86400;
        $CFG->forced_plugin_settings['totara_webhook']['queue_purge_threshold'] = $one_day;
        $threshold = config::get_queue_purge_threshold();
        $this->assertSame($one_day, $threshold);
    }

    /**
     * @return void
     */
    public function test_get_dead_letter_purge_threshold_override(): void {
        global $CFG;

        $one_day = 86400;
        $CFG->forced_plugin_settings['totara_webhook']['dead_letter_purge_threshold'] = $one_day;
        $threshold = config::get_dead_letter_purge_threshold();
        $this->assertSame($one_day, $threshold);
    }
}
