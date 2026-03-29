<?php
include_once("inc/classes/session.php");
include_once("inc/classes/DB.php");

$userSession = new Session();
$db = new DB();

// Get data from the JavaScript Frontend
$code = $_POST['code'] ?? '';
$lang = $_POST['lang'] ?? 'javascript';
$q_id = $_POST['q_id'] ?? 0;

// 1. Map your dropdown values to Piston's required language/version strings
$langMap = [
    'javascript' => ['name' => 'javascript', 'version' => '18.15.0'],
    'python'     => ['name' => 'python', 'version' => '3.10.0'],
    'cpp'        => ['name' => 'c++', 'version' => '10.2.0'],
    'java'       => ['name' => 'java', 'version' => '15.0.2'],
    'php'        => ['name' => 'php', 'version' => '8.2.3']
];

$selected = $langMap[$lang];

// 2. Fetch the Hidden Test Case - Change 'test_input' to 'sample_input'
$stmt = $db->simplequery("SELECT sample_input, expected_output FROM ims_coding_questions WHERE id = ?", [$q_id]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

// ... 

// 3. Prepare the request - Change 'test_input' to 'sample_input'
$payload = [
    "language" => $selected['name'],
    "version"  => $selected['version'],
    "files"    => [["content" => $code]],
    "stdin"    => $question['sample_input'] // Updated here
];

$ch = curl_init("https://emkc.org/api/v2/piston/execute");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = json_decode(curl_exec($ch), true);
curl_close($ch);

// 4. Compare Student's Actual Output vs Database Expected Output
$actual_output   = trim($response['run']['stdout'] ?? '');
$expected_output = trim($question['expected_output']);

if ($actual_output === $expected_output && !empty($expected_output)) {
    echo json_encode(['status' => 'success', 'message' => 'Correct Answer! All test cases passed.']);
} else {
    $error_msg = !empty($response['run']['stderr']) ? "Runtime Error: " . $response['run']['stderr'] : "Wrong Answer.";
    echo json_encode([
        'status'  => 'error', 
        'message' => $error_msg,
        'debug'   => "Expected: $expected_output | Got: $actual_output"
    ]);
}
?>