<?php
include_once("DB.php");

class Delete {
    private $db;

    public function __construct() {
        $this->db = new DB();
    }

    // ✅ Delete question and associated reports
    public function deleteQuestion($questionId) {
        $questionId = intval($questionId);

        // Delete reports linked to this question
        $this->db->simplequery("DELETE FROM ims_reports WHERE question_id = ?", [$questionId]);

        // Delete the question itself
        $this->db->simplequery("DELETE FROM ims_questions WHERE question_id = ?", [$questionId]);
    }

    // ✅ Delete candidate and related data
    public function deleteCandidate($candId) {
        $candId = intval($candId);

        // Delete related reports
        $this->db->simplequery("DELETE FROM ims_reports WHERE cand_id = ?", [$candId]);

        // Delete related comments
        $this->db->simplequery("DELETE FROM ims_comments WHERE cand_id = ?", [$candId]);

        // Delete candidate record
        $this->db->simplequery("DELETE FROM ims_candidates WHERE cand_id = ?", [$candId]);
    }
}
