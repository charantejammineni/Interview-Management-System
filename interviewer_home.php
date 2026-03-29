<?php
include("inc/classes/session.php");
$userSession = new Session();

if ($userSession->getSession('login') != true || $userSession->getSession('role') != 'interviewer') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Interviewer Dashboard</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar with Logout -->
<?php include('nav.php'); ?>

<div class="container" style="margin-top: 50px;">
    <h2>Welcome, Interviewer!</h2>
    <p>This is your dashboard. You can evaluate candidates from here.</p>
    <a href="viewCandidate.php" class="btn btn-primary">View Candidates</a>
</div>

</body>
</html>
