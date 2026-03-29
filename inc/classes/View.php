<?php
/**
 * View class for viewing data from the database
 */
include("DB.php");
include_once("session.php");

class View {
    private $db;
    private $msgSession;

    function __construct() {
        $this->db = new DB();
        $this->msgSession = new Session();
    }

    // ✅ View all candidates
    function viewCandidate() {
        $sql = "SELECT * FROM ims_candidates";
        $query = $this->db->simplequerywithoutcondition($sql);
        return $query->fetchAll();
    }

    // ✅ Filter candidates based on optional criteria
    function filterCandidate($roll, $dept, $cgpa, $backlogs, $status = '') {
        $sql = "SELECT * FROM ims_candidates WHERE 1=1";
        $arr = array();

        if (!empty($roll)) {
            $sql .= " AND cand_roll LIKE ?";
            $arr[] = "%$roll%";
        }
        if (!empty($dept)) {
            $sql .= " AND cand_dept LIKE ?";
            $arr[] = "%$dept%";
        }
        if (!empty($cgpa)) {
            $sql .= " AND cand_cgpa LIKE ?";
            $arr[] = $cgpa;
        }
        if (!empty($backlogs)) {
            $sql .= " AND cand_backlogs LIKE ?";
            $arr[] = $backlogs;
        }

        $query = $this->db->simplequery($sql, $arr);
        return $query->fetchAll();
    }

    // ✅ View all questions
    function viewQuestions() {
        $sql = "SELECT * FROM ims_questions";
        $query = $this->db->simplequerywithoutcondition($sql);
        return $query->fetchAll();
    }

    // ✅ View specific question for editing
    function viewEditQuestions() {
        $questionId = $_GET['id'];
        $sql = "SELECT * FROM ims_questions WHERE question_id = ?";
        $arr = array($questionId);
        $query = $this->db->simplequery($sql, $arr);
        return $query->fetchAll();
    }

    // ✅ View report for a specific candidate (for admin/interviewer)
    function viewReport() {
        $candId = $_GET['id'];
        $sql = "SELECT q.question, r.result 
                FROM ims_reports r  
                JOIN ims_questions q ON r.question_id = q.question_id 
                WHERE r.cand_id = ?";
        $arr = array($candId);
        $query = $this->db->simplequery($sql, $arr);
        return $query->fetchAll();
    }

    // ✅ View comments for a specific candidate
    function viewReportComment() {
        $candId = $_GET['id'];
        $sql = "SELECT * FROM ims_comments WHERE cand_id = ?";
        $arr = array($candId);
        $query = $this->db->simplequery($sql, $arr);
        return $query->fetchAll();
    }

    // ✅ View report for a student using user_id
    public function viewReportByCandId($userId) {
        $sql = "SELECT c.cand_name, q.question, r.result, com.comment
                FROM ims_user u
                JOIN ims_candidates c ON u.cand_id = c.cand_id
                JOIN ims_reports r ON r.cand_id = c.cand_id
                JOIN ims_questions q ON r.question_id = q.question_id
                LEFT JOIN ims_comments com ON com.cand_id = c.cand_id
                WHERE u.user_id = ?";
        
        $arr = array($userId);
        $query = $this->db->simplequery($sql, $arr);
        return $query->fetchAll();
    }

    // ✅ View latest performance report for a student using cand_id (now includes roll and dept)
    public function viewPerformanceByCandId($cand_id) {
        $sql = "SELECT 
                    c.cand_name,
                    c.cand_roll,
                    c.cand_dept,
                    ps.psychometric_score,
                    ps.communication_score,
                    ps.technical_score,
                    ps.behavioral_score,
                    ps.comment_psy,
                    ps.comment_comm,
                    ps.comment_tech,
                    ps.comment_behav,
                    ps.final_comment
                FROM ims_candidates c
                JOIN ims_performance_scores ps ON c.cand_id = ps.cand_id
                WHERE c.cand_id = ?
                ORDER BY ps.created_at DESC
                LIMIT 1";

        return $this->db->simplequery($sql, [$cand_id])->fetch();
    }
}
?>
