<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
	<h3>Create Transaction</h3>
	<form method = "POST">
		<input type="number" placeholder="Account Source" name="account_src_id"/>
		<input type="number" placeholder="Account Destination" name="account_dest_id"/>
                <label>Transaction Type</label>
		<select name="action_type">
				<option value="0">Deposit</option>
				<option value="1">Withdraw</option>
				<option value="2">Transfer</option>
		</select>
		<input type="number" placeholder="Amount" name="amount"/>
		<input type="submit" name="save" value="Create"/>
	</form>

<?php
if(isset($_POST["save"])){
	$src = $_POST["account_src_id"];
	$dest = $_POST["account_dest_id"];
	$type = $_POST["action_type"];
	$amount = $_POST["amount"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Transactions (account_src_id, account_dest_id, action_type, amount, user_id) VALUES(:src, :dest:, :type, :amount, :user)");
	$r = $stmt->execute([
		":src" => $src,
		":dest" => $dest,
		":type" => $type,
		":amount" => $amount,
		":user" => $user,
	]);
	if ($r) {
        	flash("Created successfully with id: " . $db->lastInsertId());
    	}
   	else {
        	$e = $stmt->errorInfo();
        	flash("Error creating: " . var_export($e, true));
        }
}
?>
<?php require(__DIR__ . "/partials/flash.php");	
