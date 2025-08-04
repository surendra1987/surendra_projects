@totara @totara_hierarchy @totara_competency @javascript
Feature: Test TUI competency summary page
  In order to test the competency summary page
  As an admin
  I need to be able to create and view competency summary page

  Background:
    Given I am on a totara site
    And I enable the "competency_assignment" advanced feature
    And the following "competency" frameworks exist:
      | fullname                              | idnumber | description                               |
      | Fight & Reclaim the "Lonely" Mountain | CFW001   | The mountain in the north of Rhovanion... |
    And the following "competency" hierarchy exists:
      | framework | fullname                       | idnumber | description                                        |
      | CFW001    | Find & Kill the "Smaug" dragon | COMP001  | The dragon who invaded the Dwarf kingdom of Erebor |

  Scenario: Test competency framework name and competency name in UTF-8
    Given I log in as "admin"
    And I navigate to "Manage competencies" node in "Site administration > Competencies"

    When I click on "Fight & Reclaim the \"Lonely\" Mountain" "link"
    And I click on "Find & Kill the \"Smaug\" dragon" "link"
    Then I should see "Fight & Reclaim the \"Lonely\" Mountain - Find & Kill the \"Smaug\" dragon"
