<?php
include("inc/classes/Delete.php");
include_once("inc/classes/session.php");

$session = new Session();

if ($session->getSession('login') !== true || $session->getSession('role') !== 'admin') {
    header("Location: login.php");
    exit();
}

$delete = new Delete();

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $delete->deleteCandidate(intval($_GET['id']));
    $session->setSession('success_msg', 'Candidate deleted successfully.');
} else {
    $session->setSession('success_msg', 'Invalid candidate ID.');
}

header("Location: viewCandidate.php");
exit();
