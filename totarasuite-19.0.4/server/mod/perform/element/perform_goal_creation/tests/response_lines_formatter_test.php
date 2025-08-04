<?php
/**
 * This file is part of Totara Perform
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package mod_perform
 */

use core\format;
use core_phpunit\testcase;
use performelement_perform_goal_creation\formatter\response_lines_formatter;

/**
 * Unit test(s) for the response_lines_formatter.
 */
class performelement_perform_goal_creation_response_lines_formatter_test extends testcase {

    public function test_with_escaped_description(): void {
        $formatter = new response_lines_formatter(format::FORMAT_PLAIN, context_system::instance());
        $string = '{"goal":{"id":7,"context_id":73,"owner":{"id":"4","username":"ss","firstname":"Sine","lastname":"Seagal"},"user":{"id":"4","username":"ss","firstname":"Sine","lastname":"Seagal"},"name":"test2","id_number":null,"description":"<p>&lt;IMG\\u00a0&quot;&quot;&quot;&gt;&lt;SCRIPT&gt;alert(&quot;XSS&quot;)&lt;\\/SCRIPT&gt;&quot;\\\\&gt;dfdf<\\/p>","start_date":"14\\/11\\/2023","target_type":"date","target_date":"14\\/11\\/2023","target_value":"100","current_value":"0","current_value_updated_at":"14\\/11\\/2023","status":{"id":"not_started","label":"Not started"},"closed_at":null,"created_at":"14\\/11\\/2023","updated_at":"14\\/11\\/2023","plugin_name":"basic"},"raw":{"available_statuses":[{"id":"not_started","label":"Not started"},{"id":"in_progress","label":"In progress"},{"id":"completed","label":"Completed"},{"id":"cancelled","label":"Cancelled"}],"description":"{\\"type\\":\\"doc\\",\\"content\\":[{\\"type\\":\\"paragraph\\",\\"attrs\\":{},\\"content\\":[{\\"type\\":\\"text\\",\\"text\\":\\"<IMG\\u00a0\\\\\\"\\\\\\"\\\\\\"><SCRIPT>alert(\\\\\\"XSS\\\\\\")<\\/SCRIPT>\\\\\\"\\\\\\\\>dfdf\\"}]}]}","start_date":{"iso":"2023-11-14T00:00:00"},"target_date":{"iso":"2023-11-14T00:00:00"}},"permissions":{"can_view":true,"can_manage":true,"can_update_status":true}}';
        self::assertSame($string, $formatter->format($string));
    }
}
