<?php
require_once (__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$check = true;
$results = [];

if(isset($_GET["id"])){
    $transId = $_GET["id"];
}
else{
    $check = false;
    flash("Id is not set in url");
}



//TODO Fix this so that it returns actual account numbers in the query, not the internal id.
if($check) {
    $db = getDB();
    $stmt = $db->prepare("SELECT act_src_id, Accounts.id, Accounts.account_number, amount, action_type, memo FROM Transactions JOIN Accounts on Accounts.id = Transactions.act_dest_id WHERE act_src_id =:id LIMIT 10");
    $r = $stmt->execute([":id" => $transId]);
    if ($r){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results.");
        $check = false;
    }
}
/*
if($check){
    $results2 = new SplFixedArray(10);
    $i = 0;
    foreach($results as $r){
        $destID = $r["act_dest_id"];
        $stmt = $db->prepare("SELECT account_number FROM Accounts WHERE id=:dest");
        $q = $stmt->execute([":dest" => $destID]);
        if ($q){
            $results2[$i] = $stmt->fetch(PDO::FETCH_ASSOC);
            $i++;
        }
        else{
            flash("There was a problem fetching the destination account number.");
            $check = false;
        }
    }
}
*/



?>
<div class="bodyMain">
    <h1><strong>List Transactions</strong></h1>

    <div class="results">
        <?php if(count($results) > 0): ?>
            <div class="list-group">
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <div>
                            <div>Destination Account ID:</div>
                            <div><?php safer_echo($r["account_number"]); ?></div>
                        </div>
                        <div>
                            <div>Transaction Type:</div>
                            <div><?php safer_echo($r["action_type"]); ?></div>
                        </div>
                        <div>
                            <div>Amount Moved:</div>
                            <div><?php safer_echo($r["amount"]); ?></div>
                        </div>
                        <div>
                            <div>Memo:</div>
                            <div><?php safer_echo($r["memo"]); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No Results</p>
        <?php endif; ?>
    </div>