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
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_stepslib.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_activity_task.class.php');
require_once($CFG->dirroot . '/mod/hvp/backup/moodle2/backup_hvp_stepslib.php');
require_once($CFG->dirroot . '/mod/hvp/backup/moodle2/backup_hvp_activity_task.class.php');

class mod_hvp_backup_hvp_activity_task_test extends \core_phpunit\testcase {

    public function test_encode_content_links() {
        global $CFG;
        $CFG->wwwroot = "http://example.com";
        $content = "<div>http://example.com/mod/hvp/index.php?id=59</div>";
        $encoded_content = backup_hvp_activity_task::encode_content_links($content);
        $this->assertSame("<div>$@HVPINDEX*59@$</div>", $encoded_content);

        $content = "<div>http://example.com/mod/hvp/view.php?id=59</div>";
        $encoded_content = backup_hvp_activity_task::encode_content_links($content);
        $this->assertSame("<div>$@HVPVIEWBYID*59@$</div>", $encoded_content);

        $content = "<div>http://example.com/mod/hvp/embed.php?id=59</div>";
        $encoded_content = backup_hvp_activity_task::encode_content_links($content);
        $this->assertSame("<div>$@HVPEMBEDBYID*59@$</div>", $encoded_content);

        $content = "<div>http://example.com/mod/hvp/library/js/h5p-resizer.js</div>";
        $encoded_content = backup_hvp_activity_task::encode_content_links($content);
        $this->assertSame("<div>$@HVPRESIZER@$</div>", $encoded_content);
    }
}