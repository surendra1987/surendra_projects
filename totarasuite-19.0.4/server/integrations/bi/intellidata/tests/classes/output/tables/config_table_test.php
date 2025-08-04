<?php

/**
 * This file is part of Totara Core
 *
 * Copyright (C) 2023 onwards Totara Learning Solutions LTD
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
 * @author Scott Davies <scott.davies@totara.com>
 * @package bi_intellidata
 */


use bi_intellidata\output\tables\config_table;
use core_phpunit\testcase;
use bi_intellidata\helpers\SettingsHelper;

/**
 * Unit tests for the config_table class.
 */
class bi_intellidata_config_table_test extends testcase {
    /**
     * Test the config_table building SQL filters based on the totara flavour (flavour set in config_plugins table).
     * @return void
     */
    public function test_with_flavour_no_perform_and_engage(): void {
        // Set up.
        global $PAGE;
        // The default is 'flavour_learn' set in the config_plugins table.
        $original_flavour = get_config('totara_flavour', 'currentflavour');
        $query = '';
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php', ['query' => $query]);
        $PAGE->set_url($pageurl);

        // Operate - create a config_table object ($sql gets set in its constructor), and fetch its $sql property.
        $property = new ReflectionProperty('bi_intellidata\output\tables\config_table', 'sql');
        $property->setAccessible(true);
        $table = new config_table('config_table', $query);
        $sql_obj = $property->getValue($table);

        // Assert - both engage & perform should have filters to be excluded.
        $this->assertTrue(strpos($sql_obj->where, 'perform') !== false);
        $this->assertTrue(strpos($sql_obj->where, 'engage') !== false);
        $this->assertTrue(strpos($sql_obj->where, 'ml\_recommender') !== false);

        // Tear down.
        set_config('currentflavour', $original_flavour, 'totara_flavour');
    }

    /**
     * @return void
     */
    public function test_with_flavour_engage_only(): void {
        // Set up.
        global $PAGE;
        $original_flavour = get_config('totara_flavour', 'currentflavour');
        $query = '';
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php', ['query' => $query]);
        $PAGE->set_url($pageurl);
        set_config('currentflavour', 'flavour_learn_engage', 'totara_flavour');

        // Operate - create a config_table object ($sql gets set in its constructor), and fetch its $sql property.
        $property = new ReflectionProperty('bi_intellidata\output\tables\config_table', 'sql');
        $property->setAccessible(true);
        $table = new config_table('config_table', $query);
        $sql_obj = $property->getValue($table);

        // Assert - perform should have a filter to be excluded.
        $this->assertTrue(strpos($sql_obj->where, 'perform') !== false);
        $this->assertFalse(strpos($sql_obj->where, 'engage') !== false);
        $this->assertFalse(strpos($sql_obj->where, 'ml\_recommender') !== false);

        // Tear down.
        set_config('currentflavour', $original_flavour, 'totara_flavour');
    }

    /**
     * @return void
     */
    public function test_with_flavour_perform_only(): void {
        // Set up.
        global $PAGE;
        $original_flavour = get_config('totara_flavour', 'currentflavour');
        $query = '';
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php', ['query' => $query]);
        $PAGE->set_url($pageurl);
        set_config('currentflavour', 'flavour_learn_perform', 'totara_flavour');

        // Operate - create a config_table object ($sql gets set in its constructor), and fetch its $sql property.
        $property = new ReflectionProperty('bi_intellidata\output\tables\config_table', 'sql');
        $property->setAccessible(true);
        $table = new config_table('config_table', $query);
        $sql_obj = $property->getValue($table);

        // Assert - engage should have filters to be excluded.
        $this->assertFalse(strpos($sql_obj->where, 'perform') !== false);
        $this->assertTrue(strpos($sql_obj->where, 'engage') !== false);
        $this->assertTrue(strpos($sql_obj->where, 'ml\_recommender') !== false);

        // Test again with a **query** set.
        $query = 'perform';
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php', ['query' => $query]);
        $PAGE->set_url($pageurl);

        // Operate - create a config_table object ($sql gets set in its constructor), and fetch its $sql property.
        $property = new ReflectionProperty('bi_intellidata\output\tables\config_table', 'sql');
        $property->setAccessible(true);
        $table = new config_table('config_table', $query);
        $sql_obj = $property->getValue($table);

        // Assert - engage should still have filters to be excluded.
        $this->assertFalse(strpos($sql_obj->where, 'perform') !== false);
        $this->assertTrue(strpos($sql_obj->where, 'engage') !== false);

        // Tear down.
        set_config('currentflavour', $original_flavour, 'totara_flavour');
    }

    /**
     * @return void
     */
    public function test_config_file_forceflavour_value_overrides(): void {
        // Set up.
        global $PAGE, $CFG;
        $original_flavour = get_config('totara_flavour', 'currentflavour');
        $query = '';
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php', ['query' => $query]);
        $PAGE->set_url($pageurl);
        set_config('currentflavour', 'flavour_learn_perform', 'totara_flavour');
        $CFG->forceflavour = 'learn_engage';

        // Operate - create a config_table object ($sql gets set in its constructor), and fetch its $sql property.
        $property = new ReflectionProperty('bi_intellidata\output\tables\config_table', 'sql');
        $property->setAccessible(true);
        $table = new config_table('config_table', $query);
        $sql_obj = $property->getValue($table);

        // Assert - perform should have a filter to be excluded. The config.php forceflavour value should override the
        // config_plugins 'currentflavour' setting.
        $this->assertTrue(strpos($sql_obj->where, 'perform') !== false);
        $this->assertFalse(strpos($sql_obj->where, 'engage') !== false);

        // Tear down.
        set_config('currentflavour', $original_flavour, 'totara_flavour');
        unset($CFG->forceflavour);
    }

    public function test_get_product_for_table(): void {
        $product = SettingsHelper::get_product_for_table("engage_answer_option");
        $this->assertEquals($product, "Engage");

        $product = SettingsHelper::get_product_for_table("appraisal_review_data");
        $this->assertEquals($product, "Perform");
    }

    /**
     * @return void
     */
    public function test_search_with_valid_input(): void {
        // Set up.
        global $PAGE;
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php');
        $PAGE->set_url($pageurl);
        $test_params = ['datatype' => 'approval',
            'status' => '',
            'exportenabled' => ''
        ];
        $table = new config_table('config_table', $test_params);
        $table->setup();

        // Operate.
        $table->query_db(5, false);
        $results = $table->rawdata;

        // Assert.
        $this->assertGreaterThan(0, count($results));
    }

    /**
     * @return void
     */
    public function test_search_with_bad_input(): void {
        // Set up.
        global $PAGE;
        $pageurl = new \moodle_url('/integrations/bi/intellidata/config/index.php');
        $PAGE->set_url($pageurl);
        $test_params = ['datatype' => '%approval%',
            'status' => '',
            'exportenabled' => ''
        ];
        $table = new config_table('config_table', $test_params);
        $table->setup();

        // Operate.
        $table->query_db(5, false);
        $results = $table->rawdata;

        // Assert. Because '%' characters are escaped in SQL LIKE statements, we expect the search to not throw an error
        // and return 0 results.
        $this->assertCount(0, $results);
    }
}
