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
	$srcTrans = $_GET["id"];
	$destTrans = $srcTrans + 1;
}

else{
	flash("Enter ID into address");
}


$db = getDb();

//Fetching the source side of the transaction
$srcResult = [];
if(isset($srcTrans)){
    $stmt = $db->prepare("SELECT * FROM Transactions WHERE id = :id");
    $r = $stmt->execute([":id" => $srcTrans]);
    $srcResult = $stmt->fetch(PDO::FETCH_ASSOC);
}

//Fetching the destination side of the transaction
$destResult = [];
if(isset($destTrans)){
    $stmt = $db->prepare("SELECT * FROM Transactions WHERE id = :id");
    $r = $stmt->execute([":id"=>$destTrans]);
    $destResult = $stmt->fetch(PDO::FETCH_ASSOC);
}

//Bool to check if the two results are the correct pair
$transactionPaired = false;

//Check to make sure the transactions are a pair
if($srcResult["act_src_id"] == $destResult["act_dest_id"] && $srcResult["act_dest_id"] == $destResult["act_src_id"] && $srcResult["amount"] == $destResult["amount"] * -1) {
    $transactionPaired = true;
} else{
    //Attempt to check the other side of the transID to find the pair.
    $destTrans = $srcTrans - 1;
    $stmt = $db->prepare("SELECT * FROM Transactions WHERE id = :id");
    $r = $stmt->execute([":id"=>$destTrans]);
    $destResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if($srcResult["act_src_id"] == $destResult["act_dest_id"] && $srcResult["act_dest_id"] == $destResult["act_src_id"] && $srcResult["amount"] == $destResult["amount"] * -1) {
        $transactionPaired = true;
    } else{
        $e = $stmt->errorInfo();
        flash("There was an error finding the pair for this transaction " . var_export($e, true));
    }
}


if(isset($_POST["edit"]) && $transactionPaired) {
    //TODO Proper Validations
    $memo = $_POST["memo"];
    $amount = $_POST["amount"];

    //Initializing Variables for source and destination accounts
    $srcExpect = $srcResult["expected_total"];
    $srcAmt = $srcResult["amount"];

    $destExpect = $destResult["expected_total"];
    $destAmt = $destResult["amount"];

    //Set of if statements to set expected_balance according to actionType
    if ($srcResult["action_type"] == "deposit") {
        $srcExpect += $srcAmt;      //Adds the old amount back to get the balance
        $srcExpect -= $amount;      //Subs the new amount
        $srcAmt = $amount * -1;     //Sets the amount so that it reflects the new action

        $destExpect -= $destAmt;    //Subs the old amount back to get the balance
        $destExpect += $amount;     //Adds the new amount
        $destAmt = $amount;         //Sets the amount so that it reflects the new action
    } elseif ($srcResult["action_type"] == "withdrawal") {
        $srcExpect -= $srcAmt;      //Subs the old amount back to get the balance
        $srcExpect += $amount;      //Adds the new amount
        $srcAmt = $amount;          //Sets the amount so that it reflects the new action

        $destExpect += $destAmt;    //Adds the old amount back to get the balance
        $destExpect -= $amount;     //Subs the new amount
        $destAmt = $amount * -1;    //Sets the amount so that it reflects the new action
    } elseif ($srcResult["action_type"] == "transfer") {
        $srcExpect += $srcAmt;      //Adds the old amount back to get the balance
        $srcExpect -= $amount;      //Subs the new amount
        $srcAmt = $amount * -1;     //Sets the amount so that it reflects the new action

        $destExpect -= $destAmt;    //Subs the old amount back to get the balance
        $destExpect += $amount;     //Adds the new amount
        $destAmt = $amount;         //Sets the amount so that it reflects the new action
    }

    //Bool to double check to make sure that things update properly
    //I.E One updates and the other doesn't
    $continue = true;
    //Update the Source Side of the transaction
    if($continue) {
        $stmt = $db->prepare("UPDATE Transactions set memo=:memo, amount=:amount, expected_total=:expTot WHERE id=:id");
        $r = $stmt->execute([
            ":memo" => $memo,
            ":amount" => $srcAmt,
            ":expTot" => $srcExpect,
            ":id" => $srcTrans
        ]);
        if ($r) {
            flash("Successfully updated source side.");
        } else {
            $e = $stmt->errorInfo();
            flash("There was an error updating the source side! " . var_export($e, true));
            $continue = false;
        }
    }

    //Updating destination side
    if($continue) {
        $stmt = $db->prepare("UPDATE Transactions set memo=:memo, amount=:amount, expected_total=:expTot WHERE id=:id");
        $r = $stmt->execute([
            ":memo" => $memo,
            ":amount" => $destAmt,
            ":expTot" => $destExpect,
            ":id" => $destTrans
        ]);
        if ($r) {
            flash("Successfully updated destination side.");
        } else {
            $e = $stmt->errorInfo();
            flash("There was an error updating the destination side! " . var_export($e, true));
            $continue = false;
        }
    }
    if($continue){
        flash("Both sides of the transaction have been updated!");
    }
}


?>
<div class="bodyMain">
    <h3>Edit a Transaction</h3>

    <form method="POST">
        <label>Memo <br>
            <input name="memo" type="text" value="<?php echo $srcResult["memo"];?>"> <br><br>
        </label>
        <p>Transaction Type: <br>
            <?php echo $srcResult["action_type"];?>
        </p><br><br>
        <p>Source Account: <br>
            <?php echo $srcResult["act_src_id"];?>
        </p> <br><br>
        <p>Destination Account: <br>
            <?php echo $srcResult["act_dest_id"];?>
        </p> <br><br>
        <label>Transaction Amount<br>
            <input name="amount" type="number" min = "0" placeholder="00.00" value="<?php echo $srcResult["amount"];?>"><br><br>
        </label> <br><br>
        <input type="submit" name="edit" value="Update">

    </form>

<?php require(__DIR__ . "/partials/flash.php");?>
