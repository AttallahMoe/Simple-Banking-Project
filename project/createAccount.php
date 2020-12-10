<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<h1><strong>Create Checking Account</strong></h1>
<form method="POST">
    <input type="number" name="balance" min="5.00" placeholder="Starting Balance"/>
    <input type="submit" name="save" value="Create"/>
</form>

<div class="list-group">
    <div>
        <a href="createSavingsAccount.php">Create Savings Account</a>
        <a href="createLoanAccount.php">Create Loan</a>
    </div>

<?php

if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$check = true;

if(isset($_POST["save"])){



    //$check = true;
    $worldBalance = 0;

    $accNumRec = new SplFixedArray(1);
    $balance = $_POST["balance"];
    $account_type = "checking";

    //creating and storing account number
    $db = getDB();
    //$accNum = rand(10000000000,99999999999);
    $accNum = $db->lastInsertId();
    $accNumPadded = str_pad($accNum, 12, "0", STR_PAD_LEFT);
    $accNumRec[0] = $accNum;
    $accNumFinal = $accNumRec[0];
    $accNumFinal = (int)$accNumFinal;

    $user = get_user_id();

    $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, balance, user_id) VALUES(:account_number, :account_type, :balance, :user)");
    $r = $stmt->execute([
            ":account_number" => $accNumPadded,
            ":account_type" => $account_type,
            ":balance" => $balance,
            ":user" => $user
    ]);
    if($r){
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else{
        $e = $stmt->errorInfo();
        flash("Error creating new Checking Account: " . var_export($e, true));
        $check = false;
    }
?>
<?php
    if($check){

        //Updating balance for world account

        $worldID = 1;
        $db = getDB();

        $stmt = $db->prepare("SELECT balance from Accounts WHERE id = :id");
        $r = $stmt->execute([":id" => $worldID]);
        $worldBalance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Error accessing the World Account balance: " . var_export($e, true));
            $check = false;
        }

        $worldBalance = (int)$worldBalance;
        $balance = (int)$balance;

        $updateWorldBalance = $worldBalance - $balance;

        $stmt = $db->prepare("UPDATE Accounts set balance=:updateWorldBalance WHERE id=:id");
        $r = $stmt->execute([
           ":updateWorldBalance" => $updateWorldBalance,
            ":id" => $worldID
        ]);

        if(!$r){
            $e = $stmt->errorInfo();
            flash("Error updating World balance: " . var_export($e, true));
            $check = false;
        }

        //creating transaction between new account and world account

        if($check){
            $action_type = "deposit";
            $memo = "N.A.C";
            $worldAmount = $balance * -1;
            $result = [];


            $stmt = $db->prepare("SELECT id from Accounts WHERE account_number =:accNum");
            $r = $stmt->execute([":accNum" => $accNumFinal]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $sourceID = $result["id"];


            //$sourceID = $db->lastInsertId();

            if(!$r){
                $e = $stmt->errorInfo();
                flash("Error getting Account ID: " . var_export($e, true));
                $check = false;
            }
            if($check) {
                $stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, action_type, amount, memo, expected_total) VALUES(:src, :dest, :type, :amount,:memo, :expected)");
                $r = $stmt->execute([
                    ":src" => $worldID,
                    ":dest" => $sourceID,
                    ":type" => $action_type,
                    ":amount" => $worldAmount,
                    ":memo" => $memo,
                    ":expected" => $updateWorldBalance
                ]);
                if (!$r) {
                    $e = $stmt->errorInfo();
                    flash("Failed to process transaction for World Account: " . var_export($e, true));
                    $check = false;
                }
            }

            if($check) {
                $stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, action_type, amount, memo, expected_total) VALUES(:src, :dest, :type, :amount,:memo, :expected)");
                $r = $stmt->execute([
                    ":src" => $sourceID,
                    ":dest" => $worldID,
                    ":type" => $action_type,
                    ":amount" => $balance,
                    ":memo" => $memo,
                    ":expected" => $balance
                ]);
                if (!$r) {
                    $e = $stmt->errorInfo();
                    flash("Failed to process transaction for Source Account: " . var_export($e, true));
                    $check = false;
                }
            }
        }
    }

    header("Location: home.php");


}

?>
<?php require(__DIR__ . "/partials/flash.php");?>


