<?php
include("inc/classes/Delete.php");
include_once("inc/classes/session.php");

$session = new Session();
if ($session->getSession('login') !== true || $session->getSession('role') !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $questionId = intval($_GET['id']);
    $delete = new Delete();
    $delete->deleteQuestion($questionId);
    $session->setSession('success_msg', 'Question deleted successfully.');
} else {
    $session->setSession('success_msg', 'Invalid question ID.');
}

header("Location: viewQuestions.php");
exit();
