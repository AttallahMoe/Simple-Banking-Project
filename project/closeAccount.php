<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//added close account
$db = getDB();
$user = get_user_id();
$closedCheck = "true";

$stmt = $db->prepare("SELECT account_number from Accounts WHERE user_id=:id AND closed != :bool LIMIT 10");
$r = $stmt->execute([":id" => $user,
                     ":bool" => $closedCheck
                    ]);
$accs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h3>Close Account</h3>
<form method = "POST">
    <label>Choose Account to Close:</label>
    <select name="account_source" placeholder="Account Source">
        <?php foreach ($accs as $acc): ?>
            <option value="<?php safer_echo($acc["account_number"]); ?>"
            ><?php safer_echo($acc["account_number"]); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" name="save" value="Close"/>

<?php

if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$check = true;

if (isset($_POST["save"])){
    $closeAcc = $_POST["account_source"];

    $results = [];

    $stmt = $db->prepare("SELECT id, balance from Accounts WHERE account_number=:src");
    $r = $stmt->execute([":src" => $closeAcc]);
    $results = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$r) {
        $e = $stmt->errorInfo();
        flash("Error accessing the Source Account Balance: " . var_export($e, true));
        $check = false;
    }

    $balance = $results["balance"];
    $sourceID =  $results["id"];

    if($check) {
        if ($balance != 0) {
            flash("Balance must be $0.00 to close your account.");
            $check = false;
        }
    }

    $closed = "true";

    if($check){
        $stmt = $db->prepare("UPDATE Accounts set closed=:closeAcc WHERE id=:id");
        $r = $stmt->execute([
            ":closeAcc" => $closed,
            ":id" => $sourceID
        ]);

        if(!$r){
            $e = $stmt->errorInfo();
            flash("Error closing the account: " . var_export($e, true));
            $check = false;
        }
    }

    if($check){
        flash("Your Account has successfully been closed!.");
    }
}
?>
    <?php require(__DIR__ . "/partials/flash.php");?>
