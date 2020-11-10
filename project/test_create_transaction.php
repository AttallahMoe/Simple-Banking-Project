<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}

$db = getDB();
$stmt = $db->prepare("SELECT id,account_number from Accounts LIMIT 10");
$r = $stmt->execute();
$accs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

?>
	<h3>Create Transaction</h3>
	<form method = "POST">
		<label>Source    </label>
		<select name="account_source" placeholder="Account Source">
            		<?php foreach ($accs as $acc): ?>
                	<option value="<?php safer_echo($acc["id"]); ?>" 
                	><?php safer_echo($acc["account_number"]); ?></option>
            		<?php endforeach; ?>
        	</select>
		<label>Destination</label>
		<select name="account_dest" placeholder = "Account Destination">
                        <?php foreach ($accs as $acc): ?>
                        <option value="<?php safer_echo($acc["id"]); ?>" 
                        ><?php safer_echo($acc["account_number"]); ?></option>
                        <?php endforeach; ?>
                </select>
                <label>Transaction Type</label>
		<select name="action_type">
				<option value="deposit">Deposit</option>
				<option value="withdraw">Withdraw</option>
				<option value="transfer">Transfer</option>
		</select>
		<input type="number" placeholder="Amount" name="amount"/>
		<input type="text" placeholder= "Memo" name="memo"/>
		<input type="submit" name="save" value="Create"/>
	</form>

<?php
if(isset($_POST["save"])){
	$src = $_POST["account_source"];
	$dest = $_POST["account_dest"];
	$type = $_POST["action_type"];
	$amount = $_POST["amount"];
	$memo = $_POST["memo"];
	$db = getDB();


	//database variable setters
	$srcBalance = 0;
	$srcAmount = 0;
	$srcExpect = 0;

	$destBalance = 0;
	$destAmount = 0;
	$destExpect = 0;
	
	$check = true;
	
	//Source account
	if ($check) {
            $stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :srcAcct");
            $r = $stmt->execute([":srcAcct" => $src]);
            $srcBalance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error accessing the Source Account: " . var_export($e, true));
                $check = false;
            }
        }	
	
	//Dest account
	if ($failsafe) {
            $stmt = $db->prepare("SELECT balance FROM Accounts WHERE id = :destAcct");
            $r = $stmt->execute([":destAcct" => $dest]);
            $destBalance = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$r) {
                $e = $stmt->errorInfo();
                flash("Error accessing the Destination Account: " . var_export($e, true));
                $failsafe = false;
            }
        }
	
	$srcBalance = (int)$srcBalance;
	$destBalance = (int)$destBalance;
	$amount = (int)$amount;
	
	if($type == "deposit"){
		$srcExpect = $srcBalance - $amount;
		$srcAmount = $amount * -1;

		$destExpect = $destBalance + $amount;
		$destAmount = $amount;
	}
	elseif ($type == "withdraw"){
		$srcExpect = $srcBalance + $amount;
		$srcAmount = $amount;
		
		$destExpect = $destBalance - $amount;
		$destAmount = $amount * -1;
	}
	elseif ($type == "transfer"){
		$srcExpect = $srcBalance - $amount;
		$srcAmount = $amount * -1;
		
		$destExpect = $destBalance - $amount;
		$destAmount = $amount;
	}
if($check){
	$stmt = $db->prepare("INSERT INTO Transactions (account_src_id, account_dest_id, action_type, amount, memo, expected_total) VALUES(:src, :dest:, :type, :amount,:memo, :expected)");
	$r = $stmt->execute([
		":src" => $src,
		":dest" => $dest,
		":type" => $type,
		":amount" => $srcAmount,
		":memo" => $memo,
		":expected" => $srcExpect
	]);
	if (!$r) {
		$e = $stmt->errorInfo();
        	flash("Failed to write transaction for Source Account: " . var_export($e, true));
		$check = false;
    	}
}

if($check){
        $stmt = $db->prepare("INSERT INTO Transactions (account_src_id, account_dest_id, action_type, amount, memo, expected_total) VALUES(:src, :dest:, :type, :amount,:memo, :expected)");
        $r = $stmt->execute([
                ":src" => $dest,
		":dest" => $src,
                ":type" => $type,
                ":amount" => $destAmount,
                ":memo" => $memo,
                ":expected" => $destExpect
        ]);
        if (!$r) {
                $e = $stmt->errorInfo();
                flash("Failed to process transaction for Destination Account: " . var_export($e, true));
                $check = false;
        }
}
	if($r){
		flash("Transaction processed successfully!");
	}
	
}
?>
<?php require(__DIR__ . "/partials/flash.php");	
