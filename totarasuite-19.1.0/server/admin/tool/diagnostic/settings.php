<?php
/**
 * This file is part of Totara Learn
 *
 * Copyright (C) 2022 onwards Totara Learning Solutions LTD
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
 * @author  Johannes Cilliers <johannes.cilliers@totaralearning.com>
 * @package tool_diagnostic
 */

defined('MOODLE_INTERNAL') || die;

/** @var admin_root $ADMIN */
/** @var core_config $CFG */
if (is_siteadmin($USER)) {
    // Add run page.
    $ADMIN->add(
        'systeminformation',
        new admin_externalpage(
            'tool_diagnostic_providers',
            get_string('support_diagnostics_tool', 'tool_diagnostic'),
            "$CFG->wwwroot/admin/tool/diagnostic/index.php"
        )
    );
}
