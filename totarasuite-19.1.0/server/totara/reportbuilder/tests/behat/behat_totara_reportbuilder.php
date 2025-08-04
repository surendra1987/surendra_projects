<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2015 onwards Totara Learning Solutions LTD
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
 * @author Sam Hemelryk <sam.hemelryk@totaralms.com>
 * @package totara_reportbuilder
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use \Behat\Mink\Exception\ExpectationException;

class behat_totara_reportbuilder extends behat_base {

    /**
     * Adds the given column to the report.
     *
     * This definition requires the user to already be editing a report and to be on the Columns tab.
     *
     * @Given /^I add the "([^"]*)" column to the report$/
     */
    public function i_add_the_column_to_the_report($columnname) {
        \behat_hooks::set_step_readonly(false);
        $this->execute("behat_forms::i_set_the_field_to", array("newcolumns", $this->escape($columnname)));
        $this->execute("behat_forms::press_button", "Save changes");
        $this->execute("behat_general::assert_page_contains_text", "Columns updated");
        $this->execute("behat_general::assert_page_contains_text", $this->escape($columnname));

    }

    /**
     * Deletes the given column from the report.
     *
     * This definition requires the user to already be editing a report and to be on the Columns tab.
     *
     * @Given /^I delete the "([^"]*)" column from the report$/
     */
    public function i_delete_the_column_from_the_report($columnname) {
        \behat_hooks::set_step_readonly(false);
        $columnname_xpath = behat_context_helper::escape($columnname);
        $delstring = behat_context_helper::escape(get_string('delete'));
        $xpath = '//option[contains(., '.$columnname_xpath.') and @selected]/ancestor::tr//a[@title='.$delstring.']';
        $node = $this->find(
            'xpath',
            $xpath,
            new ExpectationException('The given column could not be deleted from within the report builder report. '.$xpath, $this->getSession())
        );
        $node->click();
        $this->getSession()->getDriver()->getCurrentPromptOrAlert()->accept();
    }


    /**
     * Changes a report builder column from one to another.
     *
     * @When /^I change the "([^"]*)" column to "([^"]*)" in the report$/
     */
    public function i_change_the_column_to_in_the_report($original_column, $new_column) {
        \behat_hooks::set_step_readonly(false);
        $column_xpath = behat_context_helper::escape($original_column);
        $xpath = '//select[@class="column_selector"]//option[contains(.,' . $column_xpath . ') and @selected]/ancestor::select';
        $node = $this->find(
            'xpath',
            $xpath,
            new ExpectationException('The column ' . $original_column .  ' could not be found within the report builder report. ' . $xpath, $this->getSession())
        );
        $node->selectOption($new_column);
    }


    /**
     * Sets the aggregation for the given column in the report.
     *
     * This definition requires the user to already be editing a report and to be on the Columns tab.
     *
     * @Given /^I set aggregation for the "([^"]*)" column to "([^"]*)" in the report$/
     */
    public function i_set_aggregation_for_the_column_to_in_the_report($columnname, $aggregation) {
        \behat_hooks::set_step_readonly(false);
        $columnname_xpath = behat_context_helper::escape($columnname);
        $aggregation_xpath = behat_context_helper::escape($aggregation);
        $xpath = '//option[contains(., '.$columnname_xpath.') and @selected]/ancestor::tr//select[contains(@class, \'advanced_selector\')]//option[contains(., '.$aggregation_xpath.')]//ancestor::select';
        $select = $this->find(
            'xpath',
            $xpath,
            new ExpectationException('Aggregation could not be set for the given column within the report builder report. ', $this->getSession())
        );
        $select->selectOption($aggregation);
    }

    /**
     * Sets the rowheader for the given column in the report.
     *
     * This definition requires the user to already be editing a report and to be on the Columns tab.
     *
     * @Given /^I (check|uncheck) the row header option in the  "([^"]*)" column in the report$/
     */
    public function i_check_the_row_header_option_in_the_column_report($action, $columnname) {
        \behat_hooks::set_step_readonly(false);
        $columnname_xpath = behat_context_helper::escape($columnname);
        $xpath = '//option[contains(., '.$columnname_xpath.') and @selected]/ancestor::tr//input[contains(@class, \'column_rowheader_checkbox\')]';
        $checkbox = $this->find(
            'xpath',
            $xpath,
            new ExpectationException('Row header could not be set for the given column within the report builder report. ', $this->getSession())
        );

        if ($action === 'check') {
            $checkbox->check();
        } else {
            $checkbox->uncheck();
        }
    }

