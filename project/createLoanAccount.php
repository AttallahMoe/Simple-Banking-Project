<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//created loan account
$db = getDB();
$user = get_user_id();
$stmt = $db->prepare("SELECT account_number from Accounts WHERE user_id=:id LIMIT 10");
$r = $stmt->execute([":id" => $user]);
$accs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Create Loan</h3>
<form method = "POST">
    <label>Choose Destination Account</label>
    <select name="account_source" placeholder="Account Source">
        <?php foreach ($accs as $acc): ?>
            <option value="<?php safer_echo($acc["account_number"]); ?>"
            ><?php safer_echo($acc["account_number"]); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" name="balance" min="500.00" placeholder="Loan Amount"/>
    <input type="number" name="apy" min="2" max="5" placeholder="APY"/>
    <input type="submit" name="save" value="Create"/>
</form>

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
    $account_type = "loan";

    $apy = $_POST["apy"];
    $apy = (int)$apy;
    $apy2 = $apy/100;

    $externalAccount = $_POST["account_source"]; //must get id from account number

    //getting external account info for loan to be deposited


    $resultsExternal = [];
    $stmt = $db->prepare("SELECT id, balance from Accounts WHERE account_number=:src");
    $r = $stmt->execute([":src" => $externalAccount]);
    $resultsExternal = $stmt->fetch(PDO::FETCH_ASSOC);

    $srcExternalBalance = $resultsExternal["balance"];
    $srcIDExternal = $resultsExternal["id"];


    if (!$r) {
        $e = $stmt->errorInfo();
        flash("Error accessing the Source Account Balance: " . var_export($e, true));
        $check = false;
    }

    //creating and storing account number

    $accNum = rand(100000000000,999999999999);
    $accNumRec[0] = $accNum;
    $accNumFinal = $accNumRec[0];
    $accNumFinal = (int)$accNumFinal;

    $user = get_user_id();
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO Accounts (account_number, account_type, balance, apy, user_id) VALUES(:account_number, :account_type, :balance, :apy, :user)");
    $r = $stmt->execute([
        ":account_number" => $accNumFinal,
        ":account_type" => $account_type,
        ":balance" => $balance,
        ":apy" => $apy2,
        ":user" => $user
    ]);
    if($r){
        flash("Created successfully with id: " . $db->lastInsertId());
    }
    else{
        $e = $stmt->errorInfo();
        flash("Error creating new Loan Account: " . var_export($e, true));
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

        $updateWorldBalance = $worldBalance - ($balance);

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

        //creating transaction between new loan account and world account

        if($check){
            $action_type = "loan";
            $memo = "N.L.A.C";
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

            $memoLoan = "loan fund";
            $srcExternalBalance = (int)$srcExternalBalance;

            $srcExternalExpected = $srcExternalBalance + ($balance);

            $stmt = $db->prepare("UPDATE Accounts set balance=:srcExternalExpected WHERE id=:id");
            $r = $stmt->execute([
                ":srcExternalExpected" => $srcExternalExpected,
                ":id" => $srcIDExternal
            ]);

            //creating transaction between loan account and source destination
            if($check) {
                $stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, action_type, amount, memo, expected_total) VALUES(:src, :dest, :type, :amount,:memo, :expected)");
                $r = $stmt->execute([
                    ":src" => $sourceID,
                    ":dest" => $srcIDExternal,
                    ":type" => $action_type,
                    ":amount" => $balance,
                    ":memo" => $memoLoan,
                    ":expected" => $srcExternalExpected
                ]);
                if (!$r) {
                    $e = $stmt->errorInfo();
                    flash("Failed to process transaction for Source Account: " . var_export($e, true));
                    $check = false;
                }
            }

        }
        header("Location: viewAccount.php");
    }



}
?>
<?php require(__DIR__ . "/partials/flash.php");?>
