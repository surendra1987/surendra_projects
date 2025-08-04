<?php
/**
 * This file is part of Totara Learn
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
 * @author  Matthias Bonk <matthias.bonk@totara.com>
 * @package tool_diagnostic
 */

namespace tool_diagnostic\provider;

use core\orm\query\builder;
use tool_diagnostic\content\content;

class db_row_counts extends base {

    /** @var int */
    protected static $order = 80;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'db_row_counts';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());

        $row_counts = [];
        $db = builder::get_db();
        foreach ($db->get_tables() as $table) {
            $row_counts[$table] = $db->count_records($table);
        }

        // Highest counts first.
        arsort($row_counts);

        $padding = strlen((string)max($row_counts));

        $output = '';
        foreach ($row_counts as $table => $count) {
            if ($count > 0) {
                $output .= str_pad($count, $padding, ' ', STR_PAD_LEFT) . ' ' . $table . PHP_EOL;
            }
        }

        $content->set_data($output);
        $content->set_data_type(content::TYPE_TEXT);

        return $content;
    }
}
