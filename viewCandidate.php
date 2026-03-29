<?php
include_once("inc/classes/session.php");
include_once("inc/classes/View.php");
include_once("inc/classes/DB.php");

$session = new Session();
$view = new View();
$db = new DB();

// Authentication check
if (!$session->getSession('login')) {
    header("Location: login.php");
    exit();
}

// Authorization check
$role = $session->getSession('role');
if (!in_array($role, ['admin', 'interviewer'])) {
    echo "<h3 style='text-align:center; color:red; margin-top:20px;'>Access Denied.</h3>";
    exit();
}

// Check current global status
$statusCheck = $db->simplequery("SELECT coding_status FROM ims_candidates LIMIT 1")->fetch();
$is_active = ($statusCheck && $statusCheck['coding_status'] == 1) ? true : false;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggleGlobalCoding'])) {
        $new_status = $is_active ? 0 : 1;
        $sql = "UPDATE ims_candidates SET coding_status = ?";
        $db->simplequery($sql, [$new_status]);
        
        $status_text = ($new_status == 1) ? "STARTED" : "STOPPED";
        $session->setSession('success_msg', "Coding round has been $status_text for all candidates.");
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Flash message retrieval
$successMsg = $session->getSession('success_msg');
if ($successMsg) {
    $session->setSession('success_msg', null);
}

// Handle filters
$filters = [
    'roll' => $_POST['roll'] ?? '',
    'department' => $_POST['department'] ?? ''
];

// Retrieve candidates
$allCandidates = ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter']))
    ? $view->filterCandidate($filters['roll'], $filters['department'], '', '')
    : $view->viewCandidate();

$viewCandidates = [];
foreach ($allCandidates as $cand) {
    // CHECK IF EVALUATION IS COMPLETED
    $sql = "SELECT cand_id FROM ims_reports WHERE cand_id = ? LIMIT 1";
    $reportResult = $db->simplequery($sql, [$cand['cand_id']])->fetch();
    
    $cand['is_evaluated'] = $reportResult ? true : false;
    $viewCandidates[] = $cand;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Candidates</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .control-panel { background: #fcfcfc; border: 2px solid #ddd; padding: 25px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .table > tbody > tr > td { vertical-align: middle !important; }
        .btn-toggle { padding: 10px 30px; font-size: 16px; font-weight: bold; text-transform: uppercase; border-radius: 30px; }
    </style>
</head>
<body>
<div class="container">
    <?php include('nav.php'); ?>

    <?php if ($successMsg): ?>
        <div class="alert alert-success text-center" style="margin-top: 20px;">
            <?= htmlspecialchars($successMsg) ?>
        </div>
    <?php endif; ?>

    <div class="control-panel text-center" style="margin-top: 20px;">
        <h3 style="margin-top: 0; color: #333;"><strong>Coding Round Controller</strong></h3>
        <form method="POST">
            <?php if (!$is_active): ?>
                <button type="submit" name="toggleGlobalCoding" class="btn btn-success btn-toggle" onclick="return confirm('Start coding for everyone?')">
                    <i class="glyphicon glyphicon-play"></i> Start Coding Round
                </button>
            <?php else: ?>
                <button type="submit" name="toggleGlobalCoding" class="btn btn-danger btn-toggle" onclick="return confirm('Stop coding for everyone?')">
                    <i class="glyphicon glyphicon-stop"></i> Stop Coding Round
                </button>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel panel-primary">
        <div class="panel-heading"><h3 class="panel-title">Candidate List</h3></div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Roll Number</th>
                        <th class="text-center">Candidate Name</th>
                        <th class="text-center">Dept</th>
                        <th class="text-center">Live Watch</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($viewCandidates as $cand): ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($cand['cand_roll']) ?></td>
                        <td class="text-center"><strong><?= htmlspecialchars($cand['cand_name']) ?></strong></td>
                        <td class="text-center"><?= htmlspecialchars($cand['cand_dept']) ?></td>
                        
                        <td class="text-center">
                            <?php if (($cand['coding_status'] ?? 0) == 1): ?>
                                <a href="panel_watch.php?id=<?= $cand['cand_id'] ?>" class="btn btn-info btn-xs" target="_blank">
                                    <i class="glyphicon glyphicon-eye-open"></i> Watch
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">Locked</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-center">
                            <?php if ($cand['is_evaluated']): ?>
                                <a href="viewReport.php?id=<?= $cand['cand_id'] ?>" class="btn btn-success btn-xs">
                                    <i class="glyphicon glyphicon-file"></i> View Report
                                </a>
                            <?php else: ?>
                                <a href="takeExam.php?id=<?= $cand['cand_id'] ?>" class="btn btn-primary btn-xs">
                                    <i class="glyphicon glyphicon-edit"></i> Evaluate
                                </a>
                            <?php endif; ?>
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