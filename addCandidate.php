<?php
include_once("inc/classes/session.php");
include("inc/classes/Create.php");

$userSession = new Session();
if ($userSession->getSession('login') != true) {
    header('Location: login.php');
    exit();
}

$create = new Create();
$addCandidate = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $addCandidate = $create->createCandidate($_POST);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add a Candidate</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <style>
        .form-group {
            margin-bottom: 20px;
        }
        .submit-button-wrapper {
            margin-top: 30px;
        }
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>

<div class="container">
    <?php include('nav.php'); ?>

    <div style="width: 50%; margin: 25px auto;">
        <?php if ($addCandidate) echo $addCandidate; ?>
    </div>

    <div id="signupbox" class="mainbox col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2" style="margin-top:10px">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Add New Candidate</div>
            </div>
            <div class="panel-body">
                <form class="form-horizontal" method="post" action="">

                    <div class="form-group required">
                        <label class="control-label col-md-4">Candidate Name<span class="asteriskField">*</span></label>
                        <div class="col-md-8">
                            <input class="form-control" name="candName" placeholder="Candidate Name" type="text" required>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group required">
                        <label class="control-label col-md-4">CGPA Till Date<span class="asteriskField">*</span></label>
                        <div class="col-md-8">
                            <input class="form-control" name="candCGPA" placeholder="CGPA" type="text" required>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group required">
                        <label class="control-label col-md-4">Department<span class="asteriskField">*</span></label>
                        <div class="col-md-8">
                            <input class="form-control" name="candDept" placeholder="Department" type="text" required>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group required">
                        <label class="control-label col-md-4">Active Backlogs<span class="asteriskField">*</span></label>
                        <div class="col-md-8">
                            <input class="form-control" name="candBacklogs" placeholder="Active Backlogs" type="number" min="0" required>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group required">
                        <label class="control-label col-md-4">Roll Number<span class="asteriskField">*</span></label>
                        <div class="col-md-8">
                            <input class="form-control" name="candRoll" placeholder="Roll Number" type="text" required>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group required">
                        <label class="control-label col-md-4">Age<span class="asteriskField">*</span></label>
                        <div class="col-md-8">
                            <input class="form-control" name="candAge" placeholder="Age" type="number" min="16" required>
                        </div>
                        <div class="clearfix"></div>
                    </div>

                    <div class="form-group submit-button-wrapper">
                        <div class="col-md-offset-4 col-md-8">
                            <input type="submit" name="addCand" value="Add New Candidate" class="btn btn-primary">
                        </div>
                        <div class="clearfix"></div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
