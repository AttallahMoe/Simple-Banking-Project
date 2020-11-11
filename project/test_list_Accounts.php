<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$query = "";
$results = [];
if (isset($_POST["query"])){
	$query = $_POST["query"];
}

if (isset($_POST["search"]) && !empty($query)) {
	$db = getDB();
	$stmt = $db->prepare("SELECT id, account_number, account_type, balance from Accounts WHERE account_number like :q LIMIT 10");
	$r = $stmt->execute([":q"=> "%$query%"]);
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
                        <div><?php getAccount($r["account_type"]); ?></div>
                    </div>
                    <div>
                        <div>Balance</div>
                        <div><?php safer_echo($r["balance"]); ?></div>
                    </div>
                    <div>
                        <div>Owner Id:</div>
                        <div><?php safer_echo($r["id"]); ?></div>
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

<?php require(__DIR__ . "/partials/flash.php");?>
