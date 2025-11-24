<?php
if ($this->user_info === null) {
    http_response_code(500);
    header("Location: /");
    exit;
}
ob_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $movebg =    $_POST['movingbg'] ?? false ? 1 : 0;
    $sidebars =  $_POST['sidebars'] ?? false ? 1 : 0;
    $freakmode =$_POST['freakmode'] ?? false ? 1 : 0;
    $mirrorsb =  $_POST['mirrorsb'] ?? false ? 1 : 0;
    $emojidex =  $_POST['emojidex'] ?? false ? 1 : 0;
    $light_mode =  $_POST['light_mode'] ?? false ? 1 : 0;
    
    $sidebarid = (int)$_POST['sidebarid'] ?? 1;
    $theme = 0;
    $font =  0;

    if (isset($_POST['thememode'])) {
        $scheme = $_POST['thememode'];
        if (isset($GLOBALS["color_schemes"][$scheme])) {
            $theme = (int) $scheme;
        }
    }



    if (isset($_POST['font'])) {
        $scheme = $_POST['font'];
        if (isset($GLOBALS["fonts_list"][$scheme])) {
            $font = (int) $scheme;
        }
    }

    $updstmt = $this->db->prepare("
    UPDATE config
    SET
        appearance = ?,
        movingbg = ?,
        font = ?,
        sidebarid = ?,
        sidebars = ?,
        freakmode = ?,
        mirrorsidebars = ?,
        emojidex = ?,
        light_mode = ?
    WHERE id = ?
    ");

    $updstmt->execute([
        $theme, 
        $movebg, 
        $font, 
        $sidebarid, 
        $sidebars, 
        $freakmode,
        $mirrorsb,
        $emojidex,
        $light_mode,
        $this->user_info["id"]
    ]);

    header('Location: /account/config');
}
?>
<div>
    <div class="border">
        <form method="post" action="/account/config" class="fc aifs" style="padding:0">
            <label for="light_mode">
                <input type="checkbox" id="light_mode" name="light_mode" <?= $this->preferences["light_mode"] ? "checked" : "" ?>>
                Light mode
            </label>
            <?php foreach ($GLOBALS["color_schemes"] as $index => $color_scheme) {
                $primary = $this->preferences["light_mode"] ? $color_scheme["primary"] : $color_scheme["secondary"];
                $secondary = $this->preferences["light_mode"] ? $color_scheme["secondary"] : $color_scheme["primary"];
                ?>
                <label for="<?=$index?>" style="background-color:<?=$secondary?>; color:<?=$primary?>;">
                    <input type="radio" id="<?=$index?>" name="thememode" value="<?=$index?>" <?= $this->preferences["appearance"] == $index ? "checked" : "" ?>>
                    <?=$color_scheme['name']?>
                </label>
            <?php } ?>
            <label for="movingbg">
                <input type="checkbox" id="movingbg" name="movingbg" <?= $this->preferences["movingbg"] ? "checked" : "" ?>>
                Moving background
            </label>
            <label for="emojidex">
                <input type="checkbox" id="emojidex" name="emojidex" <?= $this->preferences["emojidex"] ? "checked" : "" ?>>
                emojidex Emoji
            </label>
            <label for="freakmode">
                <input type="checkbox" id="freakmode" name="freakmode" <?= $this->preferences["freakmode"] ? "checked" : "" ?>>
                DO NOT.
            </label>
            <label for="font">Font</label>
            <select id="font" name="font" style="margin-top:6px;">
                <?php foreach ($GLOBALS["fonts_list"] as $index => $current_font) {?>
                <option value="<?=$index?>"  <?= ($this->preferences["font"] == $index) ? "selected" : "" ?> style="font-family: <?=$current_font["font_family"]?>;"><?=$current_font["name"]?></option>
                <?php }?>
            </select>
            <label for="sidebars">
                <input type="checkbox" id="sidebars" name="sidebars" <?php if($this->preferences["sidebars"]){echo"checked";}?>>
                Sidebars
            </label>
            <select <?= ($this->preferences["sidebars"]) ? "" : "hidden"?> id="sidebarid" name="sidebarid" style="margin-top:6px;">
                <?php foreach ($GLOBALS["sidebars_list"] as $index => $current_sidebar) {?>
                <option value="<?=$index?>"  <?= ($this->preferences["sidebarid"] == $index) ? "selected" : "" ?>><?=$current_sidebar["name"]?></option>
                <?php }?>
            </select>
            <label  <?= ($this->preferences["sidebars"] == true) ? "" : "hidden"?> for="mirrorsb">
                <input type="<?= ($this->preferences["sidebars"]) ? "checkbox" : "hidden"?>" id="mirrorsb" name="mirrorsb" <?php if($this->preferences["mirrorsidebars"]){echo"checked";}?>>
                Mirror right sidebar
            </label>
            <span><a href="/account">Account management</a></span>
            <input type="submit" value="Save">
        </form>
    </div>
</div>
<script>
    const INTERNETYAMERO = document.getElementById('freakmode');
    let playingyamero = false;
    async function playyamero() {
        if (playingyamero == false && INTERNETYAMERO.checked == true) {
            playingyamero = true;
            const audio = new Audio("/assets/audio/angelbreaking.mp3");
            audio.volume = 0.3;
            audio.loop = true;
            audio.play();
        }
    }
    INTERNETYAMERO.addEventListener('click', function(event) {
        playyamero()
    })
</script>