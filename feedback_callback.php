<?php
require_once("inc/classes/session.php");
require_once("inc/classes/DB.php");

$userSession = new Session();
if (!$userSession->getSession('login')) {
    header('Location: login.php');
    exit();
}

if ($userSession->getSession('role') !== 'student') {
    header('Location: landing.php');
    exit();
}

$user_id = $userSession->getSession('cand_id');
$db = new DB();

// Ensure form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q1 = $_POST['q1'] ?? null;
    $q2 = $_POST['q2'] ?? null;
    $q3 = $_POST['q3'] ?? null;
    $comments = $_POST['comments'] ?? null;

    try {
        // Insert feedback into new table ims_feedback
        $db->execute(
            "INSERT INTO ims_feedback (cand_id, q1, q2, q3, comments, submitted_at) 
             VALUES (?, ?, ?, ?, ?, NOW())",
            [$user_id, $q1, $q2, $q3, $comments]
        );

        // Update candidate table to mark feedback as submitted
        $db->execute(
            "UPDATE ims_candidates SET feedback_submitted = 1 WHERE cand_id = ?",
            [$user_id]
        );

        // Redirect back to report
        header("Location: viewreport.php");
        exit();

    } catch (Exception $e) {
        echo "Error saving feedback: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
