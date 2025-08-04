<?php
/**
 * This file is part of Totara Learn
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
 * @author  Maria Torres <maria.torres@totara.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic\provider;

use core\check\manager;
use html_table;
use html_writer;
use tool_diagnostic\content\content;

/**
 * Note: As this is most probably run from the CLI, it will get ini values from the CLI PHP.
 * The webserver's PHP values may differ.
 */
class platform_performance_check extends base {

    /** @var int */
    protected static $order = 140;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'platform_performance_check';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->get_performance_check_info());
        $content->set_data_type(content::TYPE_HTML);

        return $content;
    }

    /**
     * @return string
     */
    private function get_performance_check_info(): string {
        $checks = manager::get_checks('performance');

        $table = new html_table();
        $table->head = [
            get_string('status'),
            get_string('check'),
            get_string('summary'),
            get_string('details'),
        ];

        // Sort the checks alphabetically by name.
        \core_collator::asort_objects_by_method($checks, 'get_name');

        $table->data = [];
        foreach ($checks as $check) {
            $result = $check->get_result();

            $row = [];
            $row[] = $result->get_status();
            $row[] = $check->get_name();
            $row[] = $result->get_summary();
            $row[] = $result->get_details();
            $table->data[] = $row;
        }

        return html_writer::table($table);
    }
}
