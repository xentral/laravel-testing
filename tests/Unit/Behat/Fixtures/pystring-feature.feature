Feature: Feature with JSON data

  Scenario: Scenario with JSON data
    Given I have JSON data
      """json
      {"key": "value", "number": 42}
      """
    When I parse the JSON
    Then it should be valid