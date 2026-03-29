<?php
// 1. Fix the paths to match your folder structure
include_once("inc/classes/session.php");
include_once("inc/classes/DB.php"); 

$userSession = new Session();
$db = new DB();

// 2. Security Check
if ($userSession->getSession('login') != true || $userSession->getSession('role') != 'student') {
    header('Location: login.php');
    exit();
}

/** * NEW LOGIC START: Check if Panel enabled coding
 * Note: Ensure 'user_id' in session matches 'cand_id' in your database table
 */
$studentId = $userSession->getSession('user_id'); 
$isCodingEnabled = false;

try {
    $statusSql = "SELECT coding_status FROM ims_candidates WHERE cand_id = ?";
    $statusStmt = $db->simplequery($statusSql, [$studentId]);
    $studentData = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($studentData && $studentData['coding_status'] == 1) {
        $isCodingEnabled = true;
    }
} catch (Exception $e) {
    // Fail silently or log error
}

// 3. Randomization Logic (Runs only if coding is enabled)
if ($isCodingEnabled && !$userSession->getSession('assigned_questions')) {
    try {
        // Fetch 2 random questions from your database
        $query = "SELECT * FROM ims_coding_questions ORDER BY RAND() LIMIT 2";
        $stmt = $db->simplequerywithoutcondition($query);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($questions) < 2) {
            die("Error: Not enough questions in the database.");
        }

        // Save to session
        $userSession->setSession('assigned_questions', $questions);
    } catch (Exception $e) {
        die("Database Error: " . $e->getMessage());
    }
}

// 4. Retrieve for display (Only if enabled)
$q1 = null; $q2 = null; $assigned = [];
if ($isCodingEnabled) {
    $assigned = $userSession->getSession('assigned_questions');
    $q1 = $assigned[0];
    $q2 = $assigned[1];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Live Coding Interview</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f5f5f5; overflow: hidden; height: 100vh; }
        .main-wrapper { height: calc(100vh - 70px); padding: 15px; margin-top: 10px; }
        .problem-area { background: white; height: 100%; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-y: auto; }
        .editor-area { height: 100%; display: flex; flex-direction: column; }
        .editor-header { background: #fff; padding: 10px; border: 1px solid #ddd; border-bottom: none; border-radius: 5px 5px 0 0; display: flex; justify-content: space-between; align-items: center; }
        #editor-container { flex-grow: 1; border: 1px solid #ddd; }
        .console-output { height: 150px; background: #1e1e1e; color: #d4d4d4; padding: 15px; font-family: 'Consolas', monospace; border-radius: 0 0 5px 5px; overflow-y: auto; border: 1px solid #333; }
        .label-lang { font-weight: bold; margin-right: 10px; }
        
        /* Waiting Screen Styles */
        .waiting-box { margin-top: 100px; text-align: center; background: white; padding: 50px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<?php include('nav.php'); ?>

<div class="container-fluid main-wrapper">

    <?php if (!$isCodingEnabled): ?>
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="waiting-box">
                    <span class="glyphicon glyphicon-lock" style="font-size: 50px; color: #f0ad4e;"></span>
                    <h2 style="margin-top: 20px;">Coding Round Not Enabled</h2>
                    <p class="text-muted" style="font-size: 16px;">The interviewer has not started your coding test yet. Please stay on this page; it will automatically unlock once enabled.</p>
                    <div class="progress" style="margin-top: 25px;">
                        <div class="progress-bar progress-bar-warning progress-bar-striped active" role="progressbar" style="width: 100%">
                            Waiting for Interviewer...
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>setTimeout(function(){ location.reload(); }, 5000);</script>

    <?php else: ?>
        <div class="row" style="height: 100%;">
            <div class="col-md-4" style="height: 100%;">
                <div class="problem-area">
                    <div class="btn-group btn-group-justified" style="margin-bottom: 15px;">
                        <a href="#" class="btn btn-primary active" onclick="showQuestion(0)">Question 1</a>
                        <a href="#" class="btn btn-primary" onclick="showQuestion(1)">Question 2</a>
                    </div>

                    <div id="q-content">
                        <h3 id="q-title">Problem: <?php echo $q1['title']; ?></h3>
                        <span class="label label-warning"><?php echo $q1['difficulty']; ?></span>
                        <hr>
                        <p id="q-desc"><?php echo nl2br($q1['description']); ?></p>
                        <h4>Sample Input:</h4>
                        <pre id="q-input"><?php echo $q1['sample_input']; ?></pre>
                        <hr>
                        <h4>Test Cases:</h4>
                        <div id="test-cases-status">
                            <p class="text-muted">Output will be validated against hidden test cases.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8 editor-area">
                <div class="editor-header">
                    <div class="form-inline">
                        <span class="label-lang">Language:</span>
                        <select id="language-select" class="form-control input-sm" onchange="changeLanguage()">
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                            <option value="cpp">C++</option>
                            <option value="java">Java</option>
                            <option value="php">PHP</option>
                        </select>
                    </div>
                    <button class="btn btn-success btn-sm" onclick="runCode()">
                        <span class="glyphicon glyphicon-play"></span> Run Code
                    </button>
                </div>
                <div id="editor-container"></div>
                <div class="console-output" id="console">
                    <span style="color: #6a9955;">// Output console ready...</span>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($isCodingEnabled): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.36.1/min/vs/loader.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.36.1/min/vs/loader.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script>
    const templates = {
        javascript: "function solution(n) {\n    // Write JavaScript code here\n    return n;\n}",
        python: "def solution(n):\n    # Write Python code here\n    return n",
        cpp: "#include <iostream>\n\nint solution(int n) {\n    // Write C++ code here\n    return n;\n}",
        java: "public class Main {\n    public int solution(int n) {\n        // Write Java code here\n        return n;\n    }\n}",
        php: "<?php\n\nfunction solution($n) {\n    // Write PHP code here\n    return $n;\n}\n?>"
    };

    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.36.1/min/vs' }});
    require(['vs/editor/editor.main'], function() {
        window.editor = monaco.editor.create(document.getElementById('editor-container'), {
            value: templates.javascript,
            language: 'javascript',
            theme: 'vs-dark',
            fontSize: 14,
            automaticLayout: true,
            minimap: { enabled: false },
            scrollBeyondLastLine: false
        });
        console.log("Editor initialized successfully!");
    });

    const assignedQuestions = <?php echo json_encode($assigned); ?>;

    function showQuestion(index) {
        const q = assignedQuestions[index];
        document.getElementById('q-title').innerText = "Problem: " + q.title;
        document.getElementById('q-desc').innerHTML = q.description.replace(/\n/g, '<br>');
        document.getElementById('q-input').innerText = q.sample_input;
        const buttons = document.querySelectorAll('.btn-group .btn');
        buttons.forEach((btn, i) => {
            if(i === index) btn.classList.add('active');
            else btn.classList.remove('active');
        });
    }

    function syncLiveCode() {
        if (window.editor) {
            const currentCode = window.editor.getValue();
            const studentId = "<?php echo $studentId; ?>";

            $.ajax({
                url: 'save_live_code.php',
                method: 'POST',
                data: { 
                    code: currentCode, 
                    cand_id: studentId 
                },
                success: function(response) {
                    // This will prove to us in the console that it's working
                    console.log("Database updated at: " + new Date().toLocaleTimeString());
                }
            });
        } else {
            console.log("Waiting for editor to load...");
        }
    }

    // Run every 2 seconds
    setInterval(syncLiveCode, 2000);
</script>
<?php endif; ?>

</body>
</html>