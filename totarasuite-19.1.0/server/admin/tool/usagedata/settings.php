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
 * @author Aaron Machin <aaron.machin@totara.com>
 * @package tool_usagedata
 */

defined('MOODLE_INTERNAL') || die;

/**
 * @var bool $hassiteconfig
 * @var admin_root $ADMIN
 */

if ($hassiteconfig) {
    $ADMIN->add(
        'systeminformation',
        new admin_externalpage(
            'usagedata',
            new lang_string('pluginname', 'tool_usagedata'),
            new moodle_url("/admin/tool/usagedata/index.php"),
            'moodle/site:config'
        )
    );
}