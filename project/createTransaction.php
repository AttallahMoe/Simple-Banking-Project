<?php
require_once (__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}
$db = getDB();
$user = get_user_id();
$closedCheck = 'true';
$stmt = $db->prepare("SELECT account_number from Accounts WHERE user_id=:id  AND closed !=:closed LIMIT 10");
$r = $stmt->execute([":id" => $user, "closed" => $closedCheck]);
$accs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Create Transaction</h3>
<form method = "POST">
    <label>Choose Account</label>
    <select name="account_source" placeholder="Account Source">
        <?php foreach ($accs as $acc): ?>
            <option value="<?php safer_echo($acc["account_number"]); ?>"
            ><?php safer_echo($acc["account_number"]); ?></option>
        <?php endforeach; ?>
    </select>
    <label>Transaction Type</label>
    <select name="action_type">
        <option value="deposit">Deposit</option>
        <option value="withdraw">Withdraw</option>
    </select>
    <input type="number" min="0.00" placeholder="Amount" name="amount"/>
    <input type="text" placeholder= "Memo" name="memo"/>
    <input type="submit" name="save" value="Create"/>
</form>

<?php
$worldID = 1;
$check = true;
if(isset($_POST["save"])){
    $src = $_POST["account_source"];
    $type = $_POST["action_type"];
    $amount = $_POST["amount"];
    $memo = $_POST["memo"];
    $db = getDB();

    //database variable setters
    $srcBalance = 0;
    $srcAmount = 0;
    $srcExpect = 0;

    $worldBalance = 0;
    $worldAmount = 0;
    $worldExpect = 0;

    //source account balance
    $results = [];
    $stmt = $db->prepare("SELECT id, balance from Accounts WHERE account_number=:src");
    $r = $stmt->execute([":src" => $src]);
    $results = $stmt->fetch(PDO::FETCH_ASSOC);

    $srcBalance = $results["balance"];
    $srcID = $results["id"];


    if (!$r) {
        $e = $stmt->errorInfo();
        flash("Error accessing the Source Account Balance: " . var_export($e, true));
        $check = false;
    }

    //world account balance

    $stmt = $db->prepare("SELECT balance from Accounts WHERE id=:id");
    $r = $stmt->execute([":id" => $worldID]);
    $worldBalance = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        $e = $stmt->errorInfo();
        flash("Error accessing the World Account Balance: " . var_export($e, true));
        $check = false;
    }

    $amount = (int)$amount;
    $srcBalance = (int)$srcBalance;
    $worldBalance = (int)$worldBalance;

    //checking to see if action_type is withdraw or deposit

    if($check){
        if($type == "withdraw"){
            if($amount > $srcBalance){
                $check = false;
                flash("Please enter valid amount to withdraw");
            }
            else{
                $srcExpect = $srcBalance - $amount;
                $srcAmount = $amount * -1;

                $worldExpect = $worldBalance + $amount;
                $worldAmount = $amount;
            }
        }
        elseif ($type == "deposit"){
            $srcExpect = $srcBalance + $amount;
            $srcAmount = $amount;

            $worldExpect = $worldBalance - $amount;
            $worldAmount = $amount * -1;
        }
    }

    if($check){
        $stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, amount, action_type, memo, expected_total) VALUES(:src, :dest,:amount, :type, :memo, :expected)");
        $r = $stmt->execute([
            ":src" => $srcID,
            ":dest" => $worldID,
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
        $stmt = $db->prepare("INSERT INTO Transactions (act_src_id, act_dest_id, action_type, amount, memo, expected_total) VALUES(:src, :dest, :type, :amount,:memo, :expected)");
        $r = $stmt->execute([
            ":src" => $worldID,
            ":dest" => $srcID,
            ":type" => $type,
            ":amount" => $worldAmount,
            ":memo" => $memo,
            ":expected" => $worldExpect
        ]);
        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Failed to process transaction for World Account: " . var_export($e, true));
            $check = false;
        }
    }

    //updating world and source balances

    if($check){
        $worldBalanceSum = [];
        $stmt = $db->prepare("SELECT SUM(amount) as total from Transactions WHERE act_src_id=:id");
        $r = $stmt->execute([":id" => $worldID]);
        $worldBalanceSum = $stmt->fetch(PDO::FETCH_ASSOC);
        $worldBalFinal = $worldBalanceSum["total"];

        $srcBalanceSum = [];
        $stmt = $db->prepare("SELECT SUM(amount) as total from Transactions WHERE act_src_id=:id");
        $r = $stmt->execute([":id" => $srcID]);
        $srcBalanceSum = $stmt->fetch(PDO::FETCH_ASSOC);
        $srcBalFinal = $srcBalanceSum["total"];

        //world
        $stmt = $db->prepare("UPDATE Accounts set balance=:worldUpdate WHERE id=:id");
        $r = $stmt->execute([
            ":worldUpdate" => $worldBalFinal,
            ":id" => $worldID
        ]);

        if(!$r){
            $e = $stmt->errorInfo();
            flash("Error updating World balance: " . var_export($e, true));
            $check = false;
        }

        //source
        $stmt = $db->prepare("UPDATE Accounts set balance=:srcUpdate WHERE id=:id");
        $r = $stmt->execute([
            ":srcUpdate" => $srcBalFinal,
            ":id" => $srcID
        ]);

        if(!$r){
            $e = $stmt->errorInfo();
            flash("Error updating Source balance: " . var_export($e, true));
            $check = false;
        }
    }
    if($check){
        flash("Transaction processed successfully!");
    }

}
require(__DIR__ . "/partials/flash.php");
?>