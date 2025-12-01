<?php
$stmtgettransactions = $this->db->prepare('
SELECT u.username, u.id, e.money
FROM users u
JOIN economy e 
ON u.id = e.id
ORDER BY 
e.money DESC
');
$stmtgettransactions->execute();
$transactions = $stmtgettransactions->fetchAll(PDO::FETCH_ASSOC);
?>
<h3>Leaderboard</h3>
<div class="border" style="justify-content: flex-start;height:75%;">
    <table>
        <tr>
            <th>Position</th>
            <th></th>
            <th>User</th>
            <th>Currency</th>
        </tr>
<?php
if (count($transactions) > 0) {
    $position = 0;
    foreach ($transactions as $row) {
        $position += 1;
        $id = htmlspecialchars($row['id']);
        $username = htmlspecialchars($row['username']);
        $money = htmlspecialchars($row['money']);
        ?>
        <tr>
            <td><?=$position?></td>
            <td><a href="/social/profile?id=<?=$id;?>"><img src="/social/avatar?id=<?=$id;?>" height="100"></img></a></td>
            <td><a href="/social/profile?id=<?=$id;?>"><?=$username;?></a></td>
            <td>¥<?=$money; ?></td>
        </tr>
<?php }
    } else {
        echo 'No transactions found.';
    } ?>
    </table>
</div>