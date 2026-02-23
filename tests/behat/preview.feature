@qtype @qtype_oumatrix
Feature: Preview a OUMatrix question
  As a teacher
  In order to check my OUMatrix questions will work for students
  I need to preview them

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
      | questioncategory | qtype    | name                | template       |
      | Test questions   | oumatrix | Single matrix 001   | animals_single |
      | Test questions   | oumatrix | Multiple matrix 001 | food_multiple  |

  @javascript
  Scenario: Preview a Matrix(single) question and submit correct responses.
    Given I am on the "Single matrix 001" "core_question > preview" page logged in as teacher
    And I should see "Animal classification. Please answer the sub questions in all 4 rows."
    And I should see "a. Bee"
    And I should see "b. Salmon"
    And I should see "c. Seagull"
    And I set the field with xpath "//input[@type='radio' and @aria-labelledby='col0 row0']" to "1"
    And I set the field with xpath "//input[@type='radio' and @aria-labelledby='col1 row1']" to "1"
    And I set the field with xpath "//input[@type='radio' and @aria-labelledby='col2 row2']" to "1"
    And I set the field with xpath "//input[@type='radio' and @aria-labelledby='col3 row3']" to "1"
    When I click on "Submit and finish" "button"

    # Sub-question(row) specific feedback.
    Then I should see "Feedback"
    And I should see "Flies and Bees are insects."
    And I should see "Cod, Salmon and Trout are fish."
    And I should see "Gulls and Owls are birds."
    And I should see "Cows, Dogs and Horses are mammals."

    # General feedback.
    And I should see "Well done!"
    And I should see "We are recognising different type of animals."
    And I should see "The correct answers are:"
    And I should see "Bee → Insects"
    And I should see "Salmon → Fish"
    And I should see "Seagull → Birds"
    And I should see "Dog → Mammals"

  @javascript
  Scenario: Preview a Matrix(multiple) question and submit correct responses.
    Given I am on the "Multiple matrix 001" "core_question > preview" page logged in as teacher
    And I should see "Please classify the list of food item as Proteins, Vegetables, Fats."
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col0 row0']" to "1"
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col2 row0']" to "1"
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col5 row0']" to "1"
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col1 row1']" to "1"
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col3 row1']" to "1"
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col6 row1']" to "1"
    And I set the field with xpath "//input[@type='checkbox' and @aria-labelledby='col4 row2']" to "1"
    When I click on "Submit and finish" "button"

    # Sub-question(row) specific feedback.
    Then I should see "Feedback"
    And I should see "Chicken, fish and red meat containing proteins."
    And I should see "Carrot, Asparagus, Potato are vegetables."
    And I should see "Olive oil contains fat."

    # General feedback.
    And I should see "Well done!"
    And I should see "A delicious and healthy meal is a balanced one."
    And I should see "The correct answers are:"
    And I should see "Proteins → Chicken breast, Salmon fillet, Steak"
    And I should see "Vegetables → Carrot, Asparagus, Potato"
    And I should see "Fats → Olive oil"

  @javascript
  Scenario: Column and row headers must support and correctly render subscript, superscript.
    Given I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Matrix" question filling the form with:
      | Question name                      | Matrix-single-001                                  |
      | Question text                      | Please evaluate the following chemical parameters: |
      | id_status                          | Ready                                              |
      | id_defaultmark                     | 1                                                  |
      | id_inputtype                       | single                                             |
      | id_columnname_0                    | pH<sub>acidic</sub>                                |
      | id_columnname_1                    | Temp<sup>C</sup>                                   |
      | id_columnname_2                    | Conductivity<sub>S/cm</sub>                        |
      | id_rowname_0                       | pH<sub>25C</sub>                                   |
      | id_rowanswers_0_a1                 | 1                                                  |
      | id_rowname_1                       | Density (g/cm<sup>3</sup>)                         |
      | id_rowanswers_1_a2                 | 1                                                  |
      | id_rowname_2                       | Solubility (mol/L)<sup>sat</sup>                   |
      | id_rowanswers_2_a3                 | 1                                                  |
      | id_questionnumbering               | abc                                                |
      | For any correct response           | Correct feedback                                   |
      | For any partially correct response | Partially correct feedback.                        |
      | For any incorrect response         | Incorrect feedback.                                |
      | Hint 1                             | First hint                                         |
      | Hint 2                             | Second hint                                        |
    And I should see "Matrix-single-001"
    When I am on the "Matrix-single-001" "core_question > preview" page
    And I should see "Please evaluate the following chemical parameters:"
    Then "#col0 sub" "css_element" should exist
    And "#col1 sup" "css_element" should exist
    And "#col2 sub" "css_element" should exist
    And "#row0 sub" "css_element" should exist
    And "#row1 sup" "css_element" should exist
    And "#row2 sup" "css_element" should exist

  @javascript
  Scenario: Preview a Matrix question with multiple response with distractor rows.
    Given I am on the "Multiple matrix 001" "core_question > edit" page logged in as teacher
    And I set the following fields to these values:
      | Question name     | Multiple matrix 001 updated |
      | id_rowanswersa5_2 | 0                           |
    And I press "id_submitbutton"
    When I am on the "Multiple matrix 001 updated" "core_question > preview" page
    And I click on "Fill in correct responses" "button"
    And I click on "Submit and finish" "button"
    Then I should see "Fats → (None)"
