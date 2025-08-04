@totara @totara_topic @engage @totara_engage @javascript
Feature: Managing engage topics across the site

  Scenario: As an admin I can create one or more topics
    Given I log in as "admin"
    And I navigate to "Manage tags" node in "Site administration > Appearance"
    And I follow "Default collection"
    And I press "Add standard tags"
    And I set the field "Enter comma-separated list of new tags" to "Topic1,Topic2,Topic3"
    And I press "Continue"
    And I should see "Standard tag(s) added"
    Then I should see "Topic1"
    And I should see "Topic2"
    And I should see "Topic2"

  Scenario: As an admin I can change the case of an existing topic
    Given the following "topics" exist in "totara_topic" plugin:
      | name    |
      | Topic 1 |
    And I log in as "admin"
    And I navigate to "Manage tags" node in "Site administration > Appearance"
    And I follow "Default collection"
    And I follow "Edit this tag"
    And I set the following fields to these values:
      | Tag name | Kitten |
    And I press "Update"
    Then I should see "Kitten"