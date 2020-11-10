<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//saving
if (isset($_POST["save"])) {
	//TODO add proper validation/checks
	$src = $_POST["account_src_id"];
	$dest = $_POST["account_dest_id"];
	$type = $_POST["action_type"];
	$amount = $_POST["amount"];
	$user = get_user_id();
	$db = getDB();
	if(isset($id)) {
		$stmt = $db->prepare("UPDATE Transactions set account_src_id=:src, account_dest_id=:dest, action_type=:type, amount=:amount where id = :id");
		$r = $stmt->execute([
			":src" => $src,
			":dest" => $dest,
			":type" => $type,
			":amount" => $amount,
			":id" => $id	
		]);
		if ($r) {
	        	flash("Updated successfully with id: " . $id);
        	}
       		else {
            		$e = $stmt->errorInfo();
            		flash("Error updating: " . var_export($e, true));
        	}
    	}
    	else {
        	flash("ID isn't set, we need an ID in order to update");
    	}
}
?>

<?php
//fetching
$result = [];
if (isset($id)) {
    $id = $_GET["id"];
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM Transactions where id = :id");
    $r = $stmt->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
}
//get eggs for dropdown
$db = getDB();
$stmt = $db->prepare("SELECT id,name from Accounts LIMIT 10");
$r = $stmt->execute();
$eggs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>     

<h3>Edit Transactions</h3>
<form method="POST">
<input type = "number" placeholder = "Account Source" name="account_src_id" value=<?php echo $result["account_src_id"]; ?>"/>
<input type = "number" placeholder = "Account Destination" name="account_dest_id" value=<?php echo $result["account_dest_id"]; ?>"/>
<input type = "drop
