<?php
$stmtgettransactions = $this->db->prepare('
SELECT t.*, i.name AS item_name
FROM transactions t
JOIN items i ON t.asset = i.id
WHERE t.issuer = ? OR t.receiver = ?
ORDER BY t.time DESC
');
$stmtgettransactions->execute([$this->user_info["id"], $this->user_info["id"]]);
$transactions = $stmtgettransactions->fetchAll(PDO::FETCH_ASSOC);
?>
<h3>Transaction log</h3>
(times in UTC)
<div class="border" style="height:50%;justify-content: flex-start">
    <table style="height:50vh;">
        <tr>
            <th>User</th>
            <th>Bought</th>
            <th>From</th>
            <th>Paid</th>
            <th>At</th>
        </tr>
        <?php
        if (count($transactions) > 0) {
            foreach ($transactions as $row) {
                $id = htmlspecialchars($row['asset']);
                $issuer = htmlspecialchars($row['issuer']);
                $receiver = htmlspecialchars($row['receiver']);
                $amount = htmlspecialchars($row['amount']);
                $time = htmlspecialchars($row['time']);
                $itemname = htmlspecialchars($row['item_name']);

                $issuer_username = $this->getuser($issuer)["username"];
                $receiver_username = $this->getuser($receiver)["username"];
                ?>
        <tr>
            <td><a href="/social/profile?id=<?=$issuer?>" ><?=$issuer_username?></a></td>
            <td><a href="/asset/item?id=<?=$id;?>" title="check affected content"><?=$itemname;?></a></td>
            <td><a href="/social/profile?id=<?=$receiver;?>" ><?=$receiver_username?></a></td>
            <td>¥<?=$amount; ?></td>
            <td><span title="<?=date('Y-d-m H:i:s', $time); ?>"><?=date('Y-d-m', $time); ?></span></td>
        </tr>
        <?php }
        } else {
            echo 'No transactions found.';
        } ?>
    </table>
</div>
<a href="/social/leaderboard">Leaderboards</a>
<a href="/casino">Casino</a>
<?php $this->insertAd(); ?>