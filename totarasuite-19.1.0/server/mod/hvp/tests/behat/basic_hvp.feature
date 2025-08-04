@mod @mod_hvp @javascript
Feature: Manually run through the basics for an hvp activity
  In order for the hvp activity to function
  As a course creator
  I need to be able to add/edit/delete the module

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | creator  | User      | One      | one@example.com      |
    And the following "role assigns" exist:
      | user    | role          | contextlevel | reference |
      | creator | coursecreator | System       |           |
    And the following "courses" exist:
      | fullname    | shortname | category | format | enablecompletion |
      | Test Course | tstcrs    | 0        | topics | 1                |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | tstcrs | student |
    And I log in as "admin"

  Scenario: Fetch a basic H5P activity via the editor
    Given I skip the scenario until issue "TL-37943" lands
    And I navigate to "H5P Settings" node in "Site administration > Plugins > Activity modules > H5P"
    And I set the following fields to these values:
      | Use H5P Hub | true |
    And I press "Save changes"
    And I am on "Test Course" course homepage with editing mode on
    And I click on "Add an activity or resource" "link" in the "Topic 1" "section"
    Then I should see "H5P" in the "Add an activity or resource" "dialogue"

    When I click on "H5P" "radio" in the "Add an activity or resource" "dialogue"
    And I click on "Add" "button" in the "Add an activity or resource" "dialogue"
    And I wait until the page is ready
    And I switch to "h5p-selector-iframe" iframe
    And I click on "Get" "button" in the "li#h5p-truefalse" "css_element"
    And I click on "Install" "button"
    And I wait until the page is ready
    And I wait "5" seconds
    Then I should see "True/False Question successfully installed!"

    When I click on "Use" "button"
    And I set the field "Title" to "Behat true or false"
    And I set the field "Question" to "This question is correct?"
    And I switch to the main frame
    And I click on "Save and return to course" "button"
    Then I should see "Behat true or false"

  Scenario: Create a single activity course with an H5P activity
    Given the following "courses" exist:
      | fullname      | shortname | category | format         | activitytype | enablecompletion |
      | Single Course | singlecrs | 0        | singleactivity | hvp          | 1                |
    And the following "course enrolments" exist:
      | user     | course    | role    |
      | student1 | singlecrs | student |
    And I am on "Single Course" course homepage with editing mode on
    And I wait until the page is ready

    # Create an H5P via upload of a fixture file.
    And I upload "mod/hvp/tests/fixtures/true-or-false-6.h5p" file to "H5P File" filemanager
    And I set the following fields to these values:
    | Description           | An uploaded test H5P file                         |
    | Completion tracking   | Show activity as complete when conditions are met |
    | Grade to pass         | 1                                                 |
    | Require grade         | 1                                                 |
    | Require passing grade | 1                                                 |

    And I click on "Save and display" "button"
    Then I should see "An uploaded test H5P file" in the "div#hvpintro" "css_element"
    And I switch to "h5p-iframe-1" iframe
    And I should see "This question is correct?" in the "div#h5p-tfq0" "css_element"
    And I switch to the main frame

    When I navigate to "Course completion" node in "Course administration"
    And I click on "Condition: Activity completion" "link"
    And I click on "H5P - True or False" "checkbox"
    And I press "Save changes"

    # Login as a learner and answer the true/false question.
    When I log out
    And I log in as "student1"
    And I follow "Single Course"
    Then I should see "An uploaded test H5P file" in the "div#hvpintro" "css_element"
    And I switch to "h5p-iframe-1" iframe
    And I should see "This question is correct?" in the "div#h5p-tfq0" "css_element"

    # Answer the H5P question
    When I click on "True" "text" in the "div.h5p-true-false-answers" "css_element"
    And I press "Check"
    Then I should see "You got 1 of 1 points"

    # Check the H5P completion, flows through to activity completion, and course completion.
    When I switch to the main frame
    And I click on "Learn" in the totara menu
    Then the following should exist in the "plan_courses" table:
      | Course Title  | Progress |
      | Single Course | 100%     |

  Scenario: Copy paste H5P activities in a topics course
    Given I am on "Test Course" course homepage with editing mode on
    And I wait until the page is ready

    # Create an H5P via upload of a fixture file.
    When I add a "H5P" to section "1"
    And I upload "mod/hvp/tests/fixtures/true-or-false-6.h5p" file to "H5P File" filemanager
    And I set the following fields to these values:
    | Description          | An uploaded test H5P file                            |
    | Completion tracking  | Learners can manually mark the activity as completed |
    And I click on "Save and display" "button"
    Then I should see "An uploaded test H5P file" in the "div#hvpintro" "css_element"

    # Copy the uploaded h5p activity to the clipboard.
    When I navigate to "Edit settings" node in "H5P"
    And I switch to "h5p-selector-iframe" iframe
    And I click on "Copy" "button" in the "div.h5peditor-copypaste-wrap" "css_element"
    And I switch to the main frame
    And I click on "Save and return to course" "button"

    # Paste the copied h5p activity into a new one.
    When I add a "H5P" to section "2"
    And I switch to "h5p-selector-iframe" iframe
    And I click on "select[name=h5peditor-library]" "css_element" in the "div.h5peditor" "css_element"
    And I click on "True/False Question" "text" in the "select[name=h5peditor-library]" "css_element"
    And I click on "Paste & Replace" "button" in the "div.h5peditor-copypaste-wrap" "css_element"
    And I click on "button.h5p-confirmation-dialog-confirm-button" "css_element" in the "div.h5p-confirmation-dialog-popup" "css_element"
    And I switch to the main frame
    And I set the following fields to these values:
    | Description          | A copy pasted test H5P file                          |
    | Completion tracking  | Learners can manually mark the activity as completed |
    And I click on "Save and display" "button"

    Then I should see "A copy pasted test H5P file" in the "div#hvpintro" "css_element"
    And I switch to "h5p-iframe-2" iframe
    And I should see "This question is correct?" in the "div#h5p-tfq0" "css_element"
    And I switch to the main frame

    When I am on "Test Course" course homepage
    Then I should see "True or False" in the "li#section-1" "css_element"
    And I should see "True or False" in the "li#section-2" "css_element"
    And I should not see "True or False" in the "li#section-3" "css_element"

  Scenario: Admin can see warning when h5p is disabled
    Given the following config values are set as admin:
      | config     | value | plugin  |
      | hub_is_enabled | 0 | mod_hvp |
    And I am on "Test Course" course homepage with editing mode on
    When I add a "H5P" to section "1"
    Then I should see "There are no content types installed. You can enable H5P Hub or upload an H5P file to install new content types."

  Scenario: Course creator can see no permission warning when h5p is disabled
    Given the following config values are set as admin:
      | config     | value | plugin  |
      | hub_is_enabled | 0 | mod_hvp |
    And I log out

    And I log in as "creator"
    And I go to the courses management page
    And I click on "Create new course" "link" in the ".course-listing-actions" "css_element"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Course full name  | Test course |
      | Course short name | TCPW        |
      | Course ID number  | TCPW1       |
    And I press "Save and display"
    And I turn editing mode on
    When I add a "H5P" to section "1"
    Then I should see "There are no content types installed. You do not have permission to install or upload new content types."
    And I log out

    And I log in as "admin"
    And I set the following system permissions of "Course Creator" role:
      | capability                 | permission |
      | mod/hvp:updatelibraries    | Allow      |
    And I log out

    And I log in as "creator"
    And I am on "Test course" course homepage with editing mode on
    When I add a "H5P" to section "1"
    Then I should see "There are no content types installed. You can enable H5P Hub or upload an H5P file to install new content types."

  Scenario: Set and unset "Require passing grade" feature
    Given I am on "Test Course" course homepage with editing mode on
    And I click on "Add an activity or resource" "link" in the "Topic 1" "section"
    And I click on "H5P" "radio" in the "Add an activity or resource" "dialogue"
    And I click on "Add" "button" in the "Add an activity or resource" "dialogue"
    And I wait until the page is ready
    And I upload "mod/hvp/tests/fixtures/true-or-false-6.h5p" file to "H5P File" filemanager
    And I set the following fields to these values:
      | Description           | An uploaded test H5P file                         |
      | Completion tracking   | Show activity as complete when conditions are met |
      | Grade to pass         | 1                                                 |
      | Require grade         | 1                                                 |
      | Require passing grade | 1                                                 |
    And I click on "Save and display" "button"
    And I navigate to "Edit settings" node in "H5P"
    And I set the following fields to these values:
      | Require passing grade | 0 |
    And I click on "Save and display" "button"
    And I navigate to "Edit settings" node in "H5P"
    When I expand all fieldsets
    Then the field "Require passing grade" matches value "0"
