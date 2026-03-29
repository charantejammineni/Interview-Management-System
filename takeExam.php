<?php
include_once("inc/classes/session.php");
include("inc/classes/View.php");
include("inc/classes/Create.php");
include_once("inc/classes/DB.php");

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
$db = new DB();

// Sanitize candidate ID
$candId = intval($_GET['id']);

$successMessage = "";
$errorMessage = "";

// ✅ Check if report already exists
$check = $db->simplequery("SELECT COUNT(*) as total FROM ims_reports WHERE cand_id = ?", [$candId])->fetch();
if ($check && $check['total'] > 0) {
    echo "<div class='container'><div class='alert alert-warning text-center' style='margin-top:20px;'>
            ⚠️ This candidate has already been evaluated. You cannot submit the report again.
            <br><br>
            <a href='viewReport.php?id=" . htmlspecialchars($candId, ENT_QUOTES, 'UTF-8') . "' class='btn btn-success'>View Report</a>
            <a href='viewCandidate.php' class='btn btn-default'>Back to Candidates</a>
          </div></div>";
    exit();
}

$viewQuestions = $view->viewQuestions();
$viewCandidates = $view->viewCandidate();

// ✅ Fetch psychometric score and latest attempt for this candidate
$psychometric = $db->simplequery("
    SELECT s.psychometric_score, a.id AS attempt_id
    FROM ims_performance_scores s
    LEFT JOIN ims_psychometric_attempts a ON a.student_id = s.cand_id
    WHERE s.cand_id = ?
    ORDER BY a.id DESC LIMIT 1
", [$candId])->fetch();

$psychometricScore = $psychometric['psychometric_score'] ?? null;
$attemptId = $psychometric['attempt_id'] ?? null;

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submitReport'])) {
    ob_start();
    $status = $create->createExam($_POST);
    $output = trim(ob_get_clean());

    if ($status) {
        $successMessage = $output !== '' ? htmlspecialchars($output, ENT_QUOTES, 'UTF-8') : "✅ Evaluation stored successfully.";
    } else {
        $errorMessage = "❌ Failed to store the evaluation. Please try again.";
        if ($output !== '') {
            $errorMessage .= " " . htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Evaluate Candidate</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <style>
        body { background: #f9fbfd; font-family: 'Segoe UI', sans-serif; padding-top: 60px; }
        .panel-info { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .panel-heading { font-weight: bold; font-size: 16px; }
        .table th, .table td { vertical-align: middle !important; }
    </style>
</head>
<body>

<div class="container">
<?php include('nav.php'); ?>

    <div class="mainbox col-md-10 col-md-offset-1 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Evaluate Candidate</div>
            </div>

            <div class="panel-body">

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success text-center"><?= $successMessage ?></div>
                <?php elseif (!empty($errorMessage)): ?>
                    <div class="alert alert-danger text-center"><?= $errorMessage ?></div>
                <?php endif; ?>

                <h4>Candidate Details</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Candidate Name</th>
                            <th>CGPA Till Date</th>
                            <th>Active Backlogs</th>
                            <th>Department</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($viewCandidates as $viewCandidate): ?>
                        <?php if ($candId == $viewCandidate['cand_id']): ?>
                        <tr>
                            <td><?= htmlspecialchars($viewCandidate['cand_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($viewCandidate['cand_cgpa'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($viewCandidate['cand_backlogs'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($viewCandidate['cand_dept'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- ✅ Psychometric Score Section -->
                <div class="text-center" style="margin:25px 0;">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Psychometric Score</strong>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($psychometricScore) && $psychometricScore > 0): ?>
                                <p><strong>Score:</strong> <?= htmlspecialchars($psychometricScore) ?></p>
                                <?php if (!empty($attemptId)): ?>
                                    <a href="psychometric_report.php?attempt_id=<?= urlencode($attemptId); ?>" 
                                       class="btn btn-primary">
                                       View Detailed Report
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <button class="btn btn-default" disabled>No Psychometric Data Found</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <!-- ✅ End Psychometric Score Section -->

                <div class="panel-body">
                    <form method="post" onsubmit="return validateIndividualMarks();">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Question</th>
                                    <th>Category</th>
                                    <th>Marks (0–10)</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php $i = 0; foreach ($viewQuestions as $viewQuestion): ?>
                                <tr>
                                    <td><?= ++$i ?></td>
                                    <td>
                                        <?= htmlspecialchars($viewQuestion['question'], ENT_QUOTES, 'UTF-8') ?>
                                        <input type="hidden" name="questionId<?= $i ?>" value="<?= htmlspecialchars($viewQuestion['question_id'], ENT_QUOTES, 'UTF-8') ?>">
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($viewQuestion['category'], ENT_QUOTES, 'UTF-8') ?>
                                        <input type="hidden" name="category<?= $i ?>" value="<?= htmlspecialchars($viewQuestion['category'], ENT_QUOTES, 'UTF-8') ?>">
                                    </td>
                                    <td>
                                        <input type="number" step="1" min="0" max="20" name="result<?= $i ?>" class="form-control" required>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Comment:</strong></td>
                                    <td><textarea name="comment" rows="2" class="form-control"></textarea></td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-center">
                            <input type="hidden" name="totalQuestions" value="<?= $i ?>">
                            <input type="submit" name="submitReport" value="Submit Evaluation" class="btn btn-primary">
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function validateIndividualMarks() {
    const total = parseInt(document.querySelector('[name=totalQuestions]').value);

    for (let i = 1; i <= total; i++) {
        const markInput = document.querySelector(`[name=result${i}]`);
        const mark = parseFloat(markInput.value);
        if (isNaN(mark) || mark < 0 || mark > 20) {  // changed from 10 to 20
            alert(`⚠️ Invalid marks for question ${i}. Marks must be between 0 and 20.`);
            markInput.focus();
            return false;
        }
    }
    return true;
}
</script>

</body>
</html>
