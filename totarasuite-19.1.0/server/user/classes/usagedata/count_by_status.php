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
 * @author Ning Zhou <ning.zhou@totara.com>
 * @package core_user
 * @category usagedata
 */

namespace core_user\usagedata;

use tool_usagedata\export;

class count_by_status implements export {

    public function get_summary(): string {
        return get_string('count_by_status_summary', 'user');
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

        $sql = 'SELECT COUNT(CASE WHEN (deleted = 0 AND suspended = 0) THEN 1 ELSE NULL END) AS active,
            COUNT(CASE WHEN (deleted = 0 AND suspended = 1) THEN 1 ELSE NULL END) AS suspended,
            COUNT(CASE WHEN deleted = 1 THEN 1 ELSE NULL END) AS deleted
            FROM {user}';
        $result = $DB->get_record_sql($sql);

        return [
            'active' => $result->active,
            'suspended' => $result->suspended,
            'deleted' => $result->deleted,
        ];
    }
}
