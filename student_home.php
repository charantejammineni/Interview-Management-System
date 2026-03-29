<?php
include("inc/classes/session.php");
$userSession = new Session();

if ($userSession->getSession('login') != true || $userSession->getSession('role') != 'student') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar with Logout -->
<?php include('nav.php'); ?>

<div class="container" style="margin-top: 50px;">
    <h2>Welcome, Student!</h2>
    <p>This is your dashboard. You can view your details here.</p>
</div>

</body>
</html>