    /**
     * Navigates to a given report that the user has created.
     *
     * @Given /^I navigate to my "([^"]*)" report$/
     */
    public function i_navigate_to_my_report($reportname) {
        behat_hooks::set_step_readonly(false);
        global $DB;
        $report = $DB->get_record('report_builder', ['fullname' => $this->escape($reportname)], 'id', MUST_EXIST);
        $url = new moodle_url('/totara/reportbuilder/report.php', ['id' => $report->id]);
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
    }

    /**
     * Confirms the icon with the attribute value does not exist in the report for the given row+column.
     *
     * @Then /^I should not see icon "([^"]*)" in the "([^"]*)" report column for "([^"]*)"$/
     */
    public function i_should_not_see_icon_in_the_report_column_for($icon, $column, $rowcontent) {
        try {
            $this->i_should_see_icon_in_the_report_column_for($icon, $column, $rowcontent);
        } catch (ExpectationException $ex) {
            return true;
        }
        throw new ExpectationException('The given value could be found within the report builder report', $this->getSession());
    }

    /**
     * Confirms the icon with the attribute value exists in the report for the given row+column.
     *
     * @Then /^I should see icon "([^"]*)" in the "([^"]*)" report column for "([^"]*)"$/
     */
    public function i_should_see_icon_in_the_report_column_for($icon, $column, $rowcontent) {
        \behat_hooks::set_step_readonly(true);
        $row_search = behat_context_helper::escape($rowcontent);
        $column_search = behat_context_helper::escape($column);
        $icon_search = behat_context_helper::escape($icon);

        // Build an xpath selector to find an icon within a table > row > cell > span, e.g:
        // "//table[contains(@class, 'reportbuilder-table')]//tr/td[contains(text(),'Geddy Lee') or .//*[contains(text(),'Geddy Lee')]]/ancestor::tr/td[contains(@class, 'user_userlearningicons')]//span[@data-flex-icon='recordoflearning']"

        // Find the table.
        $xpath  = "//table[contains(@class, 'reportbuilder-table')]";
        // Find the row - the text can be either directly in TD or in child element
        $xpath .= "//tr/td[contains(text(), {$row_search}) or .//*[contains(text(), {$row_search})]]/ancestor::tr";
        // Find the column
        $xpath .= "/td[contains(@class, {$column_search})]";
        // Find the icon
        $xpath .= "//span[@data-flex-icon={$icon_search}]";
        echo $xpath;

        $this->find(
            'xpath',
            $xpath,
            new ExpectationException('The given icon could not be found within the report builder report', $this->getSession())
        );
        return true;
    }


    /**
     * Confirms the the given value exists in the report for the given row+column.
     *
     * @Then /^I should see "([^"]*)" in the "([^"]*)" report column for "([^"]*)"$/
     */
    public function i_should_see_in_the_report_column_for($value, $column, $rowcontent) {
        \behat_hooks::set_step_readonly(true);
        $rowsearch = behat_context_helper::escape($rowcontent);
        $valuesearch = behat_context_helper::escape($value);
        // Find the table.
        $xpath  = "//table[contains(concat(' ', normalize-space(@class), ' '), ' reportbuilder-table ')]";
        // Find the row - the text can be either directly in TD or in child element
        $xpath .= "//*[self::td or self::th][contains(text(),{$rowsearch}) or .//*[contains(text(),{$rowsearch})]]//ancestor::tr";
        // Find the column
        $xpath .= "/*[self::td or self::th][contains(concat(' ', normalize-space(@class), ' '), ' {$column} ')]";
        // Find the row
        $xpath .= "/self::*[child::text()[contains(.,{$valuesearch})] or *[child::text()[contains(.,{$valuesearch})]]]";

        $this->find(
            'xpath',
            $xpath,
            new ExpectationException('The given value could not be found within the report builder report', $this->getSession())
        );
        return true;
    }

    /**
     * Confirms the the given value does not exist in the report for the given row+column.
     *
     * @Then /^I should not see "([^"]*)" in the "([^"]*)" report column for "([^"]*)"$/
     */
    public function i_should_not_see_in_the_report_column_for($value, $column, $rowcontent) {
        \behat_hooks::set_step_readonly(true);
        try {
            $this->i_should_see_in_the_report_column_for($value, $column, $rowcontent);
        } catch (ExpectationException $ex) {
            return true;
        }
        throw new ExpectationException('The given value was found within the report builder report', $this->getSession());
    }

    /**
     * Checks if database family used is using one of the specified, else skip. (mysql, postgres, mssql, oracle, etc.)
     *
     * @Given /^this test is skipped if tables cannot be created from select$/
     * @return void.
     * @throws \Moodle\BehatExtension\Exception\SkippedException
     */
    public function skip_test_if_crate_table_from_select_not_supported() {
        global $DB;

        if (!$DB->is_create_table_from_select_supported()) {
            throw new \Moodle\BehatExtension\Exception\SkippedException();
        }
    }
}
