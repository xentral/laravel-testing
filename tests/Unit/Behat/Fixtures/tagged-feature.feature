Feature: Tagged feature

  @tag1 @tag2
  Scenario: Tagged scenario
    Given I have a tagged test
    When I run the test
    Then the tags should be preserved