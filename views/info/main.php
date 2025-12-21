<span>-- Legal --</span>
<a href="/info/termsofservice">Terms of Service</a>
<a href="/info/privacypolicy">Privacy Policy</a>
<a href="/info/status">Service Status</a>
<hr>
<span>˖ ᡣ𐭩 ⊹ ࣪  ౨ৎ˚₊</span>
<hr>
<span>-- Contact acidbox themselves --</span>
<a href="https://t.me/acidbox93">Telegram</a>
<a href="https://smp18.simplex.im/a#Y_8nW6diBoByt6U3BWHaTmIRFPTHnHXMWNcxTMYxAAQ">SimpleX</a>
<a href="mailto:acid@lsdblox.cc">Personal e-mail (acid@lsdblox.cc)</a>
<hr>
<span>-- Support acidbox --</span>
<a href="https://buymeacoffee.com/acidbox">Buy me a coffee</a>
<hr>
<span>-- Misc and my othar things u.u --</span>
<a href="/cdn/">LSDDL</a>
<a href="https://github.com/stuxvii/lsd">Site source</a>
<a href="https://acdbx.top">My site/blog</a>
<a href="https://github.com/stuxvii/lsd_thumbnail_server">Renderer source</a>
<a href="https://github.com/stuxvii/rhythm-test">Quick & dirty rhythm engine</a>
<a href="/mii_creator/">Mii Creator accountless (WIP)</a>
<hr>
<span>-- 88x31 badges --</span>
<div class="fr">
<?php
$files = array_diff(scandir("assets/images/blinkies/", SCANDIR_SORT_DESCENDING), array('..', '.'));
foreach ($files as $filename) {
    if (pathinfo($filename, PATHINFO_EXTENSION) == 'php') {
        continue;
    }

    if (mb_substr($filename, 0, 1) == ".") {
        continue;
    }

    $formatted_text = "<img src='/assets/images/blinkies/$filename'>";
    echo($formatted_text);

}
?>
</div>
