<?php
include_once("inc/classes/session.php");
include("inc/classes/View.php");
include("inc/classes/Create.php");

$userSession = new Session();

if ($userSession->getSession('login') != true) {
    header('Location: login.php');
    exit();
}

$role = $userSession->getSession('role');

// Only Admin and Interviewer can access this page
if ($role != 'admin' && $role != 'interviewer') {
    echo "<h3 style='text-align:center; color:red; margin-top:20px;'>Access Denied: You do not have permission to view this page.</h3>";
    exit();
}

$view = new View();
$create = new Create();

// Handle Question Edit Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editQuestion'])) {
    $questionId = $_POST['question_id'];
    $questionText = $_POST['question'];
    $category = $_POST['category'];
    $create->editQuestion($questionId, $questionText, $category);
}

$viewQuestions = $view->viewQuestions();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Edit Questions</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
</head>
<body>

<div class="container">
    <?php include('nav.php'); ?>
    <div id="signupbox" style="margin-top:10px" class="mainbox col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading text-center">
                <strong>Edit Questions</strong>
            </div>
            <div class="panel-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 5%;">SL</th>
                            <th style="width: 50%;">Question</th>
                            <th style="width: 20%;">Category</th>
                            <th style="width: 25%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; foreach ($viewQuestions as $viewQuestion): ?>
                        <tr>
                            <form method="post" action="">
                                <input type="hidden" name="question_id" value="<?= $viewQuestion['question_id']; ?>">
                                <td><?= ++$i; ?></td>
                                <td>
                                    <input type="text" name="question" class="form-control"
                                           value="<?= htmlspecialchars($viewQuestion['question']); ?>" required>
                                </td>
                                <td>
                                    <?php if ($viewQuestion['category'] === 'Psychometric'): ?>
                                        <input type="text" class="form-control" value="Psychometric" readonly>
                                        <input type="hidden" name="category" value="Psychometric">
                                    <?php else: ?>
                                        <select name="category" class="form-control" required>
                                            <option value="General" <?= $viewQuestion['category'] === 'General' ? 'selected' : ''; ?>>General</option>
                                            <option value="Communication" <?= $viewQuestion['category'] === 'Communication' ? 'selected' : ''; ?>>Communication</option>
                                            <option value="Technical" <?= $viewQuestion['category'] === 'Technical' ? 'selected' : ''; ?>>Technical</option>
                                            <option value="Behavioral" <?= $viewQuestion['category'] === 'Behavioral' ? 'selected' : ''; ?>>Behavioral</option>
                                        </select>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <input type="submit" name="editQuestion" value="Update" class="btn btn-primary">
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
