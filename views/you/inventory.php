<?php
$emptyinv = true;
$invarray = json_decode($this->economy["inv"]);
if (!empty($invarray)) {
    $inv = array_reverse($invarray);
    $emptyinv = false;
}

$links = [
    ['href' => 'inventory?meow=1', 'text' => 'Decals'],
    ['href' => 'inventory?meow=2', 'text' => 'Sounds'],
    ['href' => 'inventory?meow=4', 'text' => 'T-Shirts'],
    ['href' => 'inventory?meow=5', 'text' => 'Shirts'],
    ['href' => 'inventory?meow=6', 'text' => 'Pants'],
    ['href' => 'inventory?meow=7', 'text' => 'Faces'],
    ['href' => 'inventory?meow=8', 'text' => 'Heads'],
    ['href' => 'inventory?meow=9', 'text' => 'Hats'],
];

$category = isset($_GET['meow']) ? $_GET['meow'] : '2';
$raw = isset($_GET['raw']) ? $_GET['raw'] : false;
?>
<div>
    <?php
$curquery = ltrim($_SERVER['REQUEST_URI'] ?? '', '/you');
if (empty($category)) {
    $curquery = '2';
}
foreach ($links as $link) {
    $href = $link['href'];
    $text = $link['text'];
    
    if ($href === $curquery) {
        echo "<span>$text</span> ";
    } else {
        echo "<a href=$href>$text</a> ";
    }
}

if (!$emptyinv) {
    $placeholders = implode(',', array_fill(0, count($invarray), '?'));
    $stmtcheckitem = $this->db->prepare("
    SELECT *
    FROM items
    WHERE id IN ($placeholders) and TYPE = ?
    ");
    $params = array_merge($invarray, [$category]);
    $stmtcheckitem->execute($params);

    if ($stmtcheckitem->rowCount() > 0) {
        $results = $stmtcheckitem->fetchAll();
    } else {
        $results = false;
    }
}

?>
</div>
<div class="catalogitemborder">
<?php
if ($results) {
foreach ($results as $v) {
        ?>
<a class='catalogitem' href="/asset/item?id=<?=$v["id"]?>">
    <div class="catalogitemasset">
        <?php if ($v['approved'] == 1) {?>
            <img class='itemimg' src='/asset/thumbnail?id=<?=$v["id"]?>'>
        <?php } else if ($v['approved'] == 0) {
            echo 'Not available';
        } else if ($v['approved'] == 2){
            echo 'Deleted asset.';
        } ?>
    </div>
    <?=mb_strimwidth(htmlspecialchars($v['name']), 0, 14, '..');?>
</a>
<?php
}
} else { echo "You have no items of this type";}
echo '</div>';