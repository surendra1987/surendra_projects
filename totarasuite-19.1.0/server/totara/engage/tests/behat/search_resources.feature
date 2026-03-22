@totara @totara_engage @javascript @engage
Feature: Search resources

  Background:
    Given I am on a totara site
    And I set the site theme to "ventura"

    And the following "topics" exist in "totara_topic" plugin:
      | name    |
      | Topic 1 |

    And the following "users" exist:
      | username | firstname | lastname | email             |
      | user_one | User      | One      | user1@example.com |

    And the following "articles" exist in "engage_article" plugin:
      | name           | username | content       | format       | access | topics  |
      | Test Article 1 | user_one | Test Article  | FORMAT_PLAIN | PUBLIC | Topic 1 |
      | Test Article 2 | user_one | Test Article  | FORMAT_PLAIN | PUBLIC | Topic 1 |

  Scenario: Search resources
    Given I log in as "user_one"
    When I click on "Your library" in the totara menu
    Then I should see "Test Article 1" in the ".tui-contributionBaseContent__cards" "css_element"
    And I should see "Test Article 2" in the ".tui-contributionBaseContent__cards" "css_element"
    And I set the field "Search your library" to "search"
    Then I should see "No matching items found." in the ".tui-contributionBaseContent__emptyText" "css_element"
    When I click on "Clear this search term" "button"
    Then I should see "Test Article 1" in the ".tui-contributionBaseContent__cards" "css_element"
    And I should see "Test Article 2" in the ".tui-contributionBaseContent__cards" "css_element"