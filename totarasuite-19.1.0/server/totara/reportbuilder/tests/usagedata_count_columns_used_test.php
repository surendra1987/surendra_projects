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
 * @package totara_reportbuilder
 */

use core_phpunit\testcase;
use totara_reportbuilder\usagedata\count_columns_used;

class totara_reportbuilder_usagedata_count_columns_used_test extends testcase {

    /**
     * @throws dml_exception
     */
    public function test_export() {

        // # source: `source_1`
        $report_id = $this->create_report('source_1', 'test_report_1', 0);
        // ## type: `type_1`
        // ### value: `value_1` (total: 2)
        $this->create_column($report_id, 'type_1', 'value_1');
        $this->create_column($report_id, 'type_1', 'value_1');
        // ### value: `value_2` (total: 1)
        $this->create_column($report_id, 'type_1', 'value_2');
        // ## type: `type_2`
        // ### value: `value_1` (total: 1)
        $this->create_column($report_id, 'type_2', 'value_1');
        // ### value: `value_2` (total: 3)
        $this->create_column($report_id, 'type_2', 'value_2');
        $this->create_column($report_id, 'type_2', 'value_2');
        $this->create_column($report_id, 'type_2', 'value_2');

        // # source: `source_2`
        $report_id = $this->create_report('source_2', 'test_report_2', 0);
        // ## type: `type_1`
        // ### value: `value_1` (total: 5)
        $this->create_column($report_id, 'type_1', 'value_1');
        $this->create_column($report_id, 'type_1', 'value_1');
        $this->create_column($report_id, 'type_1', 'value_1');
        $this->create_column($report_id, 'type_1', 'value_1');
        $this->create_column($report_id, 'type_1', 'value_1');
        // ## type: `type_2`
        // ### value: `value_1` (total: 1)
        $this->create_column($report_id, 'type_2', 'value_1');
        // ### value: `value_2` (total: 1)
        $this->create_column($report_id, 'type_2', 'value_2');
        // ### value: `value_3` (total: 1)
        $this->create_column($report_id, 'type_2', 'value_3');
        // ### value: `value_4` (total: 1)
        $this->create_column($report_id, 'type_2', 'value_4');

        $results = (new count_columns_used())->export();

        foreach ($results as $result) {
            if ($result['source'] === 'source_1') {
               if ($result['type'] === 'type_1') {
                   if ($result['value'] === 'value_1') {
                      $this->assertEquals(2, $result['count']);
                   } else if ($result['value'] === 'value_2') {
                      $this->assertEquals(1, $result['count']);
                   }
               }

               if ($result['type'] === 'type_2') {
                   if ($result['value'] === 'value_1') {
                      $this->assertEquals(1, $result['count']);
                   } else if ($result['value'] === 'value_2') {
                      $this->assertEquals(3, $result['count']);
                   }
               }

            } else if ($result['source'] === 'source_2') {
                if ($result['type'] === 'type_1') {
                   if ($result['value'] === 'value_1') {
                      $this->assertEquals(5, $result['count']);
                   }
               }

               if ($result['type'] === 'type_2') {
                   if ($result['value'] === 'value_1') {
                      $this->assertEquals(1, $result['count']);
                   } else if ($result['value'] === 'value_2') {
                      $this->assertEquals(1, $result['count']);
                   } else if ($result['value'] === 'value_3') {
                      $this->assertEquals(1, $result['count']);
                   } else if ($result['value'] === 'value_4') {
                      $this->assertEquals(1, $result['count']);
                   }
               }
            }
        }
    }

    /**
     * Creates a simple report_builder record
     * @throws dml_exception
     */
    public function create_report(string $source, string $title, bool $embedded): int {
        global $DB;

        return $DB->insert_record('report_builder', [
            'source' => $source,
            'title' => $title,
            'shortname' => $title,
            'embedded' => (int) $embedded,
        ]);
    }

    /**
     * Creates a report_builder_columns record
     * @throws dml_exception
     */
    public function create_column(int $report_id, string $type, string $value): void {
        global $DB;

        $DB->insert_record('report_builder_columns', [
            'reportid' => $report_id,
            'type' => $type,
            'value' => $value,
            'sortorder' => 0,
        ]);
    }
}