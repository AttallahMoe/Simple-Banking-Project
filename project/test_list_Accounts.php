<?php require_once(__DIR__ . "/partials/nav.php); ?>

<?php
if(!has_role("Admin)){
	flash("You don't have permission to access this page");
	die(header("Location: login.php"));	
}
?>

<?php
$query = "";
$results = [];
if (isset($_POST["query"]){
	$query = $_POST["query"];
}

if (isset($_POST["search"]) && !empty($query)) {
	$db = getDB();
	$stmt = $db->prepare("SELECT account_number, account_type,user_id, balance from Accounts WHERE name like :q LIMIT 10");
        if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else {
        flash("There was a problem fetching the results");
        }
}
?>
<form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <div class="list-group-item">
                    <div>
                        <div>Account Number:</div>
                        <div><?php safer_echo($r["account_number"]); ?></div>
                    </div>
                    <div>
                        <div>Account Type:</div>
                        <div><?php getState($r["account_type"]); ?></div>
                    </div>
                    <div>
                        <div>Balance</div>
                        <div><?php safer_echo($r["balance"]); ?></div>
                    </div>
                    <div>
                        <div>Owner Id:</div>
                        <div><?php safer_echo($r["user_id"]); ?></div>
                    </div>
                    <div>
                        <a type="button" href="test_edit_Accounts.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                        <a type="button" href="test_view_Accounts.php?id=<?php safer_echo($r['id']); ?>">View</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>

