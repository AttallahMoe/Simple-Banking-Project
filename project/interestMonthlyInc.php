<?php
require_once (__DIR__ . "/partials/nav.php");

$check = true;
$db = getDB();
$type1 = "loan";
$type2 = "saving";
$stmt = $db->prepare("SELECT account_number from Accounts WHERE account_type=:type1 OR account_type=:type2 LIMIT 10");
$r = $stmt->execute([":type1" => $type1,
                     ":type2" => $type2
                    ]);
$accs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$r) {
    $e = $stmt->errorInfo();
    flash("Error accessing accounts: " . var_export($e, true));
    $check = false;
}
?>

<form method="POST">
    <h1><strong>Interest implementer</strong></h1>
    <label>Choose Account</label>
    <select name="account_source" placeholder="Account Source">
        <?php foreach ($accs as $acc): ?>
            <option value="<?php safer_echo($acc["account_number"]); ?>"
            ><?php safer_echo($acc["account_number"]); ?></option>
        <?php endforeach; ?>
    </select>
    <label>Interest nuke button</label>
    <input type="submit" value="Nuke" name="save"/>
</form>



<?php
if (!has_role("Admin")) {
//this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}

if(isset($_POST["save"]) && $check == true){

    $memo = "Interest";
    $account = $_POST["account_source"];

    $loanBalance = 0;
    $loanInterest = 0;
    $loanExpect = 0;

    $results = [];
    $stmt = $db->prepare("SELECT id, balance, apy from Accounts WHERE account_number=:src");
    $r = $stmt->execute([":src" => $account]);
    $results = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        $e = $stmt->errorInfo();
        flash("Error accessing the Source Account Balance: " . var_export($e, true));
        $check = false;
    }

    $loanBalance = $results["balance"];
    $loanID = $results["id"];
    $loanInterest = $results["apy"];

    $loanInterest = (float)$loanInterest;
    $loanBalance = (int)$loanBalance;

    $interestCalc = $loanBalance * $loanInterest;
    $loanExpect = $loanBalance + $interestCalc;

    if($check){
        $stmt = $db->prepare("UPDATE Accounts set balance=:loanBalance WHERE id=:id");
        $r = $stmt->execute([
            ":loanBalance" => $loanExpect,
            ":id" => $loanID
        ]);
        if (!$r) {
            $e = $stmt->errorInfo();
            flash("Failed to update loan balance to include interest: " . var_export($e, true));
            $check = false;
        }
    }
}




require(__DIR__ . "/partials/flash.php");
?>