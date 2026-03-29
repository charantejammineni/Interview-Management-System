<?php
require_once("inc/classes/session.php");
require_once("inc/classes/View.php");
require_once("vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Session check
$session = new Session();
if ($session->getSession('login') !== true || $session->getSession('role') !== 'student') {
    header('Location: login.php');
    exit();
}

$user_id = $session->getSession('user_id');
$view = new View();
$data = $view->viewPerformanceByCandId($user_id);

$candidateName = $data['cand_name'] ?? '';
$roll = $data['cand_roll'] ?? '';
$dept = $data['cand_dept'] ?? '';

// Dompdf setup
$options = new Options();
$options->set('isRemoteEnabled', true); // Allow images
$dompdf = new Dompdf($options);

// HTML content (identical styling to viewMyReport.php but simplified for PDF)
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 30px;
            font-size: 12px;
            color: #333;
        }
        h2 {
            text-align: center;
            color: #0d47a1;
        }
        .logo {
            height: 60px;
            display: block;
            margin: 0 auto 10px;
        }
        .info {
            text-align: center;
            margin-bottom: 20px;
        }
        .score-box {
            border: 1px solid #ccc;
            border-left: 5px solid #1976d2;
            margin-bottom: 10px;
            padding: 10px;
        }
        .category {
            font-weight: bold;
            color: #0d47a1;
        }
        .percentage {
            float: right;
            font-weight: bold;
        }
        .final-comment {
            margin-top: 20px;
            background: #fdf3d6;
            padding: 15px;
            border-left: 5px solid #ffc107;
        }
    </style>
</head>
<body>
    <img src="images/SA_Logo-removebg.png" class="logo" alt="Logo">
    <h2>Performance Report</h2>

    <div class="info">
        <p><strong>Name:</strong> <?= htmlspecialchars($candidateName) ?></p>
        <p><strong>Roll No:</strong> <?= htmlspecialchars($roll) ?></p>
        <p><strong>Department:</strong> <?= htmlspecialchars($dept) ?></p>
    </div>

    <div class="score-box">
        <span class="category">Psychometric</span>
        <span class="percentage"><?= round($data['psychometric_score'], 2) ?>%</span>
        <div><?= $data['comment_psy'] ?></div>
    </div>

    <div class="score-box">
        <span class="category">Communication</span>
        <span class="percentage"><?= round($data['communication_score'], 2) ?>%</span>
        <div><?= $data['comment_comm'] ?></div>
    </div>

    <div class="score-box">
        <span class="category">Technical</span>
        <span class="percentage"><?= round($data['technical_score'], 2) ?>%</span>
        <div><?= $data['comment_tech'] ?></div>
    </div>

    <div class="score-box">
        <span class="category">Behavioral</span>
        <span class="percentage"><?= round($data['behavioral_score'], 2) ?>%</span>
        <div><?= $data['comment_behav'] ?></div>
    </div>

    <div class="final-comment">
        <?= $data['final_comment'] ?>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// Load and render PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Report_{$roll}.pdf", ["Attachment" => true]);
exit;
?>
