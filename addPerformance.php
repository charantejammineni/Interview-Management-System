<?php
include_once("inc/classes/session.php");
include_once("inc/classes/DB.php");

$session = new Session();
if ($session->getSession('login') !== true || $session->getSession('role') !== 'admin') {
    header("Location: login.php");
    exit();
}

$db = new DB();
$message = "";

// Feedback functions
function getPsychometricComment($score) {
    if ($score > 80) return "You have performed Great and just work on the missing parts.";
    elseif ($score > 70) return "You have performed Good and almost there. Practice Psychometric questions more often.";
    elseif ($score > 50) return "You have performed Average and just halfway there. Practice Psychometric questions more often.";
    else return "You have performed Below Average and needs to take some psychometric trainings and Psychometric questions regularly.";
}

function getCommunicationComment($score) {
    if ($score > 80) return "You have performed Great and just work on the missing parts.";
    elseif ($score > 70) return "You have performed Good and almost there. Practice Soft Skills more often.";
    elseif ($score > 50) return "You have performed Average and just halfway there. Practice Soft Skill questions more often.";
    else return "You have performed Below Average and needs to take some Soft Skills trainings and communicate in English, more often.";
}

function getTechnicalComment($score) {
    if ($score > 80) return "You have performed Great and just work on the missing parts.";
    elseif ($score > 70) return "You have performed Good and almost there. Practice Technical/Aptitude Skills more often.";
    elseif ($score > 50) return "You have performed Average and just halfway there. Practice Technical/Aptitude Skills more often.";
    else return "You have performed Below Average and needs to take some Technical/Aptitude Skill trainings and practice, more often.";
}

function getBehavioralComment($score) {
    if ($score > 80) return "You have performed Great and just work on the missing parts.";
    elseif ($score > 70) return "You have performed Good and almost there. Practice Behavioral Skills more often.";
    elseif ($score > 50) return "You have performed Average and just halfway there. Practice Behavioral Skills more often.";
    else return "You have performed Below Average and needs to take some Behavioral Skills trainings and practice, more often.";
}

function getFinalComment($avg) {
    if ($avg > 80) return "You have performed Great and need to brush up the Basic Skills. Keep up the Good work. SPEC is proud of you";
    elseif ($avg > 70) return "You have performed Good and almost there. Practice Technical/Behavioral Skills more often. Utilize the resources available in college as well as connect with your TPO & mentors at SPECANCIENS for further assistance.";
    elseif ($avg > 50) return "You have performed Average and almost halfway there. Practice Technical/Behavioral Skills more often. Utilize the resources available in college as well as connect with your TPO & mentors at SPECANCIENS for further assistance.";
    else return "You have performed Below Average and needs to take some Technical/Behavioral Skills trainings and practice, regularly. Utilize the resources available in college as well as connect with your TPO & mentors at SPECANCIENS for further assistance.";
}

// Get average score (0-10 scale) for a category and convert to percentage
function getCategoryScore($db, $candId, $category) {
    $sql = "SELECT AVG(r.result) as avg_score
            FROM ims_reports r
            JOIN ims_questions q ON r.question_id = q.question_id
            WHERE r.cand_id = ? AND q.category = ?";
    $res = $db->simplequery($sql, [$candId, $category])->fetch();
    return $res && $res['avg_score'] !== null ? floatval($res['avg_score']) * 10 : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cand_id'])) {
    $candId = $_POST['cand_id'];
    $psy_raw = intval($_POST['psychometric']);
    $psy = ($psy_raw / 25) * 100;

    // Get average category scores
    $comm = getCategoryScore($db, $candId, 'Communication');
    $tech = getCategoryScore($db, $candId, 'Technical');
    $behav = getCategoryScore($db, $candId, 'Behavioral');

    // Generate comments
    $commentPsy = getPsychometricComment($psy);
    $commentComm = getCommunicationComment($comm);
    $commentTech = getTechnicalComment($tech);
    $commentBehav = getBehavioralComment($behav);
    $averageScore = ($psy + $comm + $tech + $behav) / 4;
    $finalComment = getFinalComment($averageScore);

    // Insert
    $sql = "INSERT INTO ims_performance_scores 
            (cand_id, psychometric_score, communication_score, technical_score, behavioral_score,
             comment_psy, comment_comm, comment_tech, comment_behav, final_comment)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = [$candId, $psy, $comm, $tech, $behav,
               $commentPsy, $commentComm, $commentTech, $commentBehav, $finalComment];
    $db->simplequery($sql, $params);

    $message = "✅ Performance data added successfully.";
}

$candidates = $db->simplequery("SELECT ims_candidates.cand_id, ims_candidates.cand_name FROM ims_candidates")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Candidate Performance</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 70px; }
    </style>
</head>
<body>
<div class="container">
    <?php include('nav.php'); ?>
    <div class="panel panel-info" style="margin-top: 20px;">
        <div class="panel-heading text-center"><strong>Add Performance Scores</strong></div>
        <div class="panel-body">
            <?php if ($message): ?>
                <div class="alert alert-success text-center"><?= $message ?></div>
            <?php endif; ?>

            <form method="post" action="" onsubmit="return validateScores();">
                <div class="form-group">
                    <label>Candidate Name</label>
                    <input list="candidateNames" id="candNameInput" class="form-control" placeholder="Type candidate name..." required>
                    <datalist id="candidateNames">
                        <?php foreach ($candidates as $cand): ?>
                            <option data-id="<?= $cand['cand_id'] ?>" value="<?= htmlspecialchars($cand['cand_name']) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="cand_id" id="candIdInput" required>
                </div>

                <div class="form-group">
                    <label>Psychometric Score (0–25)</label>
                    <input type="number" name="psychometric" class="form-control" required min="0" max="25">
                </div>

                <div class="alert alert-info">
                    📌 Communication, Technical, and Behavioral scores are auto-calculated from interview evaluations.
                </div>

                <button type="submit" class="btn btn-primary btn-block">Add Performance</button>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById("candNameInput").addEventListener("input", function () {
    const inputVal = this.value.toLowerCase();
    const options = document.querySelectorAll("#candidateNames option");
    let matchedId = null;

    options.forEach(option => {
        if (option.value.toLowerCase() === inputVal) {
            matchedId = option.getAttribute("data-id");
        }
    });

    document.getElementById("candIdInput").value = matchedId || '';
});

function validateScores() {
    const psychometricInput = document.querySelector("input[name='psychometric']");
    const val = parseInt(psychometricInput.value);
    if (val < 0 || val > 25) {
        alert("⚠️ Psychometric score must be between 0 and 25.");
        psychometricInput.focus();
        return false;
    }

    const candId = document.getElementById("candIdInput").value;
    if (!candId) {
        alert("⚠️ Please select a valid candidate from the list.");
        return false;
    }

    return true;
}
</script>
</body>
</html>
