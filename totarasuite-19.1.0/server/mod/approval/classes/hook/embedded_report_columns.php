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
 * @author Kunle Odusan <kunle.odusan@totara.com>
 * @package mod_approval
 */

namespace mod_approval\hook;

use totara_core\hook\base;

/**
 * Hook to add columns to a mod_approval embedded report.
 */
class embedded_report_columns extends base {

    /**
     * Defined columns
     * @var array
     */
    private array $columns = [];

    /**
     * Report name
     * @var string
     */
    private string $report_name;

    /**
     * @param string $report_name
     * @param array $columns
     *
     * @throws \coding_exception
     */
    public function __construct(string $report_name, array $columns) {
        foreach ($columns as $column) {
            if (!is_array($column)) {
                throw new \coding_exception("Invalid column provided");
            }
        }
        $this->columns = $columns;
        $this->report_name = $report_name;
    }

    /**
     * Get the report name
     * @return string
     */
    public function get_report_name(): string {
        return $this->report_name;
    }

    /**
     * Get the columns
     * @return array
     */
    public function get_columns(): array {
        return $this->columns;
    }

    /**
     * Add a column
     * @param array $column
     *
     * @return void
     */
    public function add_column(array $column) {
        $this->columns[] = $column;
    }
}