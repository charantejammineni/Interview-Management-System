<?php
session_start();
require_once 'inc/classes/DB.php'; // Your DB connection wrapper

if (!isset($_GET['attempt_id'])) {
    die("Invalid Request: Attempt ID is missing.");
}

$attempt_id = intval($_GET['attempt_id']);
$db = new DB();

// Fetch attempt details with candidate info
$sql_attempt = "
    SELECT a.*, c.cand_name, c.cand_roll, c.cand_dept
    FROM ims_psychometric_attempts a
    JOIN ims_candidates c ON a.student_id = c.cand_id
    WHERE a.id = ?
";
$attempt = $db->simplequery($sql_attempt, [$attempt_id])->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    die("Attempt not found.");
}

// Fetch question-wise details
$sql_items = "
    SELECT ai.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_answer
    FROM ims_psychometric_attempt_items ai
    JOIN ims_psychometric_questions q ON ai.question_id = q.id
    WHERE ai.attempt_id = ?
    ORDER BY ai.position ASC
";
$questions = $db->simplequery($sql_items, [$attempt_id])->fetchAll(PDO::FETCH_ASSOC);

// Calculate raw score (out of 25)
$raw_score = $attempt['total_auto_score'] * 25 / 100;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Psychometric Test Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 900px; margin: auto; }
        h2 { text-align: center; }
        .summary { background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .question-card { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 8px; }
        .correct { color: green; font-weight: bold; }
        .wrong { color: red; font-weight: bold; }
        .selected { background: #d0e7ff; padding: 3px 6px; border-radius: 4px; }
        .options { margin-left: 15px; }
        .points { font-style: italic; margin-top: 5px; }
        
       
    body { font-family: Arial, sans-serif; margin: 20px; }
    .container { max-width: 900px; margin: auto; position: relative; }
    h2 { text-align: center; }
    .summary { background: #f5f5f5; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    .question-card { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 8px; }
    .correct { color: green; font-weight: bold; }
    .wrong { color: red; font-weight: bold; }
    .selected { background: #d0e7ff; padding: 3px 6px; border-radius: 4px; }
    .options { margin-left: 15px; }
    .points { font-style: italic; margin-top: 5px; }

    /* Back button styles */
    .back-btn {
        position: absolute;
        top: 0;
        right: 0;
        background: #1de099;
        color: white;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        margin: 10px;
        font-weight: bold;
        transition: background 0.3s;
    }
    .back-btn:hover {
        background: #16b07f;
    }


        
    </style>
</head>
<body>
<div class="container">
    <h2>Psychometric Test Report</h2>
    <a href="javascript:history.back()" class="back-btn">Back</a>


    <div class="summary">
        <p><strong>Candidate:</strong> <?= htmlspecialchars($attempt['cand_name']); ?></p>
        <p><strong>Roll Number:</strong> <?= htmlspecialchars($attempt['cand_roll']); ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($attempt['cand_dept']); ?></p>
        <p><strong>Status:</strong> <?= htmlspecialchars($attempt['status']); ?></p>
        <p><strong>Score:</strong> <?= $attempt['total_auto_score']; ?>%</p>
        <p><strong>Submitted At:</strong> <?= htmlspecialchars($attempt['submitted_at']); ?></p>
    </div>

    <h3>Question Breakdown</h3>
    <?php foreach ($questions as $q): ?>
        <div class="question-card">
            <p><strong>Q<?= $q['position']; ?>:</strong> <?= htmlspecialchars($q['question_text']); ?></p>
            <div class="options">
                <?php
                $options = ['A' => $q['option_a'], 'B' => $q['option_b'], 'C' => $q['option_c'], 'D' => $q['option_d']];
                foreach ($options as $key => $val):
                    $class = '';
                    if ($key == $q['answer']) $class = 'selected'; // Student's answer
                    if ($key == $q['correct_answer']) $class .= ' correct'; // Correct answer
                ?>
                    <p class="<?= $class; ?>">
                        <?= $key ?>. <?= htmlspecialchars($val); ?>
                        <?php if ($key == $q['answer'] && $key != $q['correct_answer']): ?>
                            <span class="wrong">(Your answer)</span>
                        <?php elseif ($key == $q['answer']): ?>
                            <span>(Your answer)</span>
                        <?php endif; ?>
                        <?php if ($key == $q['correct_answer']): ?>
                            <span>(Correct answer)</span>
                        <?php endif; ?>
                    </p>
                <?php endforeach; ?>
            </div>
            <div class="points">
                <strong>Points Scored:</strong> <?= $q['points']; ?>  
                <?= $q['is_correct'] ? "<span class='correct'>(Correct)</span>" : "<span class='wrong'>(Wrong)</span>"; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
</body>
</html>
