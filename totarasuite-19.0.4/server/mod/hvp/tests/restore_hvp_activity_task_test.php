<?php
/**
 * This file is part of Totara Core
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Fesili <ben.fesili@totara.com>
 * @package mod_hvp
 */
global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/restore_stepslib.php');
require_once($CFG->dirroot . '/backup/moodle2/restore_activity_task.class.php');

class mod_hvp_restore_hvp_activity_task_test extends \core_phpunit\testcase {
    public function test_restore_decode_rules() {
        global $CFG;

        $www = $CFG->wwwroot;
        $restoreid = 89765;
        /** @var []\restore_decode_rule $rules */
        $rules = restore_hvp_activity_task::define_decode_rules();

        \restore_controller_dbops::create_restore_temp_tables($restoreid);

        $original = "This is test content.\n\n";
        $original .= "<div>$@HVPINDEX*59@$</div>\n";
        $original .= "<div>$@HVPVIEWBYID*59@$</div>\n";
        $original .= "<div>$@HVPEMBEDBYID*59@$</div>\n";
        $original .= "<div>$@HVPRESIZER@$</div>\n";

        $expected = "This is test content.\n\n";
        $expected .= "<div>{$www}/mod/hvp/index.php?id=59</div>\n";
        $expected .= "<div>{$www}/mod/hvp/view.php?id=59</div>\n";
        $expected .= "<div>{$www}/mod/hvp/embed.php?id=59</div>\n";
        $expected .= "<div>{$www}/mod/hvp/library/js/h5p-resizer.js</div>\n";

        $actual = $original;
        /** @var restore_decode_rule $rule */
        foreach ($rules as $rule) {
            $rule->set_restoreid($restoreid);
            $rule->set_wwwroots($www, $www);
            $actual = $rule->decode($actual);
        }

        \restore_controller_dbops::drop_restore_temp_tables($restoreid);

        $this->assertSame($expected, $actual);
    }
}