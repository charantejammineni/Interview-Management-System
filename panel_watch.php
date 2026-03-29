<?php
include_once("inc/classes/session.php");
include_once("inc/classes/DB.php");

$session = new Session();
$db = new DB();

if (!$session->getSession('login')) { header("Location: login.php"); exit(); }

$cand_id = $_GET['id'] ?? null;
if (!$cand_id) { exit("Invalid Candidate ID"); }

// Get candidate details for the header
$cand = $db->simplequery("SELECT cand_name, cand_roll FROM ims_candidates WHERE cand_id = ?", [$cand_id])->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Watching: <?= htmlspecialchars($cand['cand_name']) ?></title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <style>
        #codebox { min-height: 500px; background: #2d2d2d; color: #ccc; padding: 15px; border-radius: 5px; overflow: auto; }
        .live-indicator { color: red; animation: blinker 1.5s linear infinite; font-weight: bold; }
        @keyframes blinker { 50% { opacity: 0; } }
    </style>
</head>
<body>
<div class="container" style="margin-top: 20px;">
    <div class="panel panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                Live Stream: <b><?= htmlspecialchars($cand['cand_name']) ?></b> (<?= htmlspecialchars($cand['cand_roll']) ?>)
                <span class="pull-right"><span class="live-indicator">●</span> LIVE</span>
            </h3>
        </div>
        <div class="panel-body">
            <p class="text-muted">Code updates every 3 seconds...</p>
            <pre id="codebox"><code id="liveCode" class="language-php">Loading candidate code...</code></pre>
            
            <hr>
            <a href="viewCandidates.php" class="btn btn-default">Back to List</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>

<script>
function refreshCode() {
    $.ajax({
        url: 'fetch_live_code.php',
        type: 'GET',
        data: { id: '<?= $cand_id ?>' },
        success: function(data) {
            const codeElement = document.getElementById('liveCode');
            // Only update and re-highlight if the code has actually changed
            if (codeElement.textContent !== data) {
                codeElement.textContent = data;
                Prism.highlightElement(codeElement);
            }
        }
    });
}

// Check for updates every 3 seconds
setInterval(refreshCode, 3000);

// Initial load
$(document).ready(refreshCode);
</script>
</body>
</html>