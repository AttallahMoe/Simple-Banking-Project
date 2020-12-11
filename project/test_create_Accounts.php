<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if(!has_role("Admin")){
	flash("You don't have permission to access this page");
	die(header("Location: login.php"));
}
$db = getDB();
$stmt = $db->prepare("SELECT id, email from Users");
$r = $stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<form method="POST">
        <label>Enter New Account Number</label>
		<input name="account_number" placeholder="Account Number"/>
		<label>Account Type</label>
        <br>
		<select name="account_type">
				<option value="checking">Checking</option>
				<option value="saving">Saving</option>
				<option value="loan">Loan</option>
		</select>
        <br>
        <label>Users email to create account for:</label>
        <br>
        <select name="account_source" placeholder="Account Source">
            <?php foreach ($users as $user): ?>
                <option value="<?php safer_echo($user["id"]); ?>"
                ><?php safer_echo($user["email"]); ?></option>
        <?php endforeach; ?>
        </select>
        <br>

		<input type="number" name="balance" value="balance" placeholder="Balance"/>
		<input type="submit" name="save" value="Create"/>
</form>
		
<?php
if(isset($_POST["save"])){
	$account_number = $_POST["account_number"];
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];

	$user = $_POST["account_source"];
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
