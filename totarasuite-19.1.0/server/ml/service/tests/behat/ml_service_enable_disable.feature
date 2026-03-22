@engage @totara @core_ml @ml_recommender @ml_service @javascript
Feature: Recommender plugin is hidden/disabled when advanced feature is disabled

  Scenario: Disabling the recommender advanced feature will automatically disable the ml_recommender plugin
    Given I log in as "admin"

    # Check that the plugin shows as enabled
    When I enable ml_service
    And I navigate to "Manage machine learning plugins" node in "Site administration > Plugins > Machine learning settings"
    Then I should see "Recommendation engine"
    And "input[name=plugin][value=recommender]" "css_element" should exist

    # Check it shows as disabled
    When I disable ml_service
    And I navigate to "Manage machine learning plugins" node in "Site administration > Plugins > Machine learning settings"
    And the "machine_learning_settings" table should contain the following:
      | Machine learning      | Action |
      | Recommendation engine | Off    |

