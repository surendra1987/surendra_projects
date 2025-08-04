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
 * @package core
 */

namespace core\usagedata;

use tool_usagedata\export;

class role_assignment_count_by_contextlevel implements export {

    public const COMPONENT = 'core_role';

    /**
     * @throws \coding_exception
     */
    public function get_summary(): string {
        return get_string('role_assignment_count_by_contextlevel_summary', 'role');
    }

    /**
     * @inheritDoc
     */
    public function get_type(): int {
        return export::TYPE_OBJECT;
    }

    /**
     * @throws \dml_exception
     */
    public function export(): array {
        global $DB;

        $sql = 'SELECT context.contextlevel, COUNT(role_assignment.id) AS assignmentcount
                  FROM {role_assignments} role_assignment
                  JOIN {role} role ON role.id = role_assignment.roleid
                  JOIN {context} context ON context.id = role_assignment.contextid
              GROUP BY context.contextlevel';

        return $DB->get_records_sql_menu($sql);
    }
}