<?php
require_once (__DIR__ . "/partials/nav.php");

if(!has_role("Admin")){
    flash("You do not have permission to access this page.");
    dir(header("Location: login.php"));
}

if(isset($_GET["id"])){
    $transId = $_GET["id"];
}

$transResult = [];
$srcResult = [];
$destResult = [];
if(isset($transId)){
    $db = getDB();
    $stmt = $db->prepare("SELECT id, act_src_id, act_dest_id, action_type, amount, memo, created FROM Transactions WHERE  id = :id");
    $r = $stmt->execute([":id" => $transId]);
    $transResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$transResult) {
        $e = $stmt->errorInfo();
        flash("Error fetching transaction info: " . var_export($e, true));
    }

    $stmt = $db->prepare("SELECT Accs.id, account_number, Users.username FROM Accounts as Accs JOIN Users on Accs.user_id = Users.id WHERE Accs.id = :number");
    $r = $stmt->execute([":number" => $transResult["act_src_id"]]);
    $srcResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$srcResult) {
        $e = $stmt->errorInfo();
        flash("Error fetching source info: " . var_export($e, true));
    }

    $stmt = $db->prepare("SELECT Accs.id, account_number, Users.username FROM Accounts as Accs JOIN Users on Accs.user_id = Users.id WHERE Accs.id = :number");
    $r = $stmt->execute([":number" => $transResult["act_dest_id"]]);
    $destResult = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$destResult) {
        $e = $stmt->errorInfo();
        flash("Error fetching destination info: " . var_export($e, true));
    }
}
?>
<div class="bodyMain">
    <h3>Transaction Details</h3>

    <?php if(isset($destResult) && !empty($destResult)): ?>
        <div class="card">
            <div class="cardTitle">
                <p>Details for Transaction:</p>
            </div>
            <div class="cardBody">
                <div>
                    <div>Source Account: <?php safer_echo($transResult["act_src_id"]); ?></div>
                    <div>Owner: <?php safer_echo($srcResult["username"]); ?></div> <br>
                    <div>Destination Account: <?php safer_echo($transResult["act_dest_id"]); ?></div>
                    <div>Owner: <?php safer_echo($destResult["username"]); ?></div> <br>
                    <div>Action Type: <?php safer_echo($transResult["action_type"]); ?></div>
                    <div>Amount Total: <?php safer_echo($transResult["amount"]); ?></div>
                    <div>Memo: <?php safer_echo($transResult["memo"]); ?></div>
                    <div>Date/Time Occurred: <?php safer_echo($transResult["created"]); ?></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>Error looking up id.</p>
    <?php endif; ?>

