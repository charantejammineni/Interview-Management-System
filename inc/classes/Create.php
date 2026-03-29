<?php
/**
 * Create class for handling candidate, question, and report creation
 */
include_once("DB.php");
include_once("session.php");

class Create {
    private $db;
    private $msgSession;

    public function __construct() {
        $this->db = new DB();
        $this->msgSession = new Session();
    }

    // ✅ Candidate creation method
    public function createCandidate($data) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['addCand'])) {
            $candName      = $data['candName'];
            $candRoll      = $data['candRoll'];
            $candDept      = $data['candDept'];
            $candCGPA      = $data['candCGPA'];
            $candBacklogs  = $data['candBacklogs'];
            $candAge       = $data['candAge'];

            // Validation
            if (
                empty($candName) || empty($candRoll) || empty($candDept) ||
                $candCGPA === '' || $candBacklogs === '' || $candAge === ''
            ) {
                return '<div class="alert alert-danger"><b>Error!</b> All fields are required.</div>';
            }

            if (!is_numeric($candCGPA) || !is_numeric($candBacklogs) || !is_numeric($candAge)) {
                return '<div class="alert alert-danger"><b>Error!</b> CGPA, Backlogs, and Age must be numbers.</div>';
            }

            // Insert candidate
            $sql = "INSERT INTO ims_candidates (cand_name, cand_roll, cand_dept, cand_cgpa, cand_backlogs, cand_age)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $arr = array($candName, $candRoll, $candDept, $candCGPA, $candBacklogs, $candAge);
            $results = $this->db->simplequery($sql, $arr);

            if ($results) {
                return '<div class="alert alert-success"><b>Success!</b> Candidate added successfully.</div>';
            } else {
                return '<div class="alert alert-danger"><b>Error!</b> Could not add candidate. Please try again.</div>';
            }
        }
    }

    // ✅ Question creation method
    public function createQuestion($data) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['question']) && isset($data['category'])) {
            $question = trim($data['question']);
            $category = trim($data['category']);

            if (empty($question) || empty($category)) {
                return false; // No error message here since UI handles it
            }

            $sql = "INSERT INTO ims_questions (question, category) VALUES (?, ?)";
            $arr = array($question, $category);
            $result = $this->db->simplequery($sql, $arr);

            return $result ? true : false;
        }

        return false;
    }

    // ✅ Question editing method
    public function editQuestion($questionId, $questionText, $category) {
        if (empty($questionText) || empty($category)) {
            return '<div class="alert alert-danger"><b>Error!</b> All fields are required.</div>';
        }

        $sql = "UPDATE ims_questions SET question = ?, category = ? WHERE question_id = ?";
        $arr = array($questionText, $category, $questionId);
        $results = $this->db->simplequery($sql, $arr);

        if ($results) {
            return '<div class="alert alert-success"><b>Success!</b> Question updated successfully.</div>';
        } else {
            return '<div class="alert alert-danger"><b>Error!</b> Could not update question. Please try again.</div>';
        }
    }

    // ✅ Exam report creation method (fixed to return boolean, no echo)
    public function createExam($data) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['submitReport'])) {
            if (!isset($_GET['id'])) {
                return false;
            }

            $candId = intval($_GET['id']);
            $comment = isset($data['comment']) ? $data['comment'] : '';

            $count = isset($data['totalQuestions']) ? intval($data['totalQuestions']) : 0;

            // Insert results
            for ($x = 1; $x <= $count; $x++) {
                if (!empty($data["questionId$x"]) && $data["result$x"] !== '') {
                    $questionId = $data["questionId$x"];
                    $result = $data["result$x"];

                    $sql = "INSERT INTO ims_reports (question_id, cand_id, result) VALUES (?, ?, ?)";
                    $arr = array($questionId, $candId, $result);
                    $this->db->simplequery($sql, $arr);
                }
            }

            // Insert comment
            $sql = "INSERT INTO ims_comments (comment, cand_id) VALUES (?, ?)";
            $arr = array($comment, $candId);
            $this->db->simplequery($sql, $arr);

            return true; // ✅ return instead of echo
        }

        return false;
    }
}
?>
