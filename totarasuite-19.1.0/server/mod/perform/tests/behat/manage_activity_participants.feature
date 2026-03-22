@totara @perform @mod_perform @javascript @vuejs
Feature: Adding and removing participant to a perform activity section

  Background:
    Given the following "activities" exist in "mod_perform" plugin:
      | activity_name             | create_section | create_track | activity_status |
      | Participant set up test   | true           | true         | Draft           |
      | Multiple section Activity | true           | true         | Draft           |
    And the following "activity sections" exist in "mod_perform" plugin:
      | activity_name             | section_name |
      | Multiple section Activity | Section B    |
    And I log in as "admin"
    And I navigate to the manage perform activities page

  Scenario: Add and remove single participant to a section
    When I click on "Participant set up test" "link"
    Then the "Content" tui tab should be active
    And I should see no perform activity participants

    And I click the add responding participant button
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 1       |
      | Manager           | 0       | 1       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |

    And I select "Subject" in the responding participants popover then click cancel

    # Test canceling out of selections does not retain them (selections are blown away on every open of the popover).
    And I click the add responding participant button
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 1       |
      | Manager           | 0       | 1       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |

    When I select "Subject" in the responding participants popover
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I should see "Subject"

    When I click on "Edit section" "button"
    And I remove "Subject" as a perform activity participant
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast

  Scenario: I can mix and match responding and view-only participant types
    When I click on "Participant set up test" "link"
    Then the "Content" tui tab should be active
    And I should see no perform activity participants

    And I click the add responding participant button
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 1       |
      | Manager           | 0       | 1       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |

    When I select "Manager" in the responding participants popover
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast


    When I close the tui notification toast
    And I click on "Edit section" "button"
    And I click the add view-only participant button
    # Note that despite Manager belonging to the responding group it will is intending to not show checked in the view-only group.
    Then the mod perform view-only participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 1       |
      | Manager           | 0       | 0       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |

    When I select "Subject" in the view-only participants popover
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast

    When I close the tui notification toast
    And I click on "Edit section" "button"
    And I click the add responding participant button

    # Note that despite Manager already being selected in this group it should still not show ticked, this is intentional.
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 0       |
      | Manager           | 0       | 0       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |

    When I reload the page
    Then I should see "Manager"
    And I should see "Subject"

  Scenario: Add and remove all participants to a section
    When I click on "Participant set up test" "link"
    Then the "Content" tui tab should be active
    And I should see no perform activity participants
    And I click the add responding participant button
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 1       |
      | Manager           | 0       | 1       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |
      | Peer              | 0       | 1       |
      | Mentor            | 0       | 1       |
      | Reviewer          | 0       | 1       |

    When I select "Subject, Manager" in the responding participants popover
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I should see "Manager"
    And I should see "Subject"

    When I click on "Edit section" "button"
    And I click the add responding participant button
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 0       |
      | Manager           | 0       | 0       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |
      | Peer              | 0       | 1       |
      | Mentor            | 0       | 1       |
      | Reviewer          | 0       | 1       |

    When I select "Appraiser" in the responding participants popover
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I should see "Manager"
    And I should see "Subject"
    And I should see "Appraiser"

    When I click on "Edit section" "button"
    And I remove "Subject" as a perform activity participant
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it

    When I click on "Edit section" "button"
    When I remove "Manager" as a perform activity participant
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it

    When I click on "Edit section" "button"
    When I remove "Appraiser" as a perform activity participant
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it

    When I click on "Edit section" "button"
    When I click the add responding participant button
    And I select "Subject" in the responding participants popover
    Then I should see the add participant button

  Scenario: Managing participants for activity with one section
    Given I navigate to the manage perform activities page

    When I click on "Participant set up test" "link"
    And I should see "Done"
    And I should see "Cancel"
    And I click the add responding participant button
    Then the mod perform responding participants popover should match:
      | name              | checked | enabled |
      | Subject           | 0       | 1       |
      | Manager           | 0       | 1       |
      | Manager's manager | 0       | 1       |
      | Appraiser         | 0       | 1       |
      | Peer              | 0       | 1       |
      | Mentor            | 0       | 1       |
      | Reviewer          | 0       | 1       |

    When I select "Subject, Manager, Manager's manager, Appraiser, Peer, Mentor, Reviewer, Direct report, External respondent" in the responding participants popover
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I should see "Subject"
    And I should see "Manager"
    And I should see "Manager's manager"
    And I should see "Appraiser"
    And I should see "Peer"
    And I should see "Mentor"
    And I should see "Reviewer"
    And I should see "Direct report"
    And I should see "External respondent"

    # Toggle can view other responses for Subject
    When I click on "Edit section" "button"
    And I click on ".tui-performActivitySectionRelationship:nth-of-type(1) .tui-checkbox__label" "css_element"
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it

    # Toggle can view other responses for Manager
    When I click on "Edit section" "button"
    When I click on ".tui-performActivitySectionRelationship:nth-of-type(2) .tui-checkbox__label" "css_element"
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it

    # Toggle can view other responses for Appraiser
    When I click on "Edit section" "button"
    When I click on ".tui-performActivitySectionRelationship:nth-of-type(4) .tui-checkbox__label" "css_element"
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it

    # Remove a participant
    When I click on "Edit section" "button"
    When I remove "Subject" as a perform activity participant
    And I click on "Done" "button" in the ".tui-formBtnGroup" "css_element" of the "1" activity section
    Then I should see "Activity saved" in the tui success notification toast and close it
    And I should see "Manager"
    And I should see "Manager's manager"
    And I should see "Appraiser"
    And I should see "Peer"
    And I should see "Mentor"
    And I should see "Reviewer"

