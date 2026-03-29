<?php
require_once("inc/classes/session.php");
require_once("inc/classes/DB.php");

$userSession = new Session();
if (!$userSession->getSession('login')) {
    header('Location: login.php');
    exit();
}
if ($userSession->getSession('role') !== 'student') {
    header('Location: landing.php');
    exit();
}

$student_id = $userSession->getSession('user_id');
$db = new DB();

// Check if score already exists in ims_performance_scores
$scoreRow = $db->simplequery(
    "SELECT psychometric_score FROM ims_performance_scores WHERE cand_id = ?",
    [$student_id]
)->fetch();

if (isset($scoreRow['psychometric_score']) && $scoreRow['psychometric_score'] > 0) {
    $totalScore = round($scoreRow['psychometric_score']);

    // Fetch the latest attempt for this student
    $attemptData = $db->simplequery(
        "SELECT * FROM ims_psychometric_attempts WHERE student_id = ? ORDER BY id DESC LIMIT 1",
        [$student_id]
    )->fetch();

    $attempt = [
        'status' => 'submitted',
        'total_auto_score' => $totalScore,
        'id' => $attemptData ? $attemptData['id'] : null
    ];

    $questions = [];
} else {
    // Fetch or create attempt
    $attempt = $db->simplequery(
        "SELECT * FROM ims_psychometric_attempts WHERE student_id = ? ORDER BY id DESC LIMIT 1",
        [$student_id]
    )->fetch();

    if (!$attempt) {
        // Create attempt
        $db->simplequery(
            "INSERT INTO ims_psychometric_attempts (student_id, status) VALUES (?, 'in_progress')",
            [$student_id]
        );
        $attempt_id = $db->lastInsertId();

        // Insert 25 random questions using PHP loop
        $randomQuestions = $db->simplequery(
            "SELECT id, correct_answer FROM ims_psychometric_questions ORDER BY RAND() LIMIT 25"
        )->fetchAll();

        $position = 1;
        foreach ($randomQuestions as $q) {
            $db->execute(
                "INSERT INTO ims_psychometric_attempt_items (attempt_id, question_id, correct_answer, position, points)
                 VALUES (?, ?, ?, ?, 1)",
                [$attempt_id, $q['id'], $q['correct_answer'], $position]
            );
            $position++;
        }

        $attempt = $db->simplequery(
            "SELECT * FROM ims_psychometric_attempts WHERE id = ?",
            [$attempt_id]
        )->fetch();
    }

    if (isset($_GET['submitted'])) {
        $attempt = $db->simplequery(
            "SELECT * FROM ims_psychometric_attempts WHERE id = ?",
            [$attempt['id']]
        )->fetch();
    }

    $attempt_id = $attempt['id'];

    // Handle test submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $attempt['status'] !== 'submitted') {
        $validOptions = ['A', 'B', 'C', 'D'];

        if (!empty($_POST['answers']) && is_array($_POST['answers'])) {
            foreach ($_POST['answers'] as $item_id => $answer) {
                if (in_array($answer, $validOptions)) {
                    $db->simplequery(
                        "UPDATE ims_psychometric_attempt_items SET answer = ? WHERE id = ? AND attempt_id = ?",
                        [$answer, $item_id, $attempt_id]
                    );
                }
            }
        }

        $db->simplequery("
            UPDATE ims_psychometric_attempt_items
            SET is_correct = CASE WHEN UPPER(answer) = UPPER(correct_answer) THEN 1 ELSE 0 END,
                points = CASE WHEN UPPER(answer) = UPPER(correct_answer) THEN points ELSE 0 END
            WHERE attempt_id = ?
        ", [$attempt_id]);

        $totalPoints = $db->simplequery("
            SELECT COALESCE(SUM(points), 0) AS total
            FROM ims_psychometric_attempt_items
            WHERE attempt_id = ?
        ", [$attempt_id])->fetch()['total'];

        $scorePercentage = round(($totalPoints / 25) * 100);

        // Update attempt table
        $db->simplequery("
            UPDATE ims_psychometric_attempts
            SET total_auto_score = ?, submitted_at = NOW(), status = 'submitted'
            WHERE id = ?
        ", [$scorePercentage, $attempt_id]);

        // Store normalized score in ims_performance_scores
        $existingScore = $db->simplequery("
            SELECT id FROM ims_performance_scores WHERE cand_id = ?
        ", [$student_id])->fetch();

        if ($existingScore) {
            $db->execute("
                UPDATE ims_performance_scores
                SET psychometric_score = ?
                WHERE cand_id = ?
            ", [$scorePercentage, $student_id]);
        } else {
            $db->execute("
                INSERT INTO ims_performance_scores (cand_id, psychometric_score)
                VALUES (?, ?)
            ", [$student_id, $scorePercentage]);
        }

        header("Location: student_psychometric.php?submitted=1");
        exit();
    }

    // Fetch assigned questions
    $questions = $db->simplequery("
        SELECT i.id AS item_id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
               i.answer, i.correct_answer, i.points
        FROM ims_psychometric_attempt_items i
        JOIN ims_psychometric_questions q ON q.id = i.question_id
        WHERE i.attempt_id = ?
        ORDER BY i.position
    ", [$attempt_id])->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Psychometric Test</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" />
    <style>
        body { padding-top: 60px; background: #f9fbfd; font-family: 'Segoe UI', sans-serif; }
        .test-box { background: white; border-radius: 12px; padding: 30px; margin: 25px auto; max-width: 900px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .question { font-weight: bold; margin-top: 20px; font-size: 18px; }
        .option { margin-left: 20px; }
        .btn-submit { margin-top: 25px; }
        .timer-box { font-size: 18px; font-weight: bold; color: #d32f2f; text-align: right; margin-bottom: 15px; display: none; }
        #warningNote { background: #fff3cd; color: #856404; padding: 15px; margin-bottom: 20px; border: 1px solid #ffeeba; border-radius: 5px; font-weight: bold; text-align: center; display: none; }
        #reenterOverlay, #startOverlay { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.85); color: #fff; z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        #reenterOverlay .box, #startOverlay .box { background: #fff; color: #000; padding: 24px; border-radius: 8px; text-align:center; max-width: 700px; width: 90%; position: relative; }
        #reenterOverlay button, #startOverlay button { margin-top: 12px; }
        #closeOverlay { position: absolute; top: 10px; left: 10px; background: transparent; border: none; font-size: 28px; font-weight: bold; color: #fff; cursor: pointer; z-index: 10000; }
    </style>
</head>
<body>
<div class="container">
    <?php include('nav.php'); ?>

    <div class="test-box">
        <h2 style="color: #0d47a1;">Psychometric Test</h2>

        <div id="warningNote">
            Warning: You left fullscreen or switched tab. This is your first warning.<br>
            Repeated violations may end your test.
        </div>

        <?php if ($attempt['status'] === 'submitted'): ?>
            <div class="alert alert-info text-center">
                You have already completed your test.<br>
                <strong>Total Score: <?= htmlspecialchars($attempt['total_auto_score']); ?>%</strong>
                <br><br>
                <?php if (!empty($attempt['id'])): ?>
                    <a href="psychometric_report.php?attempt_id=<?= urlencode($attempt['id']); ?>" class="btn btn-primary">
                        View Detailed Report
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div id="timerWrapper" class="timer-box">Time Remaining: <span id="timer">30:00</span></div>

            <div id="startOverlay">
                <button id="closeOverlay">&times;</button>
                <div class="box">
                    <h3>Ready to Start the Psychometric Test?</h3>
                    <p>The test will launch in fullscreen mode upon starting. Any attempt to exit fullscreen or switch tabs will result in a warning on the first violation. A second violation will automatically terminate and submit your test.</p>
                    <button class="btn btn-success btn-lg" id="startBtn">Start Exam in Fullscreen</button>
                </div>
            </div>

            <div id="reenterOverlay" style="display:none;">
                <div class="box">
                    <h3>Return to Fullscreen</h3>
                    <p>Browser blocked automatic re-entry. Click the button below to return to fullscreen and continue your exam.</p>
                    <button class="btn btn-primary" id="reenterBtn">Return to Fullscreen</button>
                </div>
            </div>

            <form method="POST" id="testForm" style="display:none;">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="question"><?= ($index + 1) . ". " . htmlspecialchars($q['question_text']); ?></div>
                    <div class="option">
                        <?php foreach (['A'=>'option_a','B'=>'option_b','C'=>'option_c','D'=>'option_d'] as $optKey => $optField): ?>
                            <label>
                                <input
                                    type="radio"
                                    name="answers[<?= $q['item_id'] ?>]"
                                    value="<?= $optKey ?>"
                                    <?= ($q['answer'] === $optKey) ? 'checked' : '' ?>
                                />
                                <?= htmlspecialchars($q[$optField]); ?>
                            </label><br/>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div class="text-center">
                    <button type="submit" name="submit_test" class="btn btn-primary btn-submit">
                        Submit Test
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($attempt['status'] !== 'submitted'): ?>
    <script>
(function(){
    let violationCount = 0;
    let monitoringEnabled = true;
    let lastViolationAt = 0;
    const DUPLICATE_THRESHOLD_MS = 1200;

    const warningNote = document.getElementById('warningNote');
    const startOverlay = document.getElementById('startOverlay');
    const reenterOverlay = document.getElementById('reenterOverlay');
    const startBtn = document.getElementById('startBtn');
    const reenterBtn = document.getElementById('reenterBtn');
    const closeOverlay = document.getElementById('closeOverlay');
    const testForm = document.getElementById('testForm');
    const timerWrapper = document.getElementById('timerWrapper');
    const timerDisplay = document.getElementById('timer');

    let timeLeft = 30 * 60;
    let timerInterval = null;

    function isFullscreen() {
        return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
    }

    function openFullscreen() {
        const elem = document.documentElement;
        try {
            const r = (elem.requestFullscreen && elem.requestFullscreen()) ||
                      (elem.webkitRequestFullscreen && elem.webkitRequestFullscreen()) ||
                      (elem.mozRequestFullScreen && elem.mozRequestFullScreen()) ||
                      (elem.msRequestFullscreen && elem.msRequestFullscreen());
            return new Promise((resolve) => {
                setTimeout(() => { resolve(isFullscreen()); }, 600);
            });
        } catch (e) {
            return new Promise((resolve) => { setTimeout(() => { resolve(isFullscreen()); }, 600); });
        }
    }

    function startTimer() {
        timerWrapper.style.display = 'block';
        function tick() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            if (timeLeft <= 0) {
                stopMonitoring();
                alert('Time is up! Submitting your test.');
                testForm.submit();
            }
            timeLeft--;
        }
        timerInterval = setInterval(tick, 1000);
        tick();
    }

    function handleViolationEvent() {
        const now = Date.now();
        if (now - lastViolationAt < DUPLICATE_THRESHOLD_MS) return;
        lastViolationAt = now;

        if (!monitoringEnabled) return;
        violationCount++;

        if (violationCount === 1) {
            // First violation → show warning
            warningNote.innerHTML = `⚠ Warning: This is your LAST and FINAL warning! If you leave fullscreen or switch tabs again, your exam will be auto-submitted.`;
            warningNote.style.display = 'block';
        } else if (violationCount === 2) {
            // Second violation → auto-submit
            warningNote.innerHTML = `❌ You left fullscreen/tab twice. Your test will now be submitted.`;
            warningNote.style.display = 'block';
            stopMonitoring();
            testForm.submit();
        }
    }

    function attachMonitoring() {
        document.addEventListener("visibilitychange", () => { if (document.hidden) handleViolationEvent(); });
        window.addEventListener("blur", () => handleViolationEvent());
        ["fullscreenchange","webkitfullscreenchange","mozfullscreenchange","MSFullscreenChange"]
            .forEach(ev => document.addEventListener(ev, () => { if (!isFullscreen()) handleViolationEvent(); }));
    }

    function stopMonitoring() {
        monitoringEnabled = false;
        reenterOverlay.style.display = 'none';
        startOverlay.style.display = 'none';
        if (timerInterval) clearInterval(timerInterval);
    }

    startBtn.addEventListener('click', () => {
        startOverlay.style.display = 'none';
        testForm.style.display = 'block';
        openFullscreen().then(() => {
            startTimer();
            attachMonitoring();
        });
    });

    reenterBtn.addEventListener('click', () => {
        openFullscreen().then((entered) => {
            if (entered) {
                reenterOverlay.style.display = 'none';
                warningNote.style.display = 'none';
            }
        });
    });

    closeOverlay.addEventListener('click', () => {
        startOverlay.style.display = 'none';
    });

    testForm.addEventListener('submit', stopMonitoring);
})();
</script>


<?php endif; ?>
</body>
</html>
