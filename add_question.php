<?php
include_once("inc/classes/session.php");
include_once("inc/classes/DB.php");
$session = new Session();
$db = new DB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $desc = $_POST['description'];
    $diff = $_POST['difficulty'];
    $sample_in = $_POST['sample_input'];
    $sample_out = $_POST['sample_output'];
    
    // We store test cases as a JSON string
    $test_cases = json_encode([
        ['input' => $_POST['test_in'], 'output' => $_POST['test_out']]
    ]);

    $sql = "INSERT INTO ims_coding_questions (title, description, difficulty, sample_input, sample_output, test_cases) VALUES (?, ?, ?, ?, ?, ?)";
    $db->simplequery($sql, [$title, $desc, $diff, $sample_in, $sample_out, $test_cases]);
    header("Location: manage_questions.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Question</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <?php include('nav.php'); ?>
    <div class="well">
        <h3>Create Interview Question</h3>
        <form method="POST">
            <div class="form-group">
                <label>Question Title</label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Problem Description</label>
                <textarea name="description" class="form-control" rows="5" required></textarea>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <label>Difficulty</label>
                    <select name="difficulty" class="form-control">
                        <option>Easy</option><option>Medium</option><option>Hard</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Sample Input</label>
                    <input type="text" name="sample_input" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>Sample Output</label>
                    <input type="text" name="sample_output" class="form-control">
                </div>
            </div>
            <hr>
            <h4>Hidden Test Case (For Evaluation)</h4>
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="test_in" class="form-control" placeholder="Hidden Input">
                </div>
                <div class="col-md-6">
                    <input type="text" name="test_out" class="form-control" placeholder="Expected Hidden Output">
                </div>
            </div>
            <br>
            <button type="submit" class="btn btn-primary">Save Question</button>
        </form>
    </div>
</div>
</body>
</html>