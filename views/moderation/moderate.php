<div class="catalogitemborder">
<?php if ($fetch) {
foreach ($fetch as $row) {
    $id         = htmlspecialchars($row['id']);
    $itemname   = htmlspecialchars($row['name']);
    $owner      = htmlspecialchars($row['owner']);
    $value      = htmlspecialchars($row['value']);
    $public     = htmlspecialchars($row['public']);
    $type       = htmlspecialchars($row['type']);
    $itemdesc   = htmlspecialchars($row['desc']);
    $uploaddate = htmlspecialchars($row['uploadts']);

    foreach ($GLOBALS["asset_types"] as $group => $types) { // at line 69 (nice)
        if (in_array($type, $types)) {
            $assettypegroup = $group;
            break;
        } else {
            // Re-rolling for a blueprint..
        }
    }

    ?>
    <div class='catalogitem' id="<?=$id?>" style="width: 16rem;">
        <div class='catalogiteminfo'>
            <div>
            <?=$itemname?>
            <br>
            ¥<?=$value?>
            <br>
            <span title="<?=date('jS l, F Y', $uploaddate)?>"><?=date("d-m-y",$uploaddate); ?></span>
            <?php if (!empty($itemdesc)): ?>
            Description:
            <em><?=$itemdesc?></em>
            <?php endif; ?>
            <br>
            <span>Uploaded by: <a href="profile?id=<?=$owner?>"><?=$this->getuser($owner)['username']?></a></span>
            </div>
            <div class='catalogitemasset'>
                <?php
            if ($assettypegroup == "image" || $assettypegroup == "face") {
                ?>
                <img class="catalogitemimg" src="/asset/?id=<?=$id?>&force=true">
                <?php
            } else if ($assettypegroup == "audio") {
                ?>
                <audio controls> 
                    <source src="/asset/?id=<?=$id?>&force" type="audio/mpeg">
                </audio>
                <?php
            } else if ($assettypegroup == "mesh") {
                ?>
                    <img class="catalogitemimg" src="/asset/thumbnail?id=<?=$id?>&force=true">
                <?php
            }
            ?>
            </div>
            </div>
            <span id="stat<?=$id?>">&nbsp;</span>
            <div class="buttons">
                <button style="background-color:var(--good);" onclick="handleAction(<?=$id?>,'approve');">Approve</button>
                &nbsp;
                <button style="background-color:var(--evil);" onclick="handleAction(<?=$id?>,'reject');">Reject</button>
            </div>
        </div>
<?php }} else {
    echo "nuthin available chieef";
} ?>
</div>
<script>
function handleAction(id, action) {
    const item = document.getElementById(id);
    const statusMessage = document.getElementById('stat' + id);

    if (!item || !statusMessage) {
        console.error(`DOM elements not found for ID: ${id}`);
        return;
    }

    statusMessage.style.color = 'black';
    statusMessage.textContent = 'Processing...';

    const postdata = [{'id': id, 'action': action, 'csrftoken': "<?=$_SESSION["csrftoken"]?>"}];
    const actionUrl = "/moderation/";

    fetch(actionUrl, {
        method: 'POST',
        body: JSON.stringify(postdata)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok. Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            statusMessage.style.color = 'green';
            statusMessage.textContent = data.message;
            setTimeout(() => {
                item.remove()
            }, 1500);
        } else {
            statusMessage.style.color = 'red';
            statusMessage.textContent = data.message || 'An unknown error occurred.';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        statusMessage.style.color = 'red';
        statusMessage.textContent = 'Failed to connect to server: ' + error.message;
    })
}

</script>