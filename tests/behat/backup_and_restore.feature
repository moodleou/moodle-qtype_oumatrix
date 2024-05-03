@qtype @qtype_oumatrix
Feature: Test duplicating a quiz containing an OU matrix question
  As a teacher
  In order re-use my courses containing an OU matrix questions
  I need to be able to backup and restore them

  Background:
    Given the following config values are set as admin:
      | enableasyncbackup | 0 |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype    | name              | template       |
      | Test questions   | oumatrix | OUMatrix single   | animals_single |
      | Test questions   | oumatrix | OUMatrix multiple | food_multiple |
    And the following "activities" exist:
      | activity   | name      | course | idnumber |
      | quiz       | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | OUMatrix single | 1 |

  @javascript
  Scenario: Backup and restore a course containing an OUMatrix question
    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I am on the "Course 2" "core_question > course question bank" page
    And I choose "Edit question" action for "OUMatrix single" in the question bank
    Then the following fields match these values:
      | Question name     | OUMatrix single                                                       |
      | Question text     | Animal classification. Please answer the sub questions in all 4 rows. |
      | General feedback  | We are recognising different type of animals.                         |
      | Default mark      | 1                                                                     |
      | id_inputtype      | Single choice                                                         |
      | id_shuffleanswers | 0                                                                     |
      | id_columnname_0   | Insects                                                               |
      | id_columnname_1   | Fish                                                                  |
      | id_columnname_2   | Birds                                                                 |
      | id_columnname_3   | Mammals                                                               |
      | id_rowname_0      | Bee                                                                   |
      | id_feedback_0     | Flies and Bees are insects.                                           |
      | id_rowname_1      | Salmon                                                                |
      | id_feedback_1     | Cod, Salmon and Trout are fish.                                       |
      | id_rowname_2      | Seagull                                                               |
      | id_feedback_2     | Gulls and Owls are birds.                                             |
      | id_rowname_3      | Dog                                                                   |
      | id_feedback_3     | Cows, Dogs and Horses are mammals.                                    |
      | id_hint_0         | Hint 1.                                                               |
      | id_hint_1         | Hint 2.                                                               |
