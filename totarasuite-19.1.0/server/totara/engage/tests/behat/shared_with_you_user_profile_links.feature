@javascript @totara_engage @totara @engage
Feature: User profile links on shared with you page
  As a user
  I see user profile links depends from totara_engage_allow_view_profiles

  Background:
    Given I am on a totara site
    And I set the site theme to "ventura"

    And the following "users" exist:
      | username | firstname | lastname | email          |
      | user1    | User      | One      | user1@test.com |
      | user2    | User      | Two      | user2@test.com |
      | user3    | User      | Three    | user3@test.com |

    And the following "topics" exist in "totara_topic" plugin:
      | name   |
      | Topic1 |
      | Topic2 |

    And the following "articles" exist in "engage_article" plugin:
      | name           | username | content | access | topics |
      | Test Article 1 | user1    | blah    | PUBLIC | Topic1 |
      | Test Article 2 | user2    | blah    | PUBLIC | Topic1 |
      | Test Article 3 | user3    | blah    | PUBLIC | Topic1 |
    And "engage_article" "Test Article 1" is shared with the following users:
      | sharer | recipient |
      | user1  | user2     |
      | user1  | user3     |
    And "engage_article" "Test Article 2" is shared with the following users:
      | sharer | recipient |
      | user2  | user1     |
      | user2  | user3     |
    And "engage_article" "Test Article 3" is shared with the following users:
      | sharer | recipient |
      | user3  | user1     |
      | user3  | user2     |

    And the following "surveys" exist in "engage_survey" plugin:
      | question       | username | access | topics |
      | Test Survey 1? | user1    | PUBLIC | Topic1 |
      | Test Survey 2? | user2    | PUBLIC | Topic1 |
      | Test Survey 3? | user3    | PUBLIC | Topic1 |
    And "engage_survey" "Test Survey 1?" is shared with the following users:
      | sharer | recipient |
      | user1  | user2     |
      | user1  | user3     |
    And "engage_survey" "Test Survey 2?" is shared with the following users:
      | sharer | recipient |
      | user2  | user1     |
      | user2  | user3     |
    And "engage_survey" "Test Survey 3?" is shared with the following users:
      | sharer | recipient |
      | user3  | user1     |
      | user3  | user2     |

    And the following "playlists" exist in "totara_playlist" plugin:
      | name            | username | access | topics |
      | Test Playlist 1 | user1    | PUBLIC | Topic1 |
      | Test Playlist 2 | user2    | PUBLIC | Topic1 |
      | Test Playlist 3 | user3    | PUBLIC | Topic1 |
    And "totara_playlist" "Test Playlist 1" is shared with the following users:
      | sharer | recipient |
      | user1  | user2     |
      | user1  | user3     |
    And "totara_playlist" "Test Playlist 2" is shared with the following users:
      | sharer | recipient |
      | user2  | user1     |
      | user2  | user3     |
    And "totara_playlist" "Test Playlist 3" is shared with the following users:
      | sharer | recipient |
      | user3  | user1     |
      | user3  | user2     |

  Scenario: User profile links
    # By default the user profile links should be disabled
    Given I log in as "user2"
    And I click on "Your library" in the totara menu

    When I press "Shared with you"
    Then I should see "From User One"
    And I should see "From User Three"
    And "User One" "link" should not exist
    And "User Three" "link" should not exist
    When I am on profile page for user "user1"
    Then I should see "The details of this user are not available to you"
    And I log out

    And I log in as "admin"
    And the following config values are set as admin:
      | totara_engage_allow_view_profiles | 1 |
    And I log out

    And I log in as "user2"
    And I click on "Your library" in the totara menu

    When I press "Shared with you"
    Then I should see "From User One"
    And I should see "From User Three"
    And "User One" "link" should exist
    And "User Three" "link" should exist

    When I follow "User One"
    Then I should see "User details"
    And I log out