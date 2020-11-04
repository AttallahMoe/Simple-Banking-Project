<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if(!has_roles("Admin")){
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
				<option value="3">Loan</option>
		</select>
		<input type="number" value="balance" placeholder="Balance"/>
		<input type="submit" name="save" value="Update"/>
</form>		

if(isset($_POST["save"])){
	$account_number = $_POST["account_number"];
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB;
	$stmt = $db->prepare("UPDATE Accounts set account_number=:account_number, account_type=:account_type, balance=:balance, user_id=:user where id=:id);
	$r = $stmt->execute([
		":account_number"=>$account_number,
		":account_type"=>$account_type,
		":balance"=$balance,
		":user_id"=$user, 
		":id"=>$id
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
<?php require(__DIR__ . "/partials/flash.php");
