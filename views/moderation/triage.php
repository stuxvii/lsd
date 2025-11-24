<?php
$id = $_GET["id"] ?? null;

if ($id) {
    $stmt = $this->db->prepare('SELECT * FROM reports WHERE id = ?');
    $stmt->execute([$id]);
    $data = $stmt->fetch();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
        die("Incorrect token, CSRF Mismatch");
    }
    if (!empty($_POST["action"]) && !empty($_POST["reportid"])) {
        $reportid = $_POST["reportid"];
        $action = $_POST["action"]; // data is sent as string for some reason se we gotta use strings (:sob:)
        echo "Looking up report with ID: " . $reportid . "\n";
        $getreportinfo = $this->db->prepare('
            SELECT *
            FROM reports
            WHERE id = ? AND resolved = 0
        ');
        $getreportinfo->execute([$reportid]);
        $reportinfo = $getreportinfo->fetch();

        $type = $reportinfo["assettype"];
        $assetid = $reportinfo["assetid"];
        if (empty($reportinfo)) {
            echo "Not found...";
            exit;
        } else {
            echo "Found!\n";
            switch ($action) {
                case "false":
                    echo "Dismissing submitted report...\n";
                    $setreportstatus = $this->db->prepare('
                        UPDATE reports
                        SET resolved = 1, resolvedby = ?
                        WHERE id = ?
                    ');
                    $setreportstatus->execute([$this->user_info['id'], $reportid]);
                    break;
                case "true":
                    echo "Deleting reported asset...\n";
                    $setreportstatus = $this->db->prepare('
                        UPDATE reports
                        SET resolved = 2, resolvedby = ?
                        WHERE id = ?
                    ');
                    $setreportstatus->execute([$this->user_info['id'], $reportid]);
                    switch ($type) {
                        case 1:
                            $redactfeedpost = $this->db->prepare('
                                UPDATE feed
                                SET content = "[REDACTED]"
                                WHERE id = ?
                            ');
                            $redactfeedpost->execute([$assetid]);
                            break;
                        case 2:
                            $getasseturlfordeletion = $this->db->prepare('
                                SELECT *
                                FROM items
                                WHERE id = ?
                            ');
                            $getasseturlfordeletion->execute([$assetid]);
                            $assetinfo = $getasseturlfordeletion->fetch();
                            $asseturl = ROOT_PATH . "/" . $assetinfo["asset"];
                            unlink($asseturl);
                            $deleteasset = $this->db->prepare('
                                UPDATE items
                                SET asset = "[REDACTED]", approved = 2
                                WHERE id = ?
                            ');
                            $deleteasset->execute([$assetid]);
                            break;
                        case 3:
                            $redactfeedpost = $this->db->prepare('
                                UPDATE forummessages
                                SET text = "[REDACTED]", name = "[REDACTED]"
                                WHERE id = ?
                            ');
                            $redactfeedpost->execute([$assetid]);
                            break;
                    }
                    break;
            }
        }
    }
    exit;
}
?>
<div class="border fc">
    <div class="fr">
<?php
if ($data) {
switch ($data["assettype"]) {
    case 1:
        $stmt = $this->db->prepare('SELECT * FROM feed WHERE id = ?');
        $stmt->execute([$data["assetid"]]);
        $content_data = $stmt->fetch();
        ?>
        <a class="border fc" href="/social/profile?id=<?=$content_data["author"]?>">
            <img src="/social/avatar?id=<?=$content_data["author"]?>" height="100">
            <span>
                <?=$this->getuser($content_data["author"])["username"]?>
            </span>
        </a>
        <div class="fc aifs">
            <span class="msgdate">
                <?=date("Y-d-m H:i:s",$content_data["uploadtimestamp"])?>
            </span>
            <span>
                <?=htmlspecialchars($content_data["content"])?>
            </span>
        </div>
    <?php
    break;
    case 2:
        $stmt = $this->db->prepare('SELECT * FROM items WHERE id = ?');
        $stmt->execute([$data["assetid"]]);
        $content_data = $stmt->fetch();
        ?>
        <div class="fc aifs">
            <div class="fc">
            <?=$content_data["type"] === "3" ? "<audio controls src=\"/asset/?id=" . $data["assetid"] . "&force\"></audio>" : "<img class='itemimg' src=\"/asset/thumbnail?id=" . $data["assetid"] . "&force\">";
            ?>
            </div>
            <span>Uploader: <a href="/social/profile?id=<?=$content_data["owner"]?>"><?=$this->getuser($content_data["owner"])["username"]?></a></span>
            <span>
                <?=htmlspecialchars($content_data["desc"])?>
            </span>
        </div>
    <?php
    break;
}
    $information = "Reason for flagging: <br>" . htmlspecialchars($data["information"]);
    if (!$data["resolvedby"]) {
        $can_use_action_buttons = true;
    } else {
        $can_use_action_buttons = false;
    }
} else {
    $information = "Not found";
    $can_use_action_buttons = false;
}
?>
    </div>
    <span><?=$information?>
    </span>
    <?php if ($can_use_action_buttons):?>
    <div class="fr">
        <button onclick="moderate(<?=$id?>, true)" style="background-color:var(--evil)">Delete reported asset</button>
        <button onclick="moderate(<?=$id?>, false)" style="background-color:var(--good)">Dismiss report</button>
    </div>
    <?php else:?>
        <span>Moderated by <a href="/social/profile?id=<?=$data["resolvedby"]?>"><?=$this->getuser($data["resolvedby"])["username"]?></a></span>
    <?php endif;?>
</div>

<script>
async function moderate(assetid, action) {
    const formdata = new FormData(); 

    formdata.append('action', action);
    formdata.append('reportid', assetid);
    formdata.append('csrftoken', "<?=$_SESSION["csrftoken"]?>");

    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: formdata 
        });

        if (response.ok) {
            location.reload()
        } else {
            console.error('Operation failed:', await response.text());
        }

    } catch (error) {
        console.error('Fetch operation Operation:', error);
    }
}
</script>