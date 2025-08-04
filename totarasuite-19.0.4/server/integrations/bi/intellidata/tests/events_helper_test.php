<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    bi
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


use bi_intellidata\helpers\EventsHelper;
use core_phpunit\testcase;

/**
 * Evetns helper test case.
 *
 * @package    local
 * @subpackage intellidata
 * @copyright  2023
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or late
 * $covers     \bi_intellidata\helpers\EventsHelper
 */
class bi_intellidata_events_helper_test extends testcase {

    /**
     * Test deleted events list.
     *
     * @return void
     * @covers \bi_intellidata\helpers\EventsHelper::deleted_eventslist
     */
    public function test_deleted_eventslist() {

        $this->resetAfterTest(true);

        $events = EventsHelper::deleted_eventslist();

        $this->assertContains('\core\event\user_deleted', $events);
        $this->assertNotContains('\core\event\user_created', $events);
    }
}
