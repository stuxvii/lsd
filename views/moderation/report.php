<?php
$assetid = isset($_GET["asset"]) ? (int)$_GET["asset"] : 0;
$type = isset($_GET["type"]) ? (string)$_GET["type"] : "feed";
$step = 1;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["assetid"])) {
        $assetid = (int)$_POST["assetid"];
    }
    if (isset($_POST["type"])) {
        $type = $_POST["type"];
    }
    if (isset($_POST["reason"]) && isset($_POST["type"]) && isset($_POST["assetid"])) {
        $text = $_POST["reason"];
        $stmtsubmitreport = $this->db->prepare("
        INSERT INTO reports (assettype, assetid, information, submitter, timestamp) VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmtsubmitreport->execute([$_POST["type"], $_POST["assetid"], $text, $uid, time()]);
        header("Location: /moderation/success");
        exit;
    }
}

if (!empty($assetid) && !empty($type) && $step == 1) {
    switch ($type) {
        case "feed":$t = 1;break;
        case "catalog":$t = 2;break;
        case "forum":$t = 3;break;
        default:$t = $type;break;
    }
    $step = 2;

    $id = $assetid;
    if (!empty($id) && !empty($t) && $step == 2) {
        switch ($t) {
            case 1:
                $stmt = $this->db->prepare('
                SELECT *
                FROM feed
                WHERE id = ?
                ');
                $stmt->execute([$id]);
                $content = $stmt->fetch();
                break;
            case 2:
                $stmtgetitem = $this->db->prepare('
                SELECT *
                FROM items
                WHERE id = ?
                ');
                $stmtgetitem->execute([$id]);

                if ($stmtgetitem->rowCount() > 0) {
                    $row = $stmtgetitem->fetch();
                    
                    // Fetch basic info
                    $value = $row['value'];
                    $itemname = htmlspecialchars($row['name']);
                    $itemdesc = htmlspecialchars($row['desc']);
                    $itemupts = $row['uploadts']; // upload date as a unix timestamp
                    $owner = $row['owner'];
                    $itemtype = $row['type'];
                    $public = $row['public'];
                    $approved = $row['approved'];
                    
                    $ownername = htmlspecialchars($this->getuser($owner)['username']);
                }
                $content = $row;
            break;
            case 3:
                $stmt = $this->db->prepare('
                SELECT *
                FROM forummessages
                WHERE id = ?
                ');
                $stmt->execute([$id]);
                $content = $stmt->fetch();
                break;
        }
    }
}

?>
<div class="borderfc">
    <h3>Report inappropriate content</h3>
    <form id="plrform" method="post" class="fc" action="/moderation/report">
        <?php if ($step == 1):?>
        <label for="type">Type:
            <select name="type" id="type" style="margin-top:6px;">
                <option value="1" <?php if ($type=="feed" ){ echo "selected";}?>>Feed post</option>
                <option value="2" <?php if ($type=="catalog" ){ echo "selected";}?>>Catalog item</option>
                <option value="3" <?php if ($type=="forum" ){ echo "selected";}?>>Forum post</option>
            </select>
        </label>
        <label for="assetid">Asset ID:
            <input type="number" name="assetid" id="assetid" value="<?=htmlspecialchars($assetid)?>">
        </label>
        <input type="submit" value="Next" style="margin-top:15px;">
        <?php elseif ($step == 2):?>
        <?php switch ($t) {
            case 1:?>
        <div class="fr" style="width: 100%; justify-content: space-between; padding: 0px;">
            <a class="border" href="/social/profile?id=<?=$content['author'];?>">
                <img src="/social/avatar?id=<?=$content['author'];?>" height="100">
                <span><?=$this->getuser($content['author'])["username"];?></span>
            </a>
            <div class="msgbox">
                <div class="msg"><span class="msg wfa"><?=$this->formatmessage($content["content"])?></span></div>
                <div class="msgdate">
                    <span><?=date("Y-d-m H:i:s",(int)$content["uploadtimestamp"]);?> UTC</span>
            </div>
        </div>
    </div>
        <?php break;
            case 2:?>
            <div class="border" style="flex-direction:row;align-items:normal;">
                <div class="fc">
                <?php
                if ($itemname) {
                    if ($itemtype === "3") {
                        echo "<audio controls src=\"/asset/?id=$assetid&force\"></audio>";
                    } else {
                        echo "<img class='itemimg' src=\"/asset/thumbnail?id=$assetid\">";
                    }
                ?>
                </div>
                <div style='margin-left:1em;flex-direction:column;display:flex;justify-content: space-between;'>
                    <h1><?=$itemname;?></h1>
                    <div style="flex-direction:column;display:flex;">
                        <span>
                            Uploader:
                            <a href="/profile?id=<?=$owner;?>"><?=$ownername;?></a>
                        </span>
                        <span title="<?=date('jS l, F Y', (int)$itemupts);?>">Uploaded at <?=date("d-m-y",(int)$itemupts); ?></span>
                        <span><?php if (!empty($itemdesc)) {
                            echo $itemdesc;
                        } else {
                            echo "<em>Item has no description.</em>";
                        }?></span>
                    </div>
                    <div style="flex-direction:row;display:flex;">
                </div> <?php } else { header("Location: /404.html"); }?>
            </div>
            </div>
            <?php break;
            case 3:?>
        <div class="fr" style="width: 100%; justify-content: space-between; padding: 0px;">
            <a class="border" href="/profile?id=<?=$content['author'];?>">
                <img src="/social/avatar?id=<?=$content['author'];?>
                " height="100">
                <span><?=$this->getuser($content['author'])["username"];?></span>
            </a>
            <div class="msgbox">
                <div class="msg"><span class="msg wfa"><?=$this->formatmessage($content["text"]);?></span></div>
                <div class="msgdate">
                    <span><?=date("Y-d-m H:i:s",(int)$content["creationtime"]);?></span>
                </div>
            </div>
        </div>
        <?php break;
            }?>
            <textarea rows="8" cols="32" type="textarea" name="reason" maxlength="512" id="reason" required placeholder="I'm reporting this because it breaks this one rule..."></textarea>
            <input type="hidden" name="assetid" id="assetid" value="<?=$assetid?>">
            <input type="hidden" name="type" id="type" value="<?=$t?>">
            <input type="submit" value="<?php if ($step == 1) {echo "Next";} else {echo "Send";} ?>" style="margin-top:15px;">
            <?php endif;?>
    </form>
</div>