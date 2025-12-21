<?php
/* Small script that fetches all files in a directory and gives a friendly list of them, allowing any client to download them. You probs don't need this but in my case i have it since i frequently need to mirror files to download or to share. */
$page_content = "";
$files = array_diff(scandir(".", SCANDIR_SORT_DESCENDING), array('..', '.'));
foreach ($files as $filename) {
    if (pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
        continue;
    }

    if (mb_substr($filename, 0, 1) == ".") {
        continue;
    }

    $formatted_text = "<a href='$filename' download>$filename</a>";
    $page_content .= $formatted_text;

}
?>
<head>
    <style>*{background-color:#000;color:#fff;margin:0;padding:0}html{font-family:'Gill Sans','Gill Sans MT',Calibri,'Trebuchet MS',sans-serif}body{display:flex;flex-direction:row;justify-content:center}.listing{display:inherit;flex-direction:column}a{text-decoration:none;}a:hover{color:#000;background-color:#fff}</style>
</head>
<body>
    <div class="listing">
        <h1>LSDDL</h1>
        <hr>
        <?=$page_content?>
    </div>
</body>
