@core @core_course @totara @javascript
Feature: Paginate through category management
  As a site admin
  Test we can paginate through categories
  Test that there is no pagination if it's not required

  Background:
    Given I am on a totara site
    And the following "users" exist:
        | username | firstname | lastname  | email             |
        | user1    | User 1    | User 1    | user1@example.com |
    And the following "categories" exist:
        |name         | category | idnumber |
        | category0   | 0        |  CAT0    |
        | category1   | 0        |  CAT1    |
        | category2   | 0        |  CAT2    |
        | category3   | 0        |  CAT3    |
        | category4   | 0        |  CAT4    |
        | category5   | 0        |  CAT5    |
        | category6   | 0        |  CAT6    |
        | category7   | 0        |  CAT7    |
        | category8   | 0        |  CAT8    |
        | category9   | 0        |  CAT9    |

    And I log in as "admin"

  Scenario: Test category pagination
    Given I go to the courses management page
    And I should see the "Course categories and courses" management page
    And I should not see "Per page" in the ".category-listing-actions" "css_element"

    # Enable category pagination
    When the following config values are set as admin:
      | totara_19_1_core_course_course_category_pagination  | 1 |
    And I reload the page
    Then I should see "Per page" in the ".category-listing-actions" "css_element"
    # Make the per page cut-off less than the categories available so that pagination is required
    When I click on "Per page: 20" "link" in the ".category-listing-actions" "css_element"
    And I should see "5" in the ".category-listing-actions" "css_element"
    And I should see "10" in the ".category-listing-actions" "css_element"
    And I should see "20" in the ".category-listing-actions" "css_element"
    And I should see "All" in the ".category-listing-actions" "css_element"
    And I click on "5" "link" in the ".category-listing-actions" "css_element"
    And I should see "category0" in the "#category-listing ul.ml" "css_element"
    And I should see "category1" in the "#category-listing ul.ml" "css_element"
    And I should see "category2" in the "#category-listing ul.ml" "css_element"
    And I should see "category3" in the "#category-listing ul.ml" "css_element"
    And I should not see "Prev" in the ".category-listing-pagination" "css_element"
    And I should see "Next" in the ".category-listing-pagination" "css_element"

    # These should be on the next page so not visible
    And I should not see "category4" in the "#category-listing ul.ml" "css_element"
    And I should not see "category5" in the "#category-listing ul.ml" "css_element"
    And I should not see "category6" in the "#category-listing ul.ml" "css_element"
    And I should not see "category7" in the "#category-listing ul.ml" "css_element"
    And I should not see "category8" in the "#category-listing ul.ml" "css_element"
    And I should not see "category9" in the "#category-listing ul.ml" "css_element"

    When I click on "Next" "link" in the ".category-listing-pagination" "css_element"
    Then I should see "Prev" in the ".category-listing-pagination" "css_element"
    And I should see "category4" in the "#category-listing ul.ml" "css_element"
    And I should see "category5" in the "#category-listing ul.ml" "css_element"
    And I should see "category6" in the "#category-listing ul.ml" "css_element"
    And I should see "category7" in the "#category-listing ul.ml" "css_element"
    And I should see "category8" in the "#category-listing ul.ml" "css_element"
    # The third page is still not visible
    And I should not see "category9" in the "#category-listing ul.ml" "css_element"
    # The first page is no longer visible
    And I should not see "category0" in the "#category-listing ul.ml" "css_element"

    # Back on the first page
    And I click on "Prev" "link" in the ".category-listing-pagination" "css_element"
    And I should see "category0" in the "#category-listing ul.ml" "css_element"
    And I should see "category3" in the "#category-listing ul.ml" "css_element"
    # Should be on the next page so not visible again
    And I should not see "category4" in the "#category-listing ul.ml" "css_element"

  Scenario: Test that there is no course pagination if it's not required
    Given the following config values are set as admin:
      | totara_19_1_core_course_course_category_pagination  | 1 |
    And I go to the courses management page
    And I should see the "Course categories and courses" management page
    And I should see "category0" in the "#category-listing ul.ml" "css_element"
    And I should see "category9" in the "#category-listing ul.ml" "css_element"
    And I should not see "Prev" in the ".category-listing" "css_element"
    And I should not see "Next" in the ".category-listing" "css_element"
