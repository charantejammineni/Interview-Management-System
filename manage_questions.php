<?php
include_once("inc/classes/session.php");
include_once("inc/classes/DB.php");

$session = new Session();
$db = new DB();

if ($session->getSession('role') !== 'admin') {
    exit("Unauthorized Access");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $db->simplequery("DELETE FROM ims_coding_questions WHERE q_id = ?", [$_GET['delete']]);
    header("Location: manage_questions.php");
}

$questions = $db->simplequery("SELECT * FROM ims_coding_questions ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Coding Questions</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <?php include('nav.php'); ?>
    <div class="panel panel-info">
        <div class="panel-heading">
            <h3 class="panel-title">Coding Question Bank 
                <a href="add_question.php" class="btn btn-xs btn-success pull-right">Add New</a>
            </h3>
        </div>
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Difficulty</th>
                        <th>Sample Input</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                    <tr>
                        <td><?= htmlspecialchars($q['title']) ?></td>
                        <td><span class="label label-info"><?= $q['difficulty'] ?></span></td>
                        <td><code><?= htmlspecialchars($q['sample_input']) ?></code></td>
                        <td>
                            <a href="manage_questions.php?delete=<?= $q['q_id'] ?>" 
                               class="btn btn-danger btn-xs" 
                               onclick="return confirm('Delete this question?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>