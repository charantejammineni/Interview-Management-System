<?php
include_once("inc/classes/session.php");
include("inc/classes/View.php");
include("inc/classes/Create.php");

$userSession = new Session();
if ($userSession->getSession('login') != true) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: viewCandidate.php");
    exit();
}

$view = new View();
$create = new Create();
$viewReports = $view->viewReport();
$viewCandidates = $view->viewCandidate();
$viewComments = $view->viewReportComment();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Report</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</head>
<body>

<div class="container">
<?php include('nav.php'); ?>
    <div id="signupbox" style="margin-top:10px" class="mainbox col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">View Report</div>
            </div>

            <div class="panel-body">
                <h4>Candidate Details Report</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th>CGPA Till Date</th>
                            <th>Active Backlogs</th>
                            <th>Candidate Department</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($viewCandidates as $viewCandidate): ?>
                            <?php if ($_GET['id'] == $viewCandidate['cand_id']): ?>
                            <tr>
                                <td><?= $viewCandidate['cand_name'] ?></td>
                                <td><?= $viewCandidate['cand_cgpa'] ?></td>
                                <td><?= $viewCandidate['cand_backlogs'] ?></td>
                                <td><?= $viewCandidate['cand_dept'] ?></td>
                            </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>SL</th>
                            <th>Question</th>
                            <th>Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; foreach ($viewReports as $viewReport): ?>
                        <tr>
                            <td><?= ++$i ?></td>
                            <td><?= $viewReport['question'] ?></td>
                            <td><input value="<?= $viewReport['result'] ?>" class="form-control text-center" disabled></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div>
                    <h4>Comments:</h4>
                    <?php foreach ($viewComments as $viewComment): ?>
                        <p><?= $viewComment['comment'] ?></p>
                    <?php endforeach; ?>
                </div>

                <!-- ✅ Back Button -->
                <div class="text-center" style="margin-top: 20px;">
                    <a href="viewCandidate.php" class="btn btn-default">
                        <span class="glyphicon glyphicon-arrow-left"></span> Back to Candidates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
