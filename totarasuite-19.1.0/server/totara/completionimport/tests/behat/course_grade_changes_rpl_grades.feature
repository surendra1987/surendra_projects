@totara @totara_completion_upload @totara_courseprogressbar @javascript @_file_upload
Feature: Test RPL grades show as expected when making changes to the course grade
  In order to check RPL grades are not affected when changing course grades
  As admin
  I need to create a course, set max grade and min grade for a course, upload RPL
  grades and check the grade at time of completion column

  Background:
    Given I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname   | email                |
      | learner1 | Learner   | One        | learner1@example.com |
      | learner2 | Learner   | Two        | learner2@example.com |
      | learner3 | Learner   | Three      | learner3@example.com |
      | learner4 | Learner   | Four       | learner4@example.com |
    And the following job assignments exist:
      | user     | fullname       | manager  |
      | learner1 | jobassignment1 | admin    |
    And the following "courses" exist:
      | fullname | shortname | idnumber |enablecompletion |
      | Course 1 | course1   |   c1     |1                |
    Given the following "standard_report" exist in "totara_reportbuilder" plugin:
      | fullname                                        | shortname                                              | source                |
      | Test course completion report                   | report_test_course_completion_report                   | course_completion     |
      | Test course completion including history report | report_test_course_completion_including_history_report | course_completion_all |
    And I log in as "admin"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name        | quiz                  |
      | Description | Test quiz description |
    When I click on "Course 1" "link"
    And I click on "quiz" "link"
    And I click on "Edit quiz" "button"
    And I set the field "Maximum grade" to "2"
    And I press "Save"
    And I wait "3" seconds
    And I click on "Add" "link" in the "div#region-main" "css_element"
    And I click on "a new question" "link"
    And I click on "True/False" "text"
    And I click on "Add" "button" in the ".chooserdialogue-mod_quiz-questionchooser" "css_element"
    And I set the following fields to these values:
      | Question name | question1    |
      | Question text | questionText |
    And I click on "submitbutton" "button"
    And I click on "Add" "link" in the "div#region-main" "css_element"
    And I click on "a new question" "link"
    And I click on "True/False" "text"
    And I click on "Add" "button" in the ".chooserdialogue-mod_quiz-questionchooser" "css_element"
    And I set the following fields to these values:
      | Question name | question2    |
      | Question text | questionText |
    And I click on "submitbutton" "button"
#    Set activity completion
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Course completion" node in "Course administration"
    And I click on "Condition: Activity completion" "link"
    And I click on "Quiz - quiz" "checkbox"
    And I press "Save changes"
#    Upload user's completion records
    When I navigate to "Upload course records" node in "Site administration > Courses > Upload completion records"
    And I upload "totara/completionimport/tests/behat/fixtures/course_completion_4.csv" file to "CSV file to upload" filemanager
    And I set the following fields to these values:
      | Upload course CSV Grade format | Percentage |
    And I click on "Save" "button" in the ".totara_completionimport__uploadcourse_form" "css_element"
    Then I should see "Course completion file successfully imported."
    And I should see "3 Records imported pending processing"
    And I run the adhoc scheduled tasks "totara_completionimport\task\import_course_completions_task"

  Scenario: RPL grades show as expected when making changes to the course grade
    When I navigate to my "Test course completion including history report" report
    Then the "report_test_course_completion_including_history_report" table should contain the following:
      | Date completed | Grade at time of completion | Is current record |
      | 14 Oct 2023    | 99.0%                       | Yes               |
      | 13 Sep 2022    | 98.0%                       | No                |
      | 13 Aug 2021    | 97.0%                       | No                |

    # Let's change max grade and check again.
    And I am on "Course 1" course homepage with editing mode on
    And I click on "quiz" "link"
    And I navigate to "Edit quiz" in current page administration
    And I set the field "Maximum grade" to "3"
    And I press "Save"
    And I wait "1" seconds
    And I click on "Add" "link" in the "div#region-main" "css_element"
    And I click on "a new question" "link"
    And I click on "True/False" "text"
    And I click on "Add" "button" in the ".chooserdialogue-mod_quiz-questionchooser" "css_element"
    And I set the following fields to these values:
      | Question name | question1    |
      | Question text | questionText |
    And I click on "submitbutton" "button"
    When I navigate to my "Test course completion including history report" report
    Then the "report_test_course_completion_including_history_report" table should contain the following:
      | Date completed | Grade at time of completion | Is current record |
      | 14 Oct 2023    | 99.0%                       | Yes               |
      | 13 Sep 2022    | 98.0%                       | No                |
      | 13 Aug 2021    | 97.0%                       | No                |

  Scenario: Changes for grademax and grademin in the course completion editor for history are reflected correctly
    # Let's change max grade in completion editor and check.
    And I am on "Course 1" course homepage with editing mode on
    And I navigate to "Completion editor" node in "Course administration"
    Then I should see "Learner One"

    # Completion editor criteria and activities tab/list.
    And I click on "Edit course completion" "link" in the "Learner One" "table_row"
    And I switch to "History" tab
    Then I should see "Course completion history"
    And I follow "Edit"

    # Update history.
    When I set the following Totara form fields to these values:
      | Time completed | 2011-02-03 04:56 |
      | Grade          | 99               |
      | Maximum grade  | 300              |
      | Minimum grade  | 0                |
    And I press "Save changes"
    Then I should see "Completion changes have been saved"
    And I should see "February 2011"
    And I should see "99"
    When I switch to "Transactions" tab
    Then I should see "History manually edited"
    And I should see "Grade: 99"
    And I should see "Maximum grade: 300"
    And I should see "Minimum grade: 0"

    When I navigate to my "Test course completion including history report" report
    Then the "report_test_course_completion_including_history_report" table should contain the following:
      | Date completed | Grade at time of completion | Is current record |
      | 14 Oct 2023    | 99.0%                       | Yes               |
      | 13 Aug 2021    | 97.0%                       | No                |
      | 3 Feb 2011     | 33.0%                       | No                |
