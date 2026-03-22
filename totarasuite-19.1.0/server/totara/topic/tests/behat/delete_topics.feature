@totara @totara_topic @engage @javascript
Feature: Delete topics
  Background:
    Given the following "topics" exist in "totara_topic" plugin:
      | name    |
      | Topic 1 |
      | Topic 2 |
    And the following "articles" exist in "engage_article" plugin:
      | name      | username | format             | content | access | topics  |
      | Article 1 | admin    | FORMAT_JSON_EDITOR | blah    | PUBLIC | Topic 1 |

  Scenario: Deleting a topic that does not have any usage.
    Given I log in as "admin"
    And I navigate to "Manage tags" node in "Site administration > Appearance"
    And I follow "Default collection"
    Then I should see "Topic 1"
    And I should see "Topic 2"
    And I click on "Delete" "link" in the "Topic 2" "table_row"
    And I press "Yes"
    And I should see "Tag(s) deleted"
    Then I should not see "Topic 2"
    And I should see "Topic 1"

  Scenario: Deleting a topic that does have usage
    Given I log in as "admin"
    And I navigate to "Manage tags" node in "Site administration > Appearance"
    And I follow "Default collection"
    Then I should see "Topic 1"
    And I should see "Topic 2"
    And I click on "Delete" "link" in the "Topic 1" "table_row"
    And I press "Yes"
    And I should see "Tag(s) deleted"
    Then I should not see "Topic 1"
    And I should see "Topic 2"