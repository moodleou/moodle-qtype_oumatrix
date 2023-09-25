@qtype @qtype_oumatrix
Feature: Test editing an ouMatrix question
  As a teacher
  In order to be able to update my Matrix question
  I need to edit them

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype    | name                        | template       |
      | Test questions   | oumatrix | Single matrix for editing   | animals_single |
      | Test questions   | oumatrix | Multiple matrix for editing | food_multiple  |

  Scenario: Edit a Matrix question with single response (radio buttons)
    When I am on the "Single matrix for editing" "core_question > edit" page logged in as teacher
    And I set the following fields to these values:
      | Question name | Edited Single matrix name |
    And I press "id_submitbutton"
    Then I should see "Edited Single choice name"

  Scenario: Edit a Matrix question with multiple response (checkboxes)
    When I am on the "Multiple matrix for editing" "core_question > edit" page logged in as teacher
    And I set the following fields to these values:
      | Question name | |
    And I press "id_submitbutton"
    And I should see "You must supply a value here."
    And I set the following fields to these values:
      | Question name | Edited Multiple matrix name |
    And I press "id_submitbutton"
    Then I should see "Edited Multiple matrix name"



#  Scenario: Edit a Numerical question with very small answer
#    When I am on the "Numerical for editing" "core_question > edit" page logged in as teacher
#    And I set the following fields to these values:
#      | id_answer_0    | 0.00000123456789 |
#      | id_tolerance_1 | 0.0000123456789  |
#    And I press "id_submitbutton"
#    And I choose "Edit question" action for "Numerical for editing" in the question bank
#    Then the following fields match these values:
#      | id_answer_0    | 0.00000123456789 |
#      | id_tolerance_1 | 0.0000123456789  |
