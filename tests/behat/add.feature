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
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Matrix" question filling the form with:
      | Question name                      | Matrix-001                                  |
      | Question text                      | Choose the answer from the options provided |
      | id_status                          | Ready                                       |
      | id_defaultmak                      | 1                                           |
      | id_inputtype                       | single                                      |
      | Answer mode                        | partial                                     |
      | id_colimnname_0                    | salmon                                      |
      | id_rowanswers_0_a1                 | 1                                           |
      | id_rowanswers_0_a2                 | 0                                           |
      | id_rowanswers_0_a3                 | 0                                           |
      | id_colimnname_1                    | chicken                                     |
      | id_rowanswers_1_a1                 | 0                                           |
      | id_rowanswers_1_a2                 | 1                                           |
      | id_rowanswers_1_a3                 | 0                                           |
      | id_colimnname_2                    | lamb                                        |
      | id_rowname_0                       | Is a fish                                   |
      | id_rowname_1                       | Is a bird                                   |
      | id_rowname_2                       | Is a mammal                                 |
      | id_rowanswers_2_a1                 | 0                                           |
      | id_rowanswers_2_a2                 | 0                                           |
      | id_rowanswers_2_a3                 | 1                                           |
      | For any correct response           | Correct feedback                            |
      | For any partially correct response | Partially correct feedback.                 |
      | For any incorrect response         | Incorrect feedback.                         |
      | Hint 1                             | First hint                                  |
      | Hint 2                             | Second hint                                 |
    And I pause
    Then I should see "Matrix-001"
    And I pause

  @javascript
  Scenario: Create a Matrix question with multiple response option
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Matrix" question filling the form with:
      | Question name                      | Matrix-002                      |
      | Question text                      | You can choose multiple answers |
      | Default mark                       | 1                               |
      | Answer mode                        | Multiple response               |
      | id_answer_0                        | 1                               |
      | id_answer_1                        | 2                               |
      | id_answer_2                        | 1                               |
      | id_startrow_0                      | Copper                          |
      | id_startrow_1                      | Plastic                         |
      | id_startrow_2                      | Gold                            |
      | id_startcolumn_0                   | Is a good electrical conductor  |
      | id_startcolumn_1                   | Is an insulator                 |
      | For any correct response           | Correct feedback                |
      | For any partially correct response | Partially correct feedback.     |
      | For any incorrect response         | Incorrect feedback.             |
      | Hint 1                             | First hint                      |
      | Hint 2                             | Second hint                     |
    Then I should see "Matrix-001"
