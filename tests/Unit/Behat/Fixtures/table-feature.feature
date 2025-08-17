Feature: Feature with table data

  Scenario: Scenario with table
    Given I have users with data
      | Name | Age |
      | John | 30  |
      | Jane | 25  |
    When I process the data
    Then all users should be created