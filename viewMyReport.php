<?php
// =================================================================
// !!! CRITICAL DEBUGGING LINES !!!
// These lines force PHP to display errors, which will replace the 500 error
// with a specific message pointing to the line causing the problem.
error_reporting(E_ALL);
ini_set('display_errors', 1);
// REMOVE these two lines once the error is resolved on a production server.
// =================================================================

require_once("inc/classes/session.php");
require_once("inc/classes/View.php");
require_once("inc/classes/DB.php");

// Feedback functions (Keeping them for completeness)
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

// Function to fetch the psychometric score. (CORRECTED)
function getPsychometricScore($db, $candId) {
    $sql = "SELECT psychometric_score FROM ims_performance_scores WHERE cand_id = ?";
    $res = $db->simplequery($sql, [$candId])->fetch();
    
    $psy_score = $res['psychometric_score'] ?? 0;
    
    return floatval($psy_score); 
}


$userSession = new Session();
if (!$userSession->getSession('login')) {
    header('Location: login.php');
    exit();
}

if ($userSession->getSession('role') !== 'student') {
    header('Location: landing.php');
    exit();
}

$user_id = $userSession->getSession('user_id');
$view = new View();
$db = new DB();

// Get candidate basic details and any pre-stored report data
$data = $view->viewPerformanceByCandId($user_id);

// Check evaluation done - check if any report entries exist for the candidate
$evaluationCheck = $db->simplequery("SELECT 1 FROM ims_reports WHERE cand_id = ? LIMIT 1", [$user_id])->fetch();
$evaluationExists = $evaluationCheck ? true : false;


// If evaluation exists, calculate all scores and comments using the live functions
if ($evaluationExists) {
    // 1. Fetch/Calculate all scores (in percentage)
    $psy_score  = getPsychometricScore($db, $user_id); 
    $comm_score = getCategoryScore($db, $user_id, 'Communication');
    $tech_score = getCategoryScore($db, $user_id, 'Technical');
    $behav_score = getCategoryScore($db, $user_id, 'Behavioral');

    // Update $data array with freshly calculated scores (required for display)
    $data['psychometric_score'] = $psy_score;
    $data['communication_score'] = $comm_score;
    $data['technical_score'] = $tech_score;
    $data['behavioral_score'] = $behav_score;
    
    // 2. Calculate comments and update $data
    $data['comment_psy'] = getPsychometricComment($psy_score);
    $data['comment_comm'] = getCommunicationComment($comm_score);
    $data['comment_tech'] = getTechnicalComment($tech_score);
    $data['comment_behav'] = getBehavioralComment($behav_score);

    // 3. Calculate final average and comment
    $averageScore = ($psy_score + $comm_score + $tech_score + $behav_score) / 4;
    $data['final_comment'] = getFinalComment($averageScore);
}

// Check feedback status
$candData = $db->simplequery("SELECT feedback_submitted FROM ims_candidates WHERE cand_id = ?", [$user_id])->fetch();
$feedbackSubmitted = ($candData && $candData['feedback_submitted'] == 1);

// Fetch final interviewer comment
$commentData = $db->simplequery("SELECT comment FROM ims_comments WHERE cand_id = ?", [$user_id])->fetch();
$interviewerComment = $commentData['comment'] ?? '';

