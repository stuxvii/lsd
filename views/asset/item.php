<?php
$itemid = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmtgetitem = $this->db->prepare('
SELECT *
FROM items
WHERE id = ?
');
$stmtgetitem->execute([$itemid]);
$row = $stmtgetitem->fetch() ?? null;

$itemname = NULL;
$owned = false;

$invarray = json_decode($this->economy["inv"]);
if (!empty($invarray) && in_array($itemid,$invarray)) {
    $owned = true;
}

if ($row) {
    // Fetch basic info
    $value = $row['value'];
    $itemname = htmlspecialchars($row['name']);
    $itemdesc = htmlspecialchars($row['desc']);
    $itemupts = $row['uploadts']; // upload date as a unix timestamp
    $owner = $row['owner'];
    $type = $row['type'];
    $public = $row['public'];
    $approved = $row['approved'];
    if ($approved == 2) {
        $approved = null;
    }
    // We need to get the owner of the item,
    $stmtgetowner = $this->db->prepare('
    SELECT username
    FROM users
    WHERE id = ?
    ');
    $stmtgetowner->execute([$owner]);
    $ownrow = $stmtgetowner->fetch();
    $ownername = htmlspecialchars($ownrow['username']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $owner == $this->user_info["id"]) {
    if ($_POST['itemname']) {
        $changedtopublic = (int)isset($_POST['itempub']);

        $stmtupditem = $this->db->prepare('
        UPDATE items
        SET value = ?, name = ?, `desc` = ?, `public` = ?
        WHERE id = ?
        ');
        $stmtupditem->execute([$_POST['itemprice'], $_POST['itemname'], $_POST['itemdesc'], $changedtopublic, $itemid]);
        header("Location: item?id=$itemid");
        exit;
    }
    ob_start();
    ?>
    <form method="post" action="item?id=<?=$itemid;?>">
        Item name
        <br>
        <input type="text" placeholder="My epic asset" name="itemname" id="itemname" required value="<?=$itemname;?>">
        <br>
        Description
        <br>
        <textarea type="textarea" placeholder="Nice shirt with alpha. Get good LSDBLOX street cred with this shirt." rows="4" cols="32" name="itemdesc" id="itemdesc"><?=$itemdesc;?></textarea>
        <br>
        <input type="checkbox" id="itempub" name="itempub" <?= $public ? "checked" : "" ?>>
        <label for="itempub">On sale
        <br>
        Price
        <br>
        <input type="number" placeholder="0" name="itemprice" id="itemprice" required value="<?=$value;?>">
        <br>
        <input type="submit" value="Update" style="margin-top:1rem;">
    </form>
    <?php
    $msg = ob_get_clean();
    echo json_encode(['status' => 'success', 'message' => $msg,]);
    exit;
} 
ob_start();
?>
<div id="manage" class="hidden" style="width:100%;"></div>
<span id="purchase-status-message"></span>
<div class="border" style="flex-direction:row;align-items:normal;">
    <div class="fc">
    <?php
    if ($itemname) {
        if ($type == "2") {
            echo "<img class='itemimg' src=\"/asset/thumbnail?id=$itemid&force\">";
            echo "<audio controls src=\"/asset/?id=$itemid&force\"></audio>";
        } else {
            echo "<img class='itemimg' src=\"/asset/thumbnail?id=$itemid&force\">";
        }
     ?>
    </div>
    <div style='margin-left:1em;flex-direction:column;display:flex;justify-content:space-between;'>
        <h1><?=$itemname;?></h1>
        <div style="flex-direction:column;display:flex;">
            <span>
                Uploader:
                <a href="/social/profile?id=<?=$owner;?>"><?=$ownername;?></a>
            </span>
            <span title="<?=date('jS l, F Y', (int)$itemupts);?>">Uploaded at <?=date("d-m-y",(int)$itemupts); ?></span>
            <span><?=empty($itemdesc) ? "<em>Item has no description.</em>" : $itemdesc?></span>
        </div>
        <?php
        if (!$approved == null) {
            if ($public && $approved) {
                if ($value > 0) {
                    echo "¥" . $value;
                } else {
                    echo "Free";
                }
            } else {
                echo "Offsale";
            }
        } else {
            echo "Not available";
        }
        ?>
        <div style="flex-direction:row;display:flex;">
        <?php if ($public && $approved) : ?>
        <button <?php if (!$owned) {echo "onclick=\"purchase($itemid)\"";} ?> style="background-color:var(<?php if ($owned) {echo "--primary-color";} else {echo "--good";} ?>);">
            <?php 
            if (!$owned) {
                echo "Get";
            } else {
                echo "Owned";
            }
            ?>
        </button>
        <button onclick="location.href = '/moderation/report?type=catalog&asset=<?=htmlspecialchars($itemid);?>'" style="background-color:var(--evil);">
            Report
        </button>
        <?php endif; ?>
        <?php if ($owner == $this->user_info["id"]): ?>
        <button onclick="promptmanage(<?=$itemid;?>)" style="background-color:var(--good);">
            Manage
        </button>
        <?php endif;?>
    </div> <?php } else { header("Location: 404.html"); }?>
<script>
const csrftoken = '<?=$_SESSION["csrftoken"]?>';

</script>
<script src="/assets/js/item.js">
</script>
</div>
</div>