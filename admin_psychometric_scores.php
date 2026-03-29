<?php
require_once("inc/classes/session.php");
require_once("inc/classes/DB.php");

$session = new Session();
if ($session->getSession('role') !== 'admin' || !$session->getSession('login')) {
    header("Location: login.php");
    exit();
}

$db = new DB();

// Capture filters with defaults
$searchName = isset($_GET['name']) ? trim($_GET['name']) : '';
$minScore = isset($_GET['min_score']) && $_GET['min_score'] !== '' ? (int)$_GET['min_score'] : 0;
$maxScore = isset($_GET['max_score']) && $_GET['max_score'] !== '' ? (int)$_GET['max_score'] : 25;
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'asc'; // Default ascending
$attemptedFilter = isset($_GET['attempted']) ? $_GET['attempted'] : ''; // Attempted or Not Attempted

// Build SQL with dynamic filters
$query = "
    SELECT c.cand_id, 
           c.cand_name, 
           ps.psychometric_score, 
           pa.attempt_id
    FROM ims_candidates c
    LEFT JOIN ims_performance_scores ps 
           ON c.cand_id = ps.cand_id
    LEFT JOIN (
        SELECT p1.id AS attempt_id, p1.student_id
        FROM ims_psychometric_attempts p1
        INNER JOIN (
            SELECT student_id, MAX(submitted_at) as latest
            FROM ims_psychometric_attempts
            WHERE status = 'submitted'
            GROUP BY student_id
        ) p2 
        ON p1.student_id = p2.student_id AND p1.submitted_at = p2.latest
    ) pa ON c.cand_id = pa.student_id
    WHERE 1=1
";

$params = [];

// Name filter
if ($searchName !== '') {
    $query .= " AND c.cand_name LIKE :name";
    $params[':name'] = "%$searchName%";
}

// Attempted / Not Attempted filter
if ($attemptedFilter === 'attempted') {
    $query .= " AND (ps.psychometric_score IS NOT NULL OR pa.attempt_id IS NOT NULL)";
} elseif ($attemptedFilter === 'not_attempted') {
    $query .= " AND ps.psychometric_score IS NULL AND pa.attempt_id IS NULL";
}

// Sorting by score, treat NULL as 0
$query .= $sortOrder === 'asc' 
          ? " ORDER BY COALESCE(ps.psychometric_score, 0) ASC" 
          : " ORDER BY COALESCE(ps.psychometric_score, 0) DESC";

$stmt = $db->simplequery($query, $params);
$students = $stmt->fetchAll();

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="psychometric_scores.xls"');
    echo "S.No\tStudent Name\tPsychometric Score\n";
    foreach ($students as $index => $s) {
        if ($s['psychometric_score'] !== null) {
            $score = round($s['psychometric_score']);
        } elseif (!empty($s['attempt_id'])) {
            $score = 'Attempted (No Admin Score)';
        } else {
            $score = 'Not Attempted';
        }
        echo ($index + 1) . "\t" . $s['cand_name'] . "\t" . $score . "\n";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Psychometric Scores - Admin</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
    <style>
        body { padding-top: 70px; font-family: 'Segoe UI', sans-serif; background: #f9fbfd; }
        .table-container { margin: 30px auto; max-width: 1000px; }
        table { background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
        th, td { text-align: center; vertical-align: middle; }
        .filter-form { background: white; padding: 15px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .btn-gap { margin-left: 8px; }
        @media (max-width: 768px) {
            .filter-form .row > div { margin-bottom: 10px; }
            .text-right { text-align: center !important; }
        }
    </style>
</head>
<body>
<div class="container table-container">
    <?php include('nav.php'); ?>
    <h2 class="text-center" style="color: #0d47a1; margin-bottom: 20px;">All Psychometric Scores</h2>

    <!-- Filter Form -->
    <form class="filter-form" method="GET" action="">
        <!-- Inputs row -->
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-3">
                <input type="text" name="name" class="form-control" placeholder="Search by Name" value="<?= htmlspecialchars($searchName) ?>">
            </div>
            <div class="col-md-2">
                <input type="number" name="min_score" class="form-control" placeholder="Min Score" value="<?= htmlspecialchars($minScore) ?>" min="0" max="25">
            </div>
            <div class="col-md-2">
                <input type="number" name="max_score" class="form-control" placeholder="Max Score" value="<?= htmlspecialchars($maxScore) ?>" min="0" max="25">
            </div>
            <div class="col-md-2">
                <select name="sort" class="form-control">
                    <option value="asc" <?= $sortOrder === 'asc' ? 'selected' : '' ?>>Sort: Low to High</option>
                    <option value="desc" <?= $sortOrder === 'desc' ? 'selected' : '' ?>>Sort: High to Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="attempted" class="form-control">
                    <option value="">Attempt Status</option>
                    <option value="attempted" <?= $attemptedFilter === 'attempted' ? 'selected' : '' ?>>Attempted</option>
                    <option value="not_attempted" <?= $attemptedFilter === 'not_attempted' ? 'selected' : '' ?>>Not Attempted</option>
                </select>
            </div>
        </div>

        <!-- Buttons row -->
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="psychometric_scores.php" class="btn btn-default btn-gap">Reset</a>
                <a href="?<?= http_build_query(array_merge($_GET, ['export' => 'excel'])) ?>" class="btn btn-success btn-gap">Export to Excel</a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Psychometric Score (out of 100)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($students) > 0): ?>
                <?php foreach ($students as $index => $s): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($s['cand_name']) ?></td>
                        <td>
                            <?php
                            if ($s['psychometric_score'] !== null) {
                                echo round($s['psychometric_score']);
                            } elseif (!empty($s['attempt_id'])) {
                                echo "<em>Attempted (No Admin Score)</em>";
                            } else {
                                echo "<em>Not Attempted</em>";
                            }
                            ?>
                        </td>
                        <td>
                            <?php if (!empty($s['attempt_id'])): ?>
                                <a href="psychometric_report.php?attempt_id=<?= $s['attempt_id'] ?>" 
                                   class="btn btn-info btn-sm" target="_blank">
                                    View Psychometric Report
                                </a>
                            <?php else: ?>
                                <button class="btn btn-default btn-sm" disabled>No Attempt</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4"><em>No records found</em></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
