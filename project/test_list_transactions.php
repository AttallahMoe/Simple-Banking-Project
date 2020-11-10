<?php
require_once (__DIR__ . "/partials/nav.php");
if (!has_role("Admin")){
    flash("You do not have permission to access this page.");
    die(header("Location: /login.php"));
}

$query = "";
$results = [];

if(isset($_POST["query"])){
    $query = $_POST["query"];
}

//TODO Fix this so that it returns actual account numbers in the query, not the internal id.
if(isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, act_src_id, act_dest_id, amount, action_type, memo FROM Transactions WHERE act_src_id LIKE :q LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r){
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        flash("There was a problem fetching the results.");
    }
}
?>
<div class="bodyMain">
    <h1><strong>TEST PAGE</strong></h1>
    <h3>This page is used to query transactions (For now, it uses the internal ID's for accounts)</h3>

    <form method="POST">
        <label> Internal Account ID <br>
            <input name="query" placeholder="Search" value="<?php safer_echo($query);?>"/>
        </label>
        <input type="submit" value="Search" name="search"/>
        <input type="reset"/>
    </form>

    <div class="results">
        <?php if(count($results) > 0): ?>
            <div class="list-group">
                <?php foreach ($results as $r): ?>
                    <div class="list-group-item">
                        <div>
                            <div>Source Account ID:</div>
                            <div><?php safer_echo($r["act_src_id"]); ?></div>
                        </div>
                        <div>
                            <div>Destination Account ID:</div>
                            <div><?php safer_echo($r["act_dest_id"]); ?></div>
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
                        <div>
                            <a href="test_edit_transactions.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                            <a href="test_view_transactions.php?id=<?php safer_echo($r['id']); ?>">View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No Results</p>
        <?php endif; ?>
    </div>
