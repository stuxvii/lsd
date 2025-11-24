<div class="border">
    <?php
    if ($fetch) {
        foreach ($fetch as $row) {
            $id = htmlspecialchars($row['id']);
            $type = htmlspecialchars($row['assettype']);
            $information = htmlspecialchars($row['information']);
            $uploaddate = htmlspecialchars($row['timestamp']);
            ?>
    <div class="fr wfa" id="<?=$id?>" style="justify-content: space-between;">
            <span style="width:-webkit-fill-available;display:flex;flex-direction:row;justify-content:space-between;s">
            <span style="margin-right:14px;">
            <a href="/moderation/triage?id=<?=$id?>" title="check affected content">Inspect</a>
            <em><?=$information?></em>
            </span>
            <span title="<?=date('Y-d-m H:i:s', $uploaddate)?>"><?=date('Y-d-m', $uploaddate)?></span>
            </span>
            </div>
<?php 
}
    } else {
        echo 'No reports up for triaging.';
    }?>
</div>