<?php
if (!isset($this->preferences["sidebarid"])) {
    return;
}

$class = "sbleft";
$img = "sidebarafn";

if (isset($GLOBALS["sidebars_list"][$this->preferences["sidebarid"]])) {
    $img = $GLOBALS["sidebars_list"][$this->preferences["sidebarid"]]["url"];
}

if (isset($rightside)) {
    $class = "sbright";
}

if ($this->preferences["sidebars"]) {
    echo "<div class='$class'><img src='/assets/images/sidebars/$img'></div>";
}
?>