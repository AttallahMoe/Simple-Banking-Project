<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//since this function call is included we can omit it here. Having multiple calls to session_start() will cause errors/warnings
//session_start();
// remove all session variables
session_unset();
// destroy the session
session_destroy();
?>
<?php require_once(__DIR__ . "/partials/nav.php");/*ultimately, this is just here for the function to be loaded now*/ ?>
<?php
//flash("You have been logged out");
die(header("Location: login.php"));
?>

echo "You have been logged out<br>";
