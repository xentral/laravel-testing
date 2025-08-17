Feature: HasBehatFeature trait functionality

  Scenario: Basic trait functionality test
    Given There are 5 testModels
    When I send a GET request to path /api/v1/test-models
    Then the response status code should be 200
    And the response should contain the following properties
      | path | value    |
      | data | ~count~5 |
