<?php
/**
 * This file is part of Totara Perform
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Matthias Bonk <matthias.bonk@totara.com>
 * @package core_my
 */

use Behat\Mink\Exception\ExpectationException;
use core\date_format;
use core\entity\user;
use core\orm\query\builder;
use core\webapi\formatter\field\date_field_formatter;
use hierarchy_goal\entity\company_goal;
use hierarchy_goal\entity\goal_item_history;
use hierarchy_goal\entity\personal_goal;
use perform_goal\entity\goal as perform_goal_entity;
use perform_goal\model\goal as perform_goal_model;
use mod_perform\models\activity\subject_instance as subject_instance_model;
use mod_perform\models\response\participant_section as participant_section_model;
use mod_perform\testing\generator as perform_generator;
use totara_hierarchy\entity\competency;
use mod_perform\entity\activity\activity;
use mod_perform\entity\activity\participant_instance;
use mod_perform\entity\activity\subject_instance;
use mod_perform\entity\activity\track_user_assignment;
use totara_hierarchy\testing\generator as hierarchy_generator;
use totara_competency\entity\competency_achievement;

class behat_core_my extends behat_base {

    /**
     * Check the goals overview header.
     *
     * @Then /^I should see "([^"]*)" in the ([^" ]*) overview header$/
     */
    public function i_should_see_in_the_goals_overview_header(string $expected_number, string $group): void {
        behat_hooks::set_step_readonly(true);
        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_number,
                $this->get_group_css_class($group) . " .tui-myPerformOverviewSection__header",
                "css_element"
            ]
        );
    }

    /**
     * Check the goals overview total count.
     *
     * @Then /^I should see "([^"]*)" in the ([^" ]*) overview total count$/
     */
    public function i_should_see_in_the_goals_overview_count(int $expected_number, string $group): void {
        behat_hooks::set_step_readonly(true);
        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_number,
                ".tui-overviewCount__itemsContent-count",
                "css_element"
            ]
        );
    }
    
    /**
     * Check the goals overview due soon count.
     *
     * @Then /^I should see "([^"]*)" in the ([^" ]*) overview due soon count$/
     */
    public function i_should_see_in_the_goals_due_soon_count(int $expected_number, string $group): void {
        behat_hooks::set_step_readonly(true);
        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_number,
                $this->get_group_css_class($group) . " .tui-overviewCount__dueContent-count",
                "css_element"
            ]
        );
    }

    /**
     * Check that an overview due soon count does not exist.
     *
     * @Then /^I should not see the ([^" ]*) overview due soon count$/
     */
    public function i_should_not_see_the_goals_due_soon_count(string $group): void {
        behat_hooks::set_step_readonly(true);
        $this->execute(
            'behat_general::should_not_exist',
            [
                $this->get_group_css_class($group) . " .tui-overviewCount__dueContent-count",
                "css_element"
            ]
        );
    }

    /**
     * Check the goals overview section content
     *
     * @Then /^I (should|should not) see "([^"]*)" in the "([^"]*)" ([^" ]*) overview section$/
     */
    public function i_should_see_in_the_goals_overview_section(
        string $should_or_not,
        string $expected_string,
        string $section_name,
        string $group
    ): void {
        behat_hooks::set_step_readonly(true);

        $assertion_method = $should_or_not === 'should'
            ? 'assert_element_contains_text'
            : 'assert_element_not_contains_text';

        $this->execute(
            'behat_general::' . $assertion_method,
            [
                $expected_string,
                $this->get_section_css_selector($group, $section_name),
                "css_element"
            ]
        );
    }

    /**
     * Check the goals overview section content
     *
     * @Then /^I should see "([^"]*)" in row "([^"]*)" of the "([^"]*)" ([^" ]*) overview section$/
     */
    public function i_should_see_in_row_of_the_goals_overview_section(
        string $expected_string,
        string $row_number,
        string $section_name,
        string $group
    ): void {
        behat_hooks::set_step_readonly(true);

        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_string,
                $this->get_section_css_selector($group, $section_name, $row_number),
                "css_element"
            ]
        );
    }

    /**
     * Check that a goals overview section does/does not exist.
     *
     * @Then /^I (should|should not) see the "([^"]*)" ([^" ]*) overview section$/
     */
    public function i_should_see_the_goals_overview_section(string $should_or_not, string $section_name, string $group): void {
        behat_hooks::set_step_readonly(true);

        $assertion_method = $should_or_not === 'should'
            ? 'should_exist'
            : 'should_not_exist';

        $this->execute(
            'behat_general::' . $assertion_method,
            [
                $this->get_section_css_selector($group, $section_name),
                "css_element"
            ]
        );
    }

    /**
     * Check the goals overview section header count
     *
     * @Then /^I should see "([^"]*)" in the "([^"]*)" ([^" ]*) overview section header count$/
     */
    public function i_should_see_in_the_goals_overview_section_header_count(string $expected_string, string $section_name, string $group): void {
        behat_hooks::set_step_readonly(true);

        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_string,
                $this->get_section_css_selector($group, $section_name) . ' .tui-overviewStatusTable__header-count',
                "css_element"
            ]
        );
    }

    /**
     * Check various types of date for a goal in the overview section
     *
     * To make the tests solid, we don't want to check for any absolute date strings coming from the Behat Gherkin.
     * E.g. goals will disappear from the overview when they haven't been touched in 2 years. Instead, we look up the
     * actual date in the DB for the given goal/user combination and check that it's displayed in the front end.
     * Works for both company and personal goals.
     *
     * @Then /^I should see the "([^"]*)" date for goal "([^"]*)" for user "([^"]*)" in row "([^"]*)" of the "([^"]*)" goals overview (section|modal)$/
     */
    public function i_should_see_the_assignment_date_for_goal(
        string $date_type,
        string $goal_name,
        string $user_name,
        string $row_number,
        string $section_name,
        string $page_type
    ): void {
        behat_hooks::set_step_readonly(true);

        [$date_label, $goal_data_key] = $this->get_goal_date_label_and_key($date_type);

        $goal_data = $this->find_goal_data_by_name_and_user_name($goal_name, $user_name);

        $date_formatter = new date_field_formatter(date_format::FORMAT_DATELONG, context_system::instance());
        $expected_string = $date_label . " " . $date_formatter->format($goal_data[$goal_data_key]);

        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_string,
                $this->get_css_selector($page_type, 'goals', $section_name, $row_number),
                "css_element"
            ]
        );
    }

    /**
     * Check various types of date for an activity in the overview section
     *
     * To make the tests solid, we don't want to check for any absolute date strings coming from the Behat Gherkin.
     * E.g. activities will disappear from the overview when they haven't been touched in 2 years. Instead, we look up the
     * actual date in the DB for the given activity/user combination and check that it's displayed in the front end.
     *
     * @Then /^I should see the "([^"]*)" date for activity "([^"]*)" for user "([^"]*)" in row "([^"]*)" of the "([^"]*)" activities overview (section|modal)$/
     */
    public function i_should_see_the_assignment_date_for_activity(
        string $date_type,
        string $activity_name,
        string $user_name,
        string $row_number,
        string $section_name,
        string $page_type
    ): void {
        behat_hooks::set_step_readonly(true);

        [$date_label, $activity_data_key] = $this->get_activity_date_label_and_key($date_type);

        $activity_data = $this->find_activity_data_by_name_and_user_name($activity_name, $user_name);
        $date_formatter = new date_field_formatter(date_format::FORMAT_DATELONG, context_system::instance());
        $expected_string = $date_label . " " . $date_formatter->format($activity_data[$activity_data_key]);

        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_string,
                $this->get_css_selector($page_type, 'activities', $section_name, $row_number),
                "css_element"
            ]
        );
    }

    /**
     * Check the goals graph legend.
     *
     * @Then /^I should see "([^"]*)" in the ([^"]*) overview graph legend$/
     */
    public function i_should_see_in_the_overview_graph_legend(string $expected_string, string $group): void {
        behat_hooks::set_step_readonly(true);

        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_string,
                ".tui-overview" . ucfirst($group) . "Section .chartjs-render-monitor",
                "css_element",
                false
            ]
        );
    }

    /**
     * Check a due soon or overdue icon.
     *
     * @When /^I click on updated string in row "([^"]*)" of the "([^"]*)" ([^" ]*) overview section$/
     */
    public function i_click_on_updated_string_in_row_of_the_goals_overview_section(string $row_number, string $section_name, string $group): void {
        $this->execute(
            'behat_general::i_click_on',
            [
                $this->get_section_css_selector($group, $section_name, $row_number) . " .tui-myPerformOverviewItemUpdated",
                "css_element"
            ]
        );
    }

    /**
     * Check a due soon or overdue icon in section.
     *
     * @Then /^I (should|should not) see the "([^"]*)" icon in row "([^"]*)" of the "([^"]*)" ([^" ]*) overview section$/
     */
    public function i_should_see_due_soon_icon_in_the_overview_section(
        string $should_or_not,
        string $icon,
        string $row_number,
        string $section_name,
        string $group
    ): void {
        behat_hooks::set_step_readonly(true);

        $assertion_method = $should_or_not === 'should'
            ? 'should_exist_in_the'
            : 'should_not_exist_in_the';

        switch ($icon) {
            case "due soon":
                $selector = '.tui-myPerformOverviewItemDueDate__icon--dueSoon';
                break;
            case "overdue":
                $selector = '.tui-myPerformOverviewItemDueDate__icon--overdue';
                break;
            default:
                throw new ExpectationException('Unknown icon name ' . $icon, $this->getSession());
        }

        $this->execute(
            'behat_general::' . $assertion_method,
            [
                $selector,
                'css_element',
                $this->get_section_css_selector($group, $section_name, $row_number),
                'css_element'
            ]
        );
    }

    /**
     * Set a goal to a given status and current_value.
     * @Given /^the goal "([^"]*)" for user "([^"]*)" was set to status "([^"]*)" and current value "([^"]*)"$/
     */
    public function the_goal_for_user_was_set_to_status_and_current_value(string $goal_name, string $user_name, string $status,
        string $current_value): void {

        $user_id = user::repository()
            ->where('username', $user_name)
            ->one(true)
            ->id;
        $goal_entity = perform_goal_entity::repository()
            ->where('user_id', $user_id)
            ->where('name', $goal_name)
            ->get()
            ->first();

        $goal_model = perform_goal_model::load_by_id($goal_entity->id);
        $goal_model->update_progress($status, $current_value);
    }

    /**
     * Progress an activity for a user.
     *
     * @Given /^the activity "([^"]*)" for user "([^"]*)" was progressed at date "([^"]*)"$/
     */
    public function the_activity_for_user_was_progressed_at_date(
        string $activity_name,
        string $user_name,
        string $backdate
    ): void {
        $activity_data = $this->find_activity_data_by_name_and_user_name($activity_name, $user_name);
        $participant_section_model = $activity_data['participant_section_model'];
        $state = $participant_section_model->get_progress_state();
        $state->on_participant_access();
        perform_generator::instance()->backdate_participant_section_updated_time(
            $participant_section_model,
            strtotime($backdate)
        );
    }
    /**
     * Complete an activity for a user.
     *
     * @Given /^the activity "([^"]*)" for user "([^"]*)" was completed at date "([^"]*)"$/
     */
    public function the_activity_for_user_was_completed_at_date(
        string $activity_name,
        string $user_name,
        string $backdate
    ): void {
        $activity_data = $this->find_activity_data_by_name_and_user_name($activity_name, $user_name);
        $subject_instance = subject_instance_model::load_by_id($activity_data['subject_instance_id']);
        perform_generator::set_subject_instance_completed_at(
            $subject_instance,
            strtotime($backdate)
        );
    }

    /**
     * Check various types of date for a competency in the overview section
     *
     * To make the tests solid, we don't want to check for any absolute date strings coming from the Behat Gherkin.
     * E.g. competencies will disappear from the overview when they haven't been touched in 2 years. Instead, we look up the
     * actual date in the DB for the given competency/user combination and check that it's displayed in the front end.
     *
     * @Then /^I should see the "([^"]*)" date for competency "([^"]*)" in row "([^"]*)" of the "([^"]*)" ([^" ]*) overview (section|modal)$/
     */
    public function i_should_see_the_assignment_date_for_competency(
        string $date_type,
        string $competency_name,
        string $row_number,
        string $section_name,
        string $group,
        string $page_type
    ): void {
        behat_hooks::set_step_readonly(true);

        $date_label = null;
        switch ($date_type) {
            case 'updated':
                $date_label = 'Updated';
                break;
            case 'achieved':
                $date_label = 'Achieved';
                break;
            case 'assigned':
                $date_label = 'Assigned';
                break;
        }

        if (!$date_label) {
            throw new ExpectationException('Unknown date type ' . $date_type, $this->getSession());
        }

        $competency_last_update = $this->find_competency_last_update_date($competency_name);
        $date_formatter = new date_field_formatter(date_format::FORMAT_DATELONG, context_system::instance());
        $expected_string = $date_label . " " . $date_formatter->format($competency_last_update);

        $this->execute(
            'behat_general::assert_element_contains_text',
            [
                $expected_string,
                $this->get_css_selector($page_type, $group, $section_name, $row_number),
                "css_element"
            ]
        );
    }

    /**
     * Check the "view all" modal content
     *
     * @param string $expected_string the string which should match
     * @param string $row_number row number which we look for
     * @param string $group The type of what we look for
     * @throws Exception
     * @Then /^I (should|should not) see "([^"]*)" in row "([^"]*)" of the "([^"]*)" view all modal$/
     */
    public function i_should_see_in_row_of_the_view_all_modal(
        string $should_or_should_not,
        string $expected_string,
        string $row_number,
        string $group
    ): void {
        $contextapi = $should_or_should_not === "should"
            ? 'behat_general::assert_element_contains_text'
            : 'behat_general::assert_element_not_contains_text';

        behat_hooks::set_step_readonly(true);

        $this->execute(
            $contextapi,
            [
                $expected_string,
                $this->get_modal_css_selector($group, $row_number),
                'css_element'
            ]
        );
    }

    /**
     * Click the description button to see the detail
     *
     * @Given /^I click on description in row "([^"]*)" of the "([^"]*)" view all modal$/
     * @param string $row_number row number which we look for
     * @param string $group The type of what we look for
     * @throws Exception
     */
    public function i_click_description_in_row_of_the_view_all_modal(
        string $row_number,
        string $group
    ):void {
        behat_hooks::set_step_readonly(true);

        $css_selector = $this->get_modal_css_selector($group, $row_number)
            . " .tui-overviewStatusTable__item-description"
            . " button";

        $this->execute(
            'behat_general::i_click_on',
            [
                $css_selector,
                'css_element'
            ]
        );
    }

    /**
     * Click the header count to see the view all modal
     *
     * @Given /^I click on "([^"]*)" header count for ([^"]*) overview group$/
     * @param string $status row number which we look for
     * @param string $group The type of what we look for
     * @throws Exception
     */
    public function i_click_on_header_count_for_overview_group(
        string $status,
        string $group
    ):void {
        behat_hooks::set_step_readonly(true);

        switch ($status) {
            case 'not started':
                $formated_status = 'notStarted';
                break;
            case 'progressed':
                $formated_status = 'progressed';
                break;
            case 'not progressed':
                $formated_status = 'notProgressed';
                break;
            case 'achieved':
                $formated_status = 'achieved';
                break;
            case 'completed':
                $formated_status = 'completed';
                break;
            default:
                return ;
        }

        $css_selector = '.tui-overview' . ucfirst($group) . 'Section__content-' . $formated_status . ' .tui-overviewStatusTable__header-countButton';

        $this->execute(
            'behat_general::i_click_on',
            [
                $css_selector,
                'css_element'
            ]
        );
    }

    /**
     * Check a due soon or overdue icon in modal.
     *
     * @Then /^I (should|should not) see the "([^"]*)" icon in row "([^"]*)" of the ([^" ]*) overview modal/
     */
    public function i_should_see_due_soon_icon_in_the_overview_modal(
        string $should_or_not,
        string $icon,
        string $row_number,
        string $group
    ): void {
        behat_hooks::set_step_readonly(true);

        $assertion_method = $should_or_not === 'should'
            ? 'should_exist_in_the'
            : 'should_not_exist_in_the';

        switch ($icon) {
            case "due soon":
                $selector = '.tui-myPerformOverviewItemDueDate__icon--dueSoon';
                break;
            case "overdue":
                $selector = '.tui-myPerformOverviewItemDueDate__icon--overdue';
                break;
            default:
                throw new ExpectationException('Unknown icon name ' . $icon, $this->getSession());
        }

        $this->execute(
            'behat_general::' . $assertion_method,
            [
                $selector,
                'css_element',
                $this->get_modal_css_selector($group, $row_number),
                'css_element'
            ]
        );
    }

    /**
     * Get the css class for a given group (goals/activities/competencies)
     *
     * @param string $group_name
     * @return string
     */
    private function get_group_css_class(string $group_name): string {
        return ".tui-overview" . ucfirst($group_name) . "Section";
    }

    /**
     * For a given date type, get a pair of label/key.
     *
     * @param $date_type
     * @return string[]
     * @throws ExpectationException
     */
    private function get_goal_date_label_and_key($date_type): array {
        switch ($date_type) {
            case 'assignment':
                return ['Assigned', 'assignment_date'];
            case 'due':
                return ['Due', 'target_date'];
            case 'update':
                return ['Updated', 'last_update'];
            case 'achieved':
                return ['Achieved', 'completed_date'];
        }
        throw new ExpectationException('Unknown date type ' . $date_type, $this->getSession());
    }

    /**
     * For a given date type, get a pair of label/key.
     *
     * @param $date_type
     * @return string[]
     * @throws ExpectationException
     */
    private function get_activity_date_label_and_key($date_type): array {
        switch ($date_type) {
            case 'assignment':
                return ['Assigned', 'assignment_date'];
            case 'due':
                return ['Due', 'due_date'];
            case 'update':
                return ['Updated', 'last_update'];
            case 'completed':
                return ['Completed', 'completed_date'];
        }
        throw new ExpectationException('Unknown date type ' . $date_type, $this->getSession());
    }

    /**
     * Get some data from the DB for an activity/user combination that we need in our assertions above.
     *
     * @param string $activity_name
     * @param string $user_name
     * @return array
     * @throws ExpectationException
     */
    private function find_activity_data_by_name_and_user_name(string $activity_name, string $user_name): array {
        $user_id = user::repository()
            ->where('username', $user_name)
            ->one(true)
            ->id;

        /** @var activity $activity */
        $activity = activity::repository()
            ->where('name', $activity_name)
            ->one(true);

        $track_id = $activity->tracks()->order_by('id')->first()->id;

        $track_user_assignment_id = track_user_assignment::repository()
            ->where('track_id', $track_id)
            ->where('subject_user_id', $user_id)
            ->one(true)
            ->id;

        /** @var subject_instance $subject_instance */
        $subject_instance = subject_instance::repository()
            ->where('track_user_assignment_id', $track_user_assignment_id)
            ->where('subject_user_id', $user_id)
            ->one(true);

        /** @var participant_instance $participant_instance */
        $participant_instance = participant_instance::repository()
            ->where('subject_instance_id', $subject_instance->id)
            ->where('participant_id', $user_id)
            ->one();

        $participant_section_model = participant_section_model::load_by_entity(
            $participant_instance->participant_sections->first()
        );

        return [
            'assignment_date' => $participant_instance->created_at,
            'due_date' => $subject_instance->due_date,
            'last_update' => $participant_section_model->progress_updated_at,
            'completed_date' => $subject_instance->completed_at,
            'user_id' => $user_id,
            'participant_section_model' => $participant_section_model,
            'subject_instance_id' => $subject_instance->id,
        ];
    }

    /**
     * Get some data from the DB for a goal/user combination that we need in our assertions above.
     * Works for both personal and company goals, but they must have unique names in the test set up.
     *
     * @param string $goal_name
     * @param string $user_name
     * @return array
     * @throws ExpectationException
     */
    private function find_goal_data_by_name_and_user_name(string $goal_name, string $user_name): array {
        $user_id = user::repository()
            ->where('username', $user_name)
            ->one(true)
            ->id;


        $goals = perform_goal_entity::repository()
            ->where('user_id', $user_id)
            ->where('name', $goal_name)
            ->get();

        if ($goals->count() < 1) {
            throw new ExpectationException('Goal not found', $this->getSession());
        }

        $goal = $goals->first();

        return [
            'assignment_date' => $goal->start_date,
            'target_date' => $goal->target_date,
            'completed_date' => $goal->closed_at,
            'user_id' => $user_id,
            'id' => $goal->id,
            'scope' => '',
            'last_update' => $goal->updated_at
        ];
    }

    /**
     * Create the css selector for section or modal
     *
     * @param string $type
     * @param string $group_name
     * @param string $section_name
     * @param int|null $row_number
     * @return string
     * @throws ExpectationException
     */
    private function get_css_selector(
        string $type,
        string $group_name,
        string $section_name,
        int $row_number = null
    ):string {
        if ($type === 'section') {
            return $this->get_section_css_selector($group_name, $section_name, $row_number);
        } else if ($type === "modal") {
            return $this->get_modal_css_selector($group_name, $row_number);
        } else {
            throw new ExpectationException('Unknown testing page type', $this->getSession());
        }
    }

    /**
     * Create the css selector for different section
     *
     * @param string $group_name
     * @param string $section_name
     * @param int|null $row_number
     * @return string
     */
    private function get_section_css_selector(string $group_name, string $section_name, int $row_number = null): string {
        $section_name = strtolower($section_name);
        $map = [
            'not started' => 'notStarted',
            'not progressed' => 'notProgressed',
        ];
        $section_css_name = $map[$section_name] ?? $section_name;

        $row_css = $row_number ? " .tui-dataTableRow:nth-of-type({$row_number})" : '';

        return '.tui-overview'. ucfirst($group_name) . 'Section__content-' . $section_css_name . $row_css;
    }

    /**
     * Get the css selector of the row in the modal view of the overview page
     *
     * @param string $group
     * @param string $row_number
     * @return string
     */
    private function get_modal_css_selector(string $group, string $row_number):string {
        return ".tui-overviewViewAllModal"
            . " .tui-overview" . ucfirst($group) . "Section__content-viewAll"
            . " .tui-dataTableRow:nth-of-type($row_number)";
    }

    /**
     * Get the last update date from the DB for a competency that we need in our assertions above.
     *
     * @param string $competency_name
     * @return string
     */
    private function find_competency_last_update_date(string $competency_name): string {
        $competency = competency::repository()
            ->where('fullname', $competency_name)
            ->get()
            ->first();

        $achievement = competency_achievement::repository()
            ->where('competency_id', $competency->id)
            ->get()
            ->last();

        return $achievement->last_aggregated;
    }
}
