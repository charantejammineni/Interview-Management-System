<?php
  include_once ("inc/classes/session.php");
  include ("inc/classes/View.php");

  $userSession = new Session();
  if ($userSession->getSession('login') != true) {
    header('Location: login.php');
    exit();
  }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <link rel="icon" type="image/png" href="images/MISA-2025.png">
  <title>Welcome to SPECANCIENS</title>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
  <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</head>
<body>

<div class="container">
  <?php include ('nav.php'); ?>
  
  <div id="signupbox" style="margin-top:10px;" class="mainbox col-md-12 col-sm-8">
    <div class="panel panel-info">
      <div class="panel-body">

        <div class="text-center">
          <!-- Correct relative image path -->
          <img src="images/Welcome1.png" alt="SPECANCIENS" class="img-responsive center-block" style="max-width: 100%;">
        </div>

        <div class="text-center" style="margin-bottom: 50px; margin-top: 20px;">
          <a href="viewCandidate.php" class="btn btn-primary">View Candidates</a>
        </div>

      </div>
    </div>
  </div>
</div>

</body>
</html>
