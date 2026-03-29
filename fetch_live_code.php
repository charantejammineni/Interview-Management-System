<?php
include_once("inc/classes/DB.php");
$db = new DB();

$cand_id = $_GET['id'] ?? null;

if ($cand_id) {
    $sql = "SELECT live_code FROM ims_candidates WHERE cand_id = ?";
    $stmt = $db->simplequery($sql, [$cand_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Return only the code text
    echo $data['live_code'] ?? "// No code written yet...";
}
?>