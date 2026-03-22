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
 * @author Stefenie Pickston <stefenie.pickston@totara.com>
 * @package mod_facetoface
 */

use core\date_format;
use \core_phpunit\testcase;
use totara_webapi\phpunit\webapi_phpunit_helper;
use mod_facetoface\formatter\session as session_formatter;

class mod_facetoface_formatter_session_test extends testcase
{

    use webapi_phpunit_helper;

    public function test_session_formatter(): void {
        $sessiondate = new stdClass();
        $sessiondate->timestart = 1;
        $sessiondate->timefinish = $sessiondate->timestart + (DAYSECS * 2);
        $sessiondate->sessiontimezone = 'Pacific/Auckland';

        $context = \context_system::instance();
        $formatter = new session_formatter($sessiondate, $context);

        $this->assertSame('1970-01-01T08:00:01+0800', $formatter->format('timestart', date_format::FORMAT_ISO8601));
        $this->assertSame('1970-01-03T08:00:01+0800', $formatter->format('timefinish', date_format::FORMAT_ISO8601));

        $this->assertSame('1 January 1970', $formatter->format('timestart', date_format::FORMAT_DATE));
        $this->assertSame('3 January 1970', $formatter->format('timefinish', date_format::FORMAT_DATE));
    }
}
