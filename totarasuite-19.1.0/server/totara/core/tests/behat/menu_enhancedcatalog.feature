@totara @totara_core @totara_core_menu
Feature: Test menu correctly highlights the course catalog page when enhanced catalog is disabled
  In order to understand the course catalog page I am currently viewing
  As a user
  I want to see the correct course catalogue page highlighted in the Totara menu

  @javascript @category_catalog
  Scenario: Enhanced catalog menu links should not be highlighted when it is disabled and viewing course index page
    Given I am on a totara site
    And I log in as "admin"
    And I navigate to "Shared services settings" node in "Site administration > System information > Configure features"
    And I set the field "Catalogue default view" to "moodle"
    And I press "Save changes"
    And I navigate to "Main menu" node in "Site administration > Navigation"
    And I press "Add new menu item"
    And I set the following Totara form fields to these values:
      | Menu title | Enhanced catalog |
      | Menu url address | /totara/coursecatalog/courses.php |
    And I press "Add"
    Then I should see "Enhanced catalog" in the totara menu
    When I follow "Enhanced catalog"
    When I click on "Find learning > Courses" in the totara menu
    Then I should see "Enhanced catalog" in the totara menu
