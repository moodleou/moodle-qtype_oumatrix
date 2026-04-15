@qtype @qtype_oumatrix
Feature: Test importing OUMatrix questions
  As a teacher
  In order to reuse OUMatrix questions
  I need to import them

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

  @javascript @_file_upload
  Scenario: Import OUMatrix single choice question.
    Given I am on the "Course 1" "core_question > course question import" page logged in as teacher
    When I set the field "id_format_xml" to "1"
    And I upload "question/type/oumatrix/tests/fixtures/testquestion_singlechoice.moodle.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. Select the correct option for each of the animals and the family they belong to."
    And I press "Continue"
    And I should see "OUMatrix single choice"

  @javascript @_file_upload
  Scenario: Import OUMatrix multiple response question.
    Given I am on the "Course 1" "core_question > course question import" page logged in as teacher
    When I set the field "id_format_xml" to "1"
    And I upload "question/type/oumatrix/tests/fixtures/testquestion_multipleresponse.moodle.xml" file to "Import" filemanager
    And I press "id_submitbutton"
    Then I should see "Parsing questions from import file."
    And I should see "Importing 1 questions from file"
    And I should see "1. Select the true statements for each of the materials by ticking the boxes in the table."
    And I press "Continue"
    And I should see "OUMatrix multiple choice"
