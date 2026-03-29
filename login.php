<?php
include("inc/classes/User.php");
include_once("inc/classes/session.php");

$userSession = new Session();
$user = new User();

// If already logged in, redirect based on role
if ($userSession->getSession('login') === true) {
    $role = $userSession->getSession('role');

    if ($role === 'admin') {
        header('Location: landing.php');
    } elseif ($role === 'interviewer') {
        header('Location: interviewer_home.php');
    } elseif ($role === 'student') {
        header('Location: student_home.php');
    }
    exit();
}

// Handle login form
$loginMessage = $user->userLogin($_POST);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="images/MISA-2025.png">
    <title>Welcome to SPECANCIENS</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="assets/main.js"></script>
    <style>
        body {
            background-color: #f9f9f9;
        }
        .panel-login {
            border-color: #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .panel-login .panel-heading {
            text-align: center;
        }
        .btn-login {
            background-color: #337ab7;
            color: #fff;
        }
    </style>
</head>
<body>

    <!-- Banner -->
    <div class="text-center">
        <img src="images/imsbanner.png" alt="Welcome Banner" class="img-responsive center-block" style="max-width: 1100px; margin-top: 20px;">
    </div>

    <!-- Login Container -->
    <div class="container" style="margin-top: 40px;">
        <div class="row justify-content-center">
            <div class="col-md-6 col-md-offset-3">

                <!-- Login Message -->
                <?php if (isset($loginMessage)) echo $loginMessage; ?>

                <!-- Login Panel -->
                <div class="panel panel-login">
                    <div class="panel-heading">
                        <h3>Login to IMS</h3>
                        <hr>
                    </div>
                    <div class="panel-body">
                        <form id="login-form" method="post" role="form">
                            <div class="form-group">
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="form-group">
                                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                            </div>
                            <div class="form-group text-center">
                                <input type="submit" name="login-submit" id="login-submit" class="btn btn-login btn-block" value="Log In">
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>
