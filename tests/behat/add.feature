@qtype @qtype_oumatrix
Feature: Test creating a Matrix question
  As a teacher
  In order to test my students
  I need to be able to create a Matrix question

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

  @javascript
  Scenario: Create a Matrix question with single choice option
    Given I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    When I add a "Matrix" question filling the form with:
      | Question name                      | Matrix-single-001                          |
      | Question text                      | Choose a correct answer for each row.      |
      | General feedback                   | Salmon(Fish), Chickern(Bird), Lamb(Mammal) |
      | id_status                          | Ready                                      |
      | id_defaultmark                     | 1                                          |
      | id_inputtype                       | single                                     |
      | id_columnname_0                    | Salmon                                     |
      | id_columnname_1                    | Chicken                                    |
      | id_columnname_2                    | Lamb                                       |
      | id_rowname_0                       | Is a fish                                  |
      | id_rowanswers_0_a1                 | 1                                          |
      | id_rowname_1                       | Is a bird                                  |
      | id_rowanswers_1_a2                 | 1                                          |
      | id_rowname_2                       | Is a mammal                                |
      | id_rowanswers_2_a3                 | 1                                          |
      | For any correct response           | Correct feedback                           |
      | For any partially correct response | Partially correct feedback.                |
      | For any incorrect response         | Incorrect feedback.                        |
      | Hint 1                             | First hint                                 |
      | Hint 2                             | Second hint                                |
    Then I should see "Matrix-single-001"

  @javascript
  Scenario: Create a Matrix question with multiple response option
    Given I am on the "Course 1" "core_question > course question bank" page logged in as "teacher"
    And I press "Create a new question ..."
    And I set the field "item_qtype_oumatrix" to "1"
    And I click on "Add" "button" in the "Choose a question type to add" "dialogue"
    And I should see "Adding a Matrix question"
    And I expand all fieldsets
    And I set the following fields to these values:
      | Question name                      | Matrix-multiple-002                    |
      | Question text                      | Choose a correct answers for each row. |
      | General feedback                   | One and Three, Two, Two and Three      |
      | id_status                          | Ready                                  |
      | id_defaultmark                     | 1                                      |
      | id_inputtype                       | multiple                               |
      | id_grademethod                     | partial                                |
    When I press "id_updateform"
    And I set the following fields to these values:
      | id_columnname_0                    | One                         |
      | id_columnname_1                    | Two                         |
      | id_columnname_2                    | Three                       |
      | id_rowname_0                       | Is an odd number            |
      | id_rowanswersa1_0                  | 1                           |
      | id_rowanswersa3_1                  | 1                           |
      | id_rowname_1                       | Is an even number           |
      | id_rowanswersa2_1                  | 1                           |
      | id_rowname_2                       | Is a prime number           |
      | id_rowanswersa2_2                  | 1                           |
      | id_rowanswersa3_2                  | 1                           |
      | For any correct response           | Correct feedback            |
      | For any partially correct response | Partially correct feedback. |
      | For any incorrect response         | Incorrect feedback.         |
      | Hint 1                             | First hint                  |
      | Hint 2                             | Second hint                 |
    And I press "id_submitbutton"
    Then I should see "Matrix-multiple-002"
