<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2020 onwards Totara Learning Solutions LTD
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
 * @author Alastair Munro <alastair.munro@totaralearning.com>
 * @package totara_reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

/**
 * @group totara_reportbuilder
 */
class totara_reportbuilder_chartjs_test extends \core_phpunit\testcase {
    use totara_reportbuilder\phpunit\report_testing;

    protected function init_graph($rid) {
        $report = reportbuilder::create($rid);
        $graph = new \totara_reportbuilder\local\graph\chartjs($report, false);
        $this->assertTrue($graph->is_valid());
        list($sql, $params, $cache) = $report->build_query(false, true);
        $order = $report->get_report_sort(false);
        $reportdb = $report->get_report_db();
        if ($records = $reportdb->get_recordset_sql($sql.$order, $params, 0, $graph->get_max_records())) {
            foreach ($records as $record) {
                $graph->add_record($record);
            }
        }

        return $graph;
    }

    public function test_chartjs_month_created() {
        global $DB;

        $this->setAdminUser();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $rid = $this->create_report('user', 'Test user report 1');

        $config = (new rb_config())->set_nocache(true);
        $report = reportbuilder::create($rid, $config);
        $this->add_column($report, 'user', 'id', null, null, null, 0);
        $this->add_column($report, 'user', 'username', null, null, null, 0);
        $this->add_column($report, 'statistics', 'coursescompleted', null, null, null, 0);
        $this->add_column($report, 'user', 'timecreated', 'month', null, null, 0);

        $graphrecords = $this->add_graph($rid, 'column', 0, 500, 'user-username', '', array('user-timecreated'), '');
        $graphrecord = reset($graphrecords);

        $graph = $this->init_graph($rid);

        $data = $graph->render();
        $this->assertStringNotContainsString('Zero length axis', $data);
        $this->assertStringContainsString($user1->username, $data);
        $this->assertStringContainsString($user2->username, $data);
    }

    /**
     * Assert the chart colour can be overridden.
     *
     * @return void
     */
    public function test_chartjs_pie_category_colors(): void {
        $this->setAdminUser();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $a1 = $this->getDataGenerator()->create_cohort(['name' => 'A1']);
        cohort_add_member($a1->id, $user1->id);
        cohort_add_member($a1->id, $user2->id);

        $a2 = $this->getDataGenerator()->create_cohort(['name' => 'A2']);
        cohort_add_member($a2->id, $user1->id);

        $rid = $this->create_audience_report();

        $custom_settings = json_encode([
            'categoryColors' => [
                'A1' => 'yellow'
            ]
        ]);

        $this->add_graph($rid, 'pie', 0, 500, 'cohort-name', '', array('cohort-numofmembers'), $custom_settings);

        $graph = $this->init_graph($rid);
        $record = $graph->get_render_data(null, null);
        $data = $this->assert_report_has_records($record);

        $labels = $data['labels'] ?? [];
        $this->assertSame(['A1', 'A2'], $labels);

        $datasets = $data['datasets'] ?? [];
        $this->assertIsArray($datasets);
        $dataset = $datasets[0];
        $this->assertArrayHasKey('data', $dataset);
        $this->assertArrayHasKey('backgroundColor', $dataset);

        $this->assertSame([2, 1], $dataset['data']);
        $this->assertSame(['#FFFF00', '#3869B1'], $dataset['backgroundColor']);

        // Create a new audiences report
        $rid = $this->create_audience_report();

        $custom_settings = json_encode([
            'categoryColors' => [
                'A2' => 'yellow'
            ]
        ]);
        $this->add_graph($rid, 'pie', 0, 500, 'cohort-name', '', array('cohort-numofmembers'), $custom_settings);

        $graph = $this->init_graph($rid);
        $record = $graph->get_render_data(null, null);
        $data = $this->assert_report_has_records($record);

        $labels = $data['labels'] ?? [];
        $this->assertSame(['A1', 'A2'], $labels);

        $datasets = $data['datasets'] ?? [];
        $this->assertIsArray($datasets);
        $dataset = $datasets[0];
        $this->assertArrayHasKey('data', $dataset);
        $this->assertArrayHasKey('backgroundColor', $dataset);

        $this->assertSame([2, 1], $dataset['data']);
        $this->assertSame(['#3869B1', '#FFFF00'], $dataset['backgroundColor']);
    }

    public function test_chartjs_column_series_colors(): void {
        $this->setAdminUser();

        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        $a1 = $this->getDataGenerator()->create_cohort(['name' => 'A1']);
        cohort_add_member($a1->id, $user1->id);
        cohort_add_member($a1->id, $user2->id);

        $a2 = $this->getDataGenerator()->create_cohort(['name' => 'A2']);
        cohort_add_member($a2->id, $user1->id);

        $rid = $this->create_audience_report();

        $custom_settings = json_encode([
            'colors' => ['red', 'green'],
            'categoryColors' => [
                'No. of Members' => 'yellow'
            ]
        ]);

        $this->add_graph(
            $rid,
            'column',
            1,
            500,
            'cohort-name',
            '',
            ['cohort-numofmembers', 'cohort-type'],
            $custom_settings
        );

        $graph = $this->init_graph($rid);
        $record = $graph->get_render_data(null, null);

        $data = $this->assert_report_has_records($record);
        $labels = $data['labels'] ?? [];
        $this->assertSame(['A1', 'A2'], $labels);

        $datasets = $data['datasets'] ?? [];
        $this->assertIsArray($datasets);

        $this->assertSame('No. of Members', $datasets[0]['label']);
        $this->assertSame('Count of Type', $datasets[1]['label']);
        $this->assertSame('#FFFF00', $datasets[0]['backgroundColor']);
        $this->assertSame('#FF0000', $datasets[1]['backgroundColor']);
    }

    /**
     * Create a new audiences report
     *
     * @return int
     */
    private function create_audience_report(): int {
        $rid = $this->create_report('cohort', 'Test audiences report 1');
        $config = (new rb_config())->set_nocache(true);
        $report = reportbuilder::create($rid, $config);
        $this->add_column($report, 'cohort', 'name', null, null, null, 0);
        $this->add_column($report, 'cohort', 'numofmembers', null, null, null, 0);
        $this->add_column($report, 'cohort', 'type', null, 'countany', null, 0);

        return $rid;
    }

    /**
     * Repetitive assertions.
     *
     * @param $record
     * @return array
     */
    private function assert_report_has_records($record): array {
        $this->assertIsArray($record);
        $this->assertArrayHasKey('chart', $record);
        $chart = $record['chart'];
        $this->assertIsArray($chart);
        $this->assertArrayHasKey('settings', $chart);
        $settings = json_decode($chart['settings'], true);
        $this->assertIsArray($settings);
        $this->assertArrayHasKey('data', $settings);
        $data = $settings['data'];

        return $data;
    }
}
