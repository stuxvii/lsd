<div id="colorpickerdiv" class="focus hidden">
    <span id="selectedBodyPart"></span>
    <div class="border">
        <div class="colorpicker" id="colorpicker">
            <?php
            foreach ($GLOBALS['brickcolor'] as $k => $v) {
                echo "<span class='color' title='$v' colorbrick='$v' style='background-color:#$k;'></span>";
            }
            ?>
        </div>
    </div>
    <button onclick="closemodal()">Save</button>
</div>

<div class="fr" style="height:100%;overflow:scroll">
    <div>
        <div class="end_to_end">
            <select id="category_selection" name="category_selection">
                <?php
                foreach ($links as $link) {
                    $id = $link["href"];
                    echo "<option value=\"$id\">";
                    echo $link["text"];
                    echo "</option>";
                }
                ?>
            </select>
            <button id="switch_category_button">Go</button>
        </div>
        <div class="catalogitemborder" id="inventory_drawer">
        </div>
    </div>
    <div class="planecharacter" id="character_view">
        <div class="border">
            <div class="vert"><img height='240px' id='render' src='/social/avatar?id=<?=$this->user_info["id"]?>'></div>
            <button onclick="render();" id="renderstat">Redraw</button>
        </div>
    
        <div class="border" id="char">
            <span class="bodypart" id="head" color="1009"></span>
            <div class="horiz">
                <span class="bodypart limb" id="larm" color="1009"></span>
                <span class="bodypart" id="trso" color="23"></span>
                <span class="bodypart limb" id="rarm" color="1009"></span>
            </div>
            <div class="horiz">
                <span class="bodypart limb" id="lleg" color="301"></span>
                <span class="bodypart limb" id="rleg" color="301"></span>
            </div>
        </div>
    </div>
</div>

<?php
$bpdata = [];
$avatarcolors = json_decode($this->profile["colors"]);
foreach ($avatarcolors as $part_id => $sql_color_id) {
    $color_id = $sql_color_id;
    $hex = array_search((int) $color_id, $GLOBALS["brickcolor"]);
    if ($hex) {
        $bpdata[] = [
            'id' => $part_id,
            'color_id' => $color_id,
            'hex' => $hex
        ];
    }
}
$newjson = json_encode($bpdata);
?>

<script src="/assets/js/character.js"></script>
<script>
document.addEventListener("DOMContentLoaded", e => {
    let t = <?=$newjson?>;
    t.forEach(e => {
        let n = document.getElementById(e.id);
        if (n) {
            n.style.backgroundColor = "#" + e.hex; 
            n.setAttribute('color', e.color_id); 
        }
    });
});
</script>