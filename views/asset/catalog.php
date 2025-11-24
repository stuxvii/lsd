<div style="padding:1em;">
<?php
$curquery = ltrim($_SERVER['REQUEST_URI'] ?? '', '/');
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
?>
</div>
<div class="catalogitemborder" id="itemsdrawer"></div>
<div style="padding:1em;">
<a id="pastpage">&lt;</a>
<a id="page_indicator"></a>
<a id="nextpage">&gt;</a>
</div>
<script src="/assets/js/catalog.js"></script>