<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if(!has_role("Admin")){
	flash("You don't have permission to access this page");
	die(header("Location: login.php"));
}
?>


<form method="POST">
		<input name="account_number" placeholder="Account Number"/>
		<label>Account Type</label>
		<select name="account_type">
				<option value="0">Checking</option>
				<option value="1">Saving</option>
				<option value="2">Loan</option>
		</select>
		<input type="number" name="balance" value="balance" placeholder="Balance"/>
		<input type="submit" name="save" value="Create"/>
</form>
		
<?php
if(isset($_POST["save"])){
	$account_number = $_POST["account_number"];
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, balance, user_id) VALUES(:account_number, :account_type, :balance, :user)");
	$r = $stmt->execute([
		":account_number"=>$account_number,
		":account_type"=>$account_type,
		":balance"=>$balance,
		":user"=>$user, 
	//	":id"=>$id
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}

?>
<?php require(__DIR__ . "/partials/flash.php");?>
