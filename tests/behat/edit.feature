@qtype @qtype_oumatrix @javascript
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

  @javascript
  Scenario: Edit a Matrix question with single response (radio buttons)
    When I am on the "Single matrix for editing" "core_question > edit" page logged in as teacher

    Then "input[id=id_rowanswers_0_a1][checked]" "css_element" should exist
    And "input[id=id_rowanswers_0_a2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_0_a3]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_0_a4]:not([checked])" "css_element" should exist

    And "input[id=id_rowanswers_1_a1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_1_a2][checked]" "css_element" should exist
    And "input[id=id_rowanswers_1_a3]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_1_a4]:not([checked])" "css_element" should exist

    And "input[id=id_rowanswers_2_a1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_2_a2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_2_a3][checked]" "css_element" should exist
    And "input[id=id_rowanswers_2_a4]:not([checked])" "css_element" should exist

    And "input[id=id_rowanswers_3_a1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_3_a2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_3_a3]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswers_3_a4][checked]" "css_element" should exist

    And I set the following fields to these values:
      | Question name | Edited Single matrix name |
    And I press "id_submitbutton"
    And I should see "Edited Single matrix name"

  @javascript
  Scenario: Edit a Matrix question with multiple response (checkboxes)
    When I am on the "Multiple matrix for editing" "core_question > edit" page logged in as teacher

    Then "input[id=id_rowanswersa1_0][checked]" "css_element" should exist
    And "input[id=id_rowanswersa2_0]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa3_0][checked]" "css_element" should exist
    And "input[id=id_rowanswersa4_0]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa5_0]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa6_0][checked]" "css_element" should exist
    And "input[id=id_rowanswersa7_0]:not([checked])" "css_element" should exist

    And "input[id=id_rowanswersa1_1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa2_1][checked]" "css_element" should exist
    And "input[id=id_rowanswersa3_1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa4_1][checked]" "css_element" should exist
    And "input[id=id_rowanswersa5_1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa6_1]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa7_1][checked]" "css_element" should exist

    And "input[id=id_rowanswersa1_2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa2_2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa3_2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa4_2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa5_2][checked]" "css_element" should exist
    And "input[id=id_rowanswersa6_2]:not([checked])" "css_element" should exist
    And "input[id=id_rowanswersa7_2]:not([checked])" "css_element" should exist

    And I set the following fields to these values:
      | Question name | |
    And I press "id_submitbutton"
    And I should see "You must supply a value here."
    And I set the following fields to these values:
      | Question name | Edited Multiple matrix name |
    And I press "id_submitbutton"
    And I should see "Edited Multiple matrix name"

  @javascript
  Scenario: Edit a Matrix question with multiple response containing HTML tags.
    Given I am on the "Multiple matrix for editing" "core_question > edit" page logged in as teacher
    When I set the following fields to these values:
      | id_columnname_0 | <div>Chicken breast</div> |
      | id_rowname_0    | <blink>Proteins</blink>   |
    And I press "id_submitbutton"
    Then I should see "<div> is not allowed. (Only <sub>, <sup>, <i>, <em>, <span> are permitted.)"
    And I should see "<blink> is not allowed. (Only <sub>, <sup>, <i>, <em>, <span> are permitted.)"
    And I set the following fields to these values:
      | id_columnname_0 | <em>Chicken breast<em> |
      | id_rowname_0    | <i>Proteins</i>        |
    And I press "id_submitbutton"
    And I should not see "is not allowed. (Only <sub>, <sup>, <i>, <em>, <span> are permitted.)"
