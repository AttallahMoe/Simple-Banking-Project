<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
if(!has_role("Admin")){
	flash("You don't have permission to access this page");
	die(header("Location: login.php"));
}
?>

<?php
if(isset($_GET["id"])){
	$id = $_GET["id"];
}
?>

<?php
if(isset($_POST["save"])){
	$account_number = $_POST["account_number"];
	$account_type = $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	if(isset($id)){
		$stmt = db->prepare("UPDATE Accounts set account_number=:account_number, account_type=:account_type, balance=:balance, user=:user_id where id=:id");
		$r = $stmt->execute([
                	":account_number"=>$account_number,
                	":account_type"=>$account_type,
                	":balance"=>$balance,
                	":user_id"=>$user,
                	":id"=>$id
       		]);
			
		if($r){
			flash("Updated successfully with id: " . $id);
		}
		else{
			$e = $stmt->errorInfo();
			flash("Error updating: " . var_export($e, true));
		}	
	}	
	else{
		flash("ID isn't set, we need an ID in order to update");
	}
}
?>
<?php
//fetching
$result = [];
if(isset($id)){
	$id = $_GET["id"];
	$db = getDB();
	$stmt = $db->prepare("SELECT * FROM Accounts where id = :id");
	$r = $stmt->execute([":id"=>$id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<form method="POST">
                <input name="account_number" placeholder="Account Number" value="<?php echo $result["account_number"];?>"/>
                <label>Account Type</label>
                <select name="account_type" value="<?php echo $result["account_type"];?>">
                                <option value="0" <?php echo ($result["account_type"] == "0"?'selected="selected"':'');?>>Checking</option>
                                <option value="1" <?php echo ($result["account_type"] == "1"?'selected="selected"':'');?>>Savings</option>
                                <option value="2" <?php echo ($result["account_type"] == "2"?'selected="selected"':'');?>>Loan</option>
                </select>
                <input type="number" name="balance" value="<?php echo $result["balance"];?>" placeholder="Balance"/>
                <input type="submit" name="save" value="Update"/>
</form>

<?php require(__DIR__ . "/partials/flash.php");

