<?php
include_once("inc/classes/session.php");
include("inc/classes/Create.php");

$userSession = new Session();

// Check login
if (!$userSession->getSession('login')) {
    header('Location: login.php');
    exit();
}

// Check user role
$role = $userSession->getSession('role');
if (!in_array($role, ['admin', 'interviewer'])) {
    echo "<h3 style='text-align:center; color:red; margin-top:20px;'>Access Denied: You do not have permission to view this page.</h3>";
    exit();
}

$create = new Create();
$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addQuestion'])) {
    $questionText = trim($_POST['question']);
    $category = $_POST['category'] ?? '';

    if (!empty($questionText) && !empty($category)) {
        $addStatus = $create->createQuestion([
            'question' => $questionText,
            'category' => $category
        ]);

        if ($addStatus) {
            $userSession->setSession('success_msg', '✅ Question added successfully.');
            header("Location: viewQuestions.php");
            exit();
        } else {
            $message = '<div class="alert alert-danger text-center">❌ Failed to add the question. Please try again.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning text-center">⚠️ Both question and category fields are required.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Add a Question</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
</head>
<body>

<div class="container">
    <?php include('nav.php'); ?>

    <?php if (!empty($message)): ?>
        <div style="width: 60%; margin: 20px auto;">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <div id="signupbox" class="mainbox col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2" style="margin-top: 20px;">
        <div class="panel panel-info">
            <div class="panel-heading text-center">
                <div class="panel-title"><strong>Add New Question</strong></div>
            </div>
            <div class="panel-body">
                <form method="post" action="" class="form-horizontal">
                    <div class="form-group">
                        <label for="question" class="col-md-4 control-label">Question</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" id="question" name="question" placeholder="Enter your question" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="category" class="col-md-4 control-label">Category</label>
                        <div class="col-md-8">
                            <select name="category" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                <option value="Communication">Communication</option>
                                <option value="Technical">Technical</option>
                                <option value="Behavioral">Behavioral</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group text-center" style="margin-top: 20px;">
                        <div class="col-md-8 col-md-offset-4">
                            <input type="submit" name="addQuestion" value="Add New Question" class="btn btn-primary">
                            <a href="viewQuestions.php" class="btn btn-default">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
