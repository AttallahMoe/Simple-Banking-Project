<?php
require_once (__DIR__ . "/partials/nav.php");

if (!is_logged_in()) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You must be logged in to access this page");
    die(header("Location: login.php"));
}

$check = true;
$results = [];
$results1 = [];

if(isset($_GET["id"])){
    $transId = $_GET["id"];
}
else{
    $check = false;
    flash("Id is not set in url");
}

$page = 1;

if(isset($_GET["page"])) {
    try {
        $page = (int)$_GET["page"];
    }
    catch (Exception $e) {

    }
}

//TODO Fix this so that it returns actual account numbers in the query, not the internal id. Fixed!!!
if($check) {
    $db = getDB();

    //TODO pageination
    $numPerPage = 1;
    $numRecords = 0;
    $resultPage = [];

    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM Transactions WHERE act_src_id=:id");
    $r = $stmt->execute([":id" => $transId]);
    $resultPage = $stmt->fetch(PDO::FETCH_ASSOC);
    if($resultPage){
        $numRecords = (int)$resultPage["total"];
    }

    $numRecords = (int)$numRecords;
    echo $numRecords;
    $numLinks = ceil($numRecords/$numPerPage); //gets number of links to be created
    $offset = ($page-1) * $numPerPage;

    $stmt = $db->prepare("SELECT act_src_id, Accounts.id, Accounts.account_number, amount, action_type, memo FROM Transactions JOIN Accounts on Accounts.id = Transactions.act_dest_id WHERE act_src_id =:id LIMIT 10");
    $r = $stmt->execute([":id" => $transId]);
    if ($r){
        $results1 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else{
        $e = $stmt->errorInfo();
        flash("There was a problem fetching the results." . var_export($e, true));
        $check = false;
    }

}

?>


<form method="POST">
    <label><strong>Filter Transactions</strong></label>
    <br>
    <label>START:<br></label>
    <input type="date" name="dateStart" />
    <br>
    <label>END:<br></label>
    <input type="date" name="dateTo"/>
    <label>Transaction Type: <br></label>
    <select name="action_type">
        <option value="deposit">Deposit</option>
        <option value="withdraw">Withdraw</option>
        <option value="transfer">Transfer</option>
    </select>
    <input type="submit" name="save" value="Filter" />
</form>
<?php
/*
<div class="bodyMain">
    <h1><strong>List Transactions</strong></h1>

    <div class="results">
        <?php if(count($results) > 0 && !isset($_POST["save"])): ?>
            <div class="list-group">
                <?php foreach ($results1 as $r): ?>
                    <div class="list-group-item">
                        <div>
                            <div>Destination Account ID:</div>
                            <div><?php safer_echo($r["account_number"]); ?></div>
                        </div>
                        <div>
                            <div>Transaction Type:</div>
                            <div><?php safer_echo($r["action_type"]); ?></div>
                        </div>
                        <div>
                            <div>Amount Moved:</div>
                            <div><?php safer_echo($r["amount"]); ?></div>
                        </div>
                        <div>
                            <div>Memo:</div>
                            <div><?php safer_echo($r["memo"]); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="display:none"></div>
        <?php endif; ?>
    </div>

*/
    ?>
<?php
    if(isset($_POST["save"])){
        $startDate = $_POST["dateStart"];
        $endDate = $_POST["dateTo"];
        $type = $_POST["action_type"];

        //$stmt->bindValue(":memo", $memo, PDO::PARAM_STR);

        $startDate = (String)$startDate . ' 00:00:00';
        $endDate = (String)$endDate . ' 00:00:00';

        $stmt = $db->prepare("SELECT act_src_id, Accounts.id, Accounts.account_number, amount, action_type, memo FROM Transactions JOIN Accounts on Accounts.id = Transactions.act_src_id WHERE act_src_id =:id AND action_type=:action_type AND created BETWEEN :startDate AND :endDate LIMIT :offset, :count");
        $stmt->bindValue(":startDate", $startDate, PDO::PARAM_STR);
        $stmt->bindValue(":endDate", $endDate, PDO::PARAM_STR);
        $stmt->bindValue(":action_type", $type, PDO::PARAM_STR);
        $stmt->bindValue(":id", $transId, PDO::PARAM_INT);
        $stmt->bindValue(":offset", $offset, PDO::PARAM_INT);
        $stmt->bindValue(":count", $numPerPage, PDO::PARAM_INT);
        $r = $stmt->execute();
        if ($r){
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        else{
            $e = $stmt->errorInfo();
            flash("There was a problem fetching the results." . var_export($e, true));
            $check = false;
        }


    }


?>

    <div class="BodyMain">

        <div class="results">
            <h1><strong>Filtered Transactions</strong></h1>
            <?php if(count($results) > 0): ?>
                <div class="list-group">
                    <?php foreach ($results as $r): ?>
                        <div class="list-group-item">
                            <div>
                                <div>Destination Account ID:</div>
                                <div><?php safer_echo($r["account_number"]); ?></div>
                            </div>
                            <div>
                                <div>Transaction Type:</div>
                                <div><?php safer_echo($r["action_type"]); ?></div>
                            </div>
                            <div>
                                <div>Amount Moved:</div>
                                <div><?php safer_echo($r["amount"]); ?></div>
                            </div>
                            <div>
                                <div>Memo:</div>
                                <div><?php safer_echo($r["memo"]); ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No Results</p>
            <?php endif; ?>
        </div>
    </div>
<div>
<nav aria-label="Filtered">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
            <a class="page-link" href="?id=<?php echo $transId;?>&page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
        </li>
        <?php for($i = 0; $i < $numLinks; $i++):?>
            <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?id=<?php echo $transId;?>&page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
        <?php endfor; ?>
        <li class="page-item <?php echo ($page+1) >= $numLinks?"disabled":"";?>">
            <a class="page-link" href="?id=<?php echo $transId;?>&page=<?php echo $page+1;?>">Next</a>
        </li>
    </ul>
</nav>
</div>

<?php require(__DIR__ . "/partials/flash.php");?>