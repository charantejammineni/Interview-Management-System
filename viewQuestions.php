<?php
include_once("inc/classes/session.php");
include("inc/classes/View.php");

$userSession = new Session();

// Check if user is logged in
if ($userSession->getSession('login') !== true) {
    header('Location: login.php');
    exit();
}

// Check user role
$role = $userSession->getSession('role');
if (!in_array($role, ['admin', 'interviewer'])) {
    echo "<h3 style='text-align:center; color:red; margin-top:20px;'>Access Denied: You do not have permission to view this page.</h3>";
    exit();
}

// Show flash message once and then unset
$successMessage = $userSession->getSession('success_msg');
if ($successMessage) {
    $userSession->setSession('success_msg', null);
}

$view = new View();
$viewQuestions = $view->viewQuestions();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View All Questions</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</head>
<body>

<div class="container">
    <?php include('nav.php'); ?>

    <?php if ($successMessage): ?>
        <div class="alert alert-success text-center">
            <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <div class="mainbox col-md-12 col-sm-10" style="margin-top: 10px;">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">All Questions</div>
            </div>
            <div class="panel-body">

                <?php if ($role === 'admin'): ?>
                    <div class="text-right" style="margin-bottom: 15px;">
                        <a href="addQuestion.php" class="btn btn-success">➕ Add New Question</a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($viewQuestions)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%; text-align: center;">#</th>
                                <th style="width: 55%;">Question</th>
                                <th style="width: 20%;">Category</th>
                                <th style="width: 20%; text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($viewQuestions as $index => $question): ?>
                                <tr>
                                    <td style="text-align: center;"><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($question['question']) ?></td>
                                    <td><?= ucfirst($question['category']) ?></td>
                                    <td style="text-align: center;">
                                        <?php if ($role === 'admin'): ?>
                                            <a href="editQuestion.php?id=<?= $question['question_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                            <a href="deleteQuestion.php?id=<?= $question['question_id'] ?>"
                                               class="btn btn-danger btn-sm"
                                               onclick="return confirm('Are you sure you want to delete this question?');">
                                                Delete
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">No action</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info text-center">No questions available to display.</div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

</body>
</html>