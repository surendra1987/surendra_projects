@totara @totara_plan @totara_rol @totara_core_menu @javascript
Feature: Check Record of Learning feature visibility
  In order to control access to RoL
  As an admin
  I need to be able to enable and disable it

  Scenario: Verify Learn appears in the Totara menu if enabled
    Given I am on a totara site
    And I log in as "admin"

    When I navigate to "Main menu" node in "Site administration > Navigation"
    Then I should see "Learn" in the "#totaramenutable" "css_element"

  Scenario: Verify Learn does not appear in the Totara menu if disabled
    Given I am on a totara site
    And I disable the "recordoflearning" advanced feature
    And I log in as "admin"

    When I navigate to "Main menu" node in "Site administration > Navigation"
    Then I should see "Learn" in the "#totaramenutable" "css_element"
    And I should see "Feature disabled" in the "Learn" "table_row"
    And I should not see "Learn" in the totara menu

  Scenario: Verify Record of Learning can still be loaded if teams are disabled
    Given the "mylearning" user profile block exists
    And I am on a totara site
    And the following "users" exist:
      | username | firstname | lastname | email          |
      | user     | test      | user     | test@test.test |
    When I log in as "admin"

    # '3' = 'Disable'
    And I set the following administration settings values:
      | Enable Team | 3 |

    And I navigate to "Manage users" node in "Site administration > Users"
    And I click on "test user" "link"
    And I click on "Record of learning" "link" in the ".block_totara_user_profile_category_mylearning" "css_element"
    Then I should see "There are no records to display"
