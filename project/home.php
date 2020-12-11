<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
}
?>
<p>Welcome, <?php echo $email; ?></p>

<div class="list-group">
    <div>
        <a href="createAccount.php">Create Account</a>
        <a href="viewAccount.php">View Accounts</a>
        <a href="createTransaction.php">Personal Transaction</a>
        <a href="createPersonalTransfer.php">Personal Transfer</a>
        <a href="createExternalTransfer.php">External Transfer</a>
    </div>
</div>

<?php require(__DIR__ . "/partials/flash.php"); ?>
