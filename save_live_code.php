<?php
include_once("inc/classes/DB.php");
$db = new DB();

if (isset($_POST['code']) && isset($_POST['cand_id'])) {
    $code = $_POST['code'];
    $id = $_POST['cand_id'];
    
    // Ensure your 'ims_candidates' table has a 'live_code' column (TEXT type)
    $sql = "UPDATE ims_candidates SET live_code = ? WHERE cand_id = ?";
    $db->simplequery($sql, [$code, $id]);
}
?>