$candidateName = $data['cand_name'] ?? '';
$roll = $data['cand_roll'] ?? '';
$dept = $data['cand_dept'] ?? '';

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$feedbackSubmitted) {
    $email = $_POST['email'] ?? null;
    $roll_number = $_POST['roll_number'] ?? null;
    $q1 = $_POST['q1_org_level'] ?? null;
    $q2 = $_POST['q2_psychometric'] ?? null;
    $q3 = $_POST['q3_mock_interview'] ?? null;
    $q4 = $_POST['q4_report_useful'] ?? null;
    $q5 = $_POST['q5_useful_aspects'] ?? null;
    $q6 = $_POST['q6_improvements'] ?? null;

    try {
        $db->execute(
            "INSERT INTO ims_feedback 
            (cand_id, email, roll_number, q1_org_level, q2_psychometric, q3_mock_interview, q4_report_useful, q5_useful_aspects, q6_improvements, submitted_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
            [$user_id, $email, $roll_number, $q1, $q2, $q3, $q4, $q5, $q6]
        );

        $db->execute(
            "UPDATE ims_candidates SET feedback_submitted = 1 WHERE cand_id = ?",
            [$user_id]
        );
        
        // ** FIX: Set a success message in the session before redirecting. **
        $userSession->setSession('flash_message', 'Thank you! Your feedback has been successfully submitted and your full report is now available.');

        header("Location: viewMyReport.php");
        exit();

    } catch (Exception $e) {
        // Display database submission error (This is shown before redirect)
        echo "<div class='alert alert-danger'>Error saving feedback: " . $e->getMessage() . "</div>";
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Report</title>
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
/* CSS Styles ... (kept for completeness) */
body { padding-top: 60px; background: #f9fbfd; font-family: 'Segoe UI', sans-serif; }
.report-box { background: white; border-radius: 12px; padding: 30px; margin: 25px auto; max-width: 850px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);}
.section-title { font-size: 22px; font-weight: bold; color: #0d47a1; margin-bottom: 15px;}
.score-box { border-radius: 8px; padding: 20px; margin-bottom: 20px; display: flex; align-items: center;}
.score { width: 25%; font-size: 26px; font-weight: bold; text-align: center; margin-right: 20px;}
.comment { flex: 1; font-size: 16px; }
.final-comment { background: #fff9e6; border-left: 6px solid #ffc107; padding: 20px; font-size: 16px; font-weight: bold; margin-bottom: 20px; border-radius: 8px;}
.logo { height: 80px; margin-right: 20px; }
.info-text { font-size: 16px; margin-bottom: 5px; }
.top-info { display: flex; flex-wrap: nowrap; gap: 20px; margin-top: 10px; margin-bottom: 25px;}
.candidate-details { flex: 0 0 35%; min-width: 200px; }
.interviewer-box { flex: 1; background: #e3f2fd; border-left: 6px solid #2196f3; padding: 15px 20px; border-radius: 8px; font-size: 15px; word-wrap: break-word; min-width: 0; }
.header-flex { display: flex; align-items: center; justify-content: flex-start; margin-bottom: 30px;}
.header-title { font-weight: bold; color: #0d47a1; font-size: 28px; margin: 0;}
span.required { color:red; }
</style>
</head>
<body>
<div class="container">
<?php include('nav.php'); ?>

<?php
// ** FIX: Display flash message (conditional feedback) if set in session. **
if ($userSession->getSession('flash_message')) {
    echo '<div class="alert alert-success text-center" style="margin-top:30px;">' . htmlspecialchars($userSession->getSession('flash_message')) . '</div>';
    $userSession->unsetSession('flash_message'); // Remove it so it only shows once
}
?>

<?php if (!$evaluationExists): ?>
<div class="alert alert-warning text-center" style="margin-top:30px;">
    No report found yet. Please wait until your evaluation is complete.
</div>

<?php elseif ($evaluationExists && !$feedbackSubmitted): ?>
<div class="panel panel-info" style="margin-top:30px;">
    <div class="panel-heading"><strong>Feedback Form</strong></div>
    <div class="panel-body">
    <form method="post" action="">
            <input type="hidden" name="cand_id" value="<?= htmlspecialchars($user_id) ?>">

            <label>Email <span class="required">*</span></label>
            <input type="email" name="email" class="form-control" required>

            <label>Roll Number <span class="required">*</span></label>
            <input type="text" name="roll_number" class="form-control" required>

            <label>Level of Organizing of MISA24 <span class="required">*</span></label>
            <select name="q1_org_level" class="form-control" required>
                <option value="">Select</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Satisfactory</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>

            <label>Psychometric Exam was helpful <span class="required">*</span></label>
            <select name="q2_psychometric" class="form-control" required>
                <option value="">Select</option>
                <option value="1">1 - Strongly Disagree</option>
                <option value="2">2 - Disagree</option>
                <option value="3">3 - Neutral</option>
                <option value="4">4 - Agree</option>
                <option value="5">5 - Strongly Agree</option>
            </select>

            <label>Your Mock Interview Experience <span class="required">*</span></label>
            <select name="q3_mock_interview" class="form-control" required>
                <option value="">Select</option>
                <option value="1">1 - Poor</option>
                <option value="2">2 - Fair</option>
                <option value="3">3 - Satisfactory</option>
                <option value="4">4 - Very Good</option>
                <option value="5">5 - Excellent</option>
            </select>

            <label>Personalized Report was helpful <span class="required">*</span></label>
            <select name="q4_report_useful" class="form-control" required>
                <option value="">Select</option>
                <option value="1">1 - Strongly Disagree</option>
                <option value="2">2 - Disagree</option>
                <option value="3">3 - Neutral</option>
                <option value="4">4 - Agree</option>
                <option value="5">5 - Strongly Agree</option>
            </select>

            <label>What aspects of this workshop were most useful or valuable? <span class="required">*</span></label>
            <textarea name="q5_useful_aspects" class="form-control" required></textarea>

            <label>How would you improve this workshop? <span class="required">*</span></label>
            <textarea name="q6_improvements" class="form-control" required></textarea>

            <br>
            <button type="submit" class="btn btn-success">Submit Feedback</button>
        </form>
    </div>
</div>

<?php elseif ($evaluationExists && $feedbackSubmitted): ?>
<div id="reportContent" class="report-box">
    <div class="header-flex">
        <img src="images/MISA-2025.png" alt="SPEC Logo" class="logo">
        <h2 class="header-title">Performance Report</h2>
    </div>

    <div class="top-info">
        <div class="candidate-details">
            <?php if ($candidateName): ?>
                <p class="info-text"><strong>Name:</strong> <?= htmlspecialchars($candidateName) ?></p>
                <p class="info-text"><strong>Roll Number:</strong> <?= htmlspecialchars($roll) ?></p>
                <p class="info-text"><strong>Department:</strong> <?= htmlspecialchars($dept) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($interviewerComment): ?>
            <div class="interviewer-box">
                <strong>Interviewer's Comment:</strong><br>
                <?= nl2br(htmlspecialchars($interviewerComment)) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="section-title">Evaluation Overview</div>

    <div class="score-box" style="background:#f1f8ff; border-left:6px solid #1976d2;">
        <div class="score" style="color:#1565c0;"><?= round($data['psychometric_score'], 2) ?>%</div>
        <div class="comment"><strong>Psychometric:</strong> <?= htmlspecialchars($data['comment_psy']) ?></div>
    </div>

    <div class="score-box" style="background:#e8f5e9; border-left:6px solid #388e3c;">
        <div class="score" style="color:#2e7d32;"><?= round($data['communication_score'], 2) ?>%</div>
        <div class="comment"><strong>Communication:</strong> <?= htmlspecialchars($data['comment_comm']) ?></div>
    </div>

    <div class="score-box" style="background:#fff3e0; border-left:6px solid #f57c00;">
        <div class="score" style="color:#ef6c00;"><?= round($data['technical_score'], 2) ?>%</div>
        <div class="comment"><strong>Technical:</strong> <?= htmlspecialchars($data['comment_tech']) ?></div>
    </div>

    <div class="score-box" style="background:#f3e5f5; border-left:6px solid #8e24aa;">
        <div class="score" style="color:#6a1b9a;"><?= round($data['behavioral_score'], 2) ?>%</div>
        <div class="comment"><strong>Behavioral:</strong> <?= htmlspecialchars($data['comment_behav']) ?></div>
    </div>

    <div class="section-title">Overall Feedback</div>
    <div class="final-comment"><?= htmlspecialchars($data['final_comment']) ?></div>

    <div class="text-center" style="margin-top:20px;">
        <button onclick="downloadPDF()" class="btn btn-success">Download PDF Report</button>
    </div>

    <div class="text-center" style="margin-top:30px;">
        <a href="student_home.php" class="btn btn-default">← Back to Dashboard</a>
    </div>
</div>
<?php endif; ?>
<script>
function downloadPDF() {
    const element = document.getElementById('reportContent');
    const clone = element.cloneNode(true);
    const opt = {
        margin: 0.5,
        filename: `Report_<?= htmlspecialchars($roll) ?>.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2, scrollY: 0, useCORS: true },
        jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    html2pdf().set(opt).from(clone).save();
}
</script>

</body>
</html>