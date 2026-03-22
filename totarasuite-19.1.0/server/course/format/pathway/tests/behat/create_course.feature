@course @format_pathway @mod_certificate @javascript
Feature: Manually create a course using the pathway format
  In order for the pathway format to be used
  As a course creator
  I need to be able to create a course in the pathway format

  Background:
    Given I log in as "admin"
    And I go to the courses management page
    And I click on "Create new course" "link" in the ".course-listing-actions" "css_element"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Format            | Pathway format              |
      | Course full name  | Test course: Pathway format |
      | Course short name | TCPW                        |
      | Course ID number  | TCPW1                       |
      | Course summary    | This is a pathway course    |
    And I press "Save and display"
    And I should see "Test course: Pathway format"

    # Pathway format should not have an announcement forum
    And I should not see "Announcements"

  Scenario: Create pathway course
    Given I click on "Add an activity or resource" "link" in the "[aria-label='Topic 2']" "css_element"
    And I click on "Certificate" "text" in the "#chooseform" "css_element"
    And I click on "Add" "button" in the "#chooseform" "css_element"
    And I set the following fields to these values:
      | Certificate Name | Certificate one |
    And I click on "Save and return to course" "button"
    Then I should see "Certificate one" in the "[aria-label='Topic 2']" "css_element"

    When I add a "Certificate" to section "4" and I fill the form with:
      | Certificate Name | Certificate four |
    Then I should see "Certificate four" in the "[aria-label='Topic 4']" "css_element"
    And I click on "Certificate four" "link"
    And I should see "Certificate one" in the ".tui-format_pathway-activityView__nav" "css_element"
    And I should see "Certificate four" in the ".tui-format_pathway-activityView__nav" "css_element"
    And I should not see "Topic 1" in the ".tui-format_pathway-activityView__nav" "css_element"
    And I should see "Topic 2" in the ".tui-format_pathway-activityView__nav" "css_element"
    And I should not see "Topic 3" in the ".tui-format_pathway-activityView__nav" "css_element"
    And I should see "Topic 4" in the ".tui-format_pathway-activityView__nav" "css_element"

  Scenario: Admin only can the option of 'number of sections' for a pathway course
    Given I am on a totara site
    And I go to the courses management page
    And I follow "Create new course"
    And I follow "Course format"
    When I set the following fields to these values:
      | Format     | Pathway format              |
    Then I should see "Number of sections"
    And I should see "Format"
    And I should not see "Hidden sections"
    And I should not see "Course layout"
    And I should not see "Collapsible sections"
    And I should not see "Include an expand/collapse all link"
    And I should not see "Header colour"
