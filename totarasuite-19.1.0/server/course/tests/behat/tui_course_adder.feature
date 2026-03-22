@totara @core @core_course @javascript @vuejs
Feature: The tui course adder behaves as expected
  As a user I should be able to use the course adder

  Background:
    Given the following "categories" exist:
      | name   | category | idnumber |
      | Cat 1  | 0        | c1       |
      | Cat 1a | c1       | c1a      |
      | Cat 1b | c1       | c1b      |
      | Cat 2  | 0        | c2       |
    And the following "courses" exist:
      | fullname   | shortname | category |
      | Course 1   | c1        | c1       |
      | Course 1a  | c1a       | c1a      |
      | Course 2   | c2        | c2       |
      | Top course | tc        | 0        |
    And I log in as "admin"
    And I navigate to "TUI Samples" node in "Site administration > Development"
    And I set the field "Search" to "CourseAdder"

  Scenario: The search in the tui course adder works as expected
    And I click on "CourseAdder" "link"
    And I click on "Add Courses" "button"
    Then I should see the tui datatable contains:
      | Courses                  |
      | Top course Miscellaneous |
      | Course 1 Cat 1           |
      | Course 1a Cat 1 / Cat 1a |
      | Course 2 Cat 2           |

    When I click on "All" "button"
    And I click on "All" "button" in the ".tui-treeNode__trigger" "css_element"
    And I click on "//button[normalize-space()='Miscellaneous']" "xpath_element"
    Then I should not see "Course 1"
    And I should not see "Course 2"
    Then I should see the tui datatable contains:
      | Courses                  |
      | Top course Miscellaneous |

    Given I click on "Miscellaneous" "button"
    And I click on "//button[normalize-space()='Cat 1']" "xpath_element"
    Then I should see the tui datatable contains:
      | Courses                  |
      | Course 1 Cat 1           |
      | Course 1a Cat 1 / Cat 1a |

    When I set the field "Search" to "1a"
    Then I should see the tui datatable contains:
      | Courses                  |
      | Course 1a Cat 1 / Cat 1a |
    And I should not see "Course 1 Cat 1"

    When I click on "Reset" "button"
    Then I should see the tui datatable contains:
      | Courses                  |
      | Top course Miscellaneous |
      | Course 1 Cat 1           |
      | Course 1a Cat 1 / Cat 1a |
      | Course 2 Cat 2           |

  Scenario: Course adder selected tab works as expected
    And I click on "CourseAdder" "link"
    And I click on "Add Courses" "button"
    And I toggle the selection of row "1" of the tui select table
    And I toggle the selection of row "3" of the tui select table
    And I click on "All" tui "button"
    And I click on "All" "button" in the ".tui-treeNode__trigger" "css_element"
    And I click on "//button[normalize-space()='Miscellaneous']" "xpath_element"
    And I switch to "Selected" tui tab
    Then I should see the tui datatable contains:
      | Courses                  |
      | Top course Miscellaneous |
      | Course 1a Cat 1 / Cat 1a |
