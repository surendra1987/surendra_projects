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

namespace tool_diagnostic\provider;

use tool_diagnostic\content\content;
use xmldb_table;

class performance_database extends base {

    private $count = 20000;

    /** @var int */
    protected static $order = 110;

    /**
     * @inheritDoc
     */
    public static function get_id(): string {
        return 'performance_database';
    }

    /**
     * @inheritDoc
     */
    public function get_content(): content {
        $content = new content();
        $content->set_id(static::get_id());
        $content->set_data($this->run_tests());
        $content->set_data_type(content::TYPE_TEXT);

        return $content;
    }

    /**
     * Run IO tests.
     *
     * @return string
     */
    private function run_tests(): string {
        $this->create_temp_table();
        $output = $this->write_records();
        $output .= $this->read_records();
        $this->drop_temp_table();

        return $output;
    }

    /**
     * @return void
     */
    private function create_temp_table(): void {
        global $DB;
        $dbman = $DB->get_manager();

        if ($dbman->table_exists('performance_database_temp')) {
            return;
        }

        $xmldb_table = new xmldb_table('performance_database_temp');
        $xmldb_table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $xmldb_table->add_field('column1', XMLDB_TYPE_CHAR, 20, null, XMLDB_NOTNULL, null, null);
        $xmldb_table->add_field('column2', XMLDB_TYPE_FLOAT, '20,5', null, XMLDB_NOTNULL, null, null);
        $xmldb_table->add_field('column3', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null);
        $xmldb_table->add_field('column4', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $xmldb_table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $xmldb_table->add_index('col1_col2_col3', XMLDB_INDEX_NOTUNIQUE, ['column1','column2','column3']);

        $dbman->create_temp_table($xmldb_table);
    }

    /**
     * @return void
     */
    private function drop_temp_table(): void {
        global $DB;
        $db_manager = $DB->get_manager();
        if ($db_manager->table_exists('performance_database_temp')) {
            $xml_db_table = new xmldb_table('performance_database_temp');
            $db_manager->drop_table($xml_db_table);
        }
    }

    /**
     * Get performance metrics for writing database records.
     *
     * @return string
     */
    private function write_records(): string {
        global $DB;
        $start_time = microtime(true);

        for ($i = 0; $i < $this->count; $i++) {
            $DB->insert_record('performance_database_temp', [
                'id' => $i,
                'column1' => random_string(20),
                'column2' => $i / 2,
                'column3' => $i,
                'column4' => 'performance database test ' . $i,
            ]);
        }

        $duration = round(microtime(true) - $start_time, 2);
        $speed = $this->count / $duration;

        return "Duration: " . $duration . " seconds" . PHP_EOL
            . "Record count: " . $this->count . PHP_EOL
            . "Speed: " . round($speed, 2) . " writes per second" . PHP_EOL
            . PHP_EOL;
    }

    /**
     * Get performance metrics for reading database records.
     *
     * @return string
     */
    private function read_records(): string {
        global $DB;
        $start_time = microtime(true);

        $i = 1;
        while (($DB->get_record('performance_database_temp', ['id' => $i])) !== false) {
            ++$i;
        }

        $duration = round(microtime(true) - $start_time, 2);
        $speed = $this->count / $duration;

        return "Duration: " . $duration . " seconds" . PHP_EOL
            . "Record count: " . $this->count . PHP_EOL
            . "Speed: " . round($speed, 2) . " reads per second" . PHP_EOL
            . PHP_EOL;
    }
}
