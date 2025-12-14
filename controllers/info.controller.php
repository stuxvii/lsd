<?php
class InfoController extends BaseController {
    public function Privacypolicy() {
        if (isset($_GET['plaintext']) == true) {
            header("Content-type: text/plain");
            readfile("assets/privacypolicyclean.txt");
            exit;
        }
        ob_start();
        require_once ROOT_PATH . "/views/info/privacypolicy.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Termsofservice() {
        ob_start();
        require_once ROOT_PATH . "/views/info/termsofservice.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Status() {
        ob_start();
        require_once ROOT_PATH . "/views/info/status.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Main() {
        ob_start();
        ?>
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
        <span>-- Misc --</span>
        <a href="https://github.com/stuxvii/lsd">Site source</a>
        <a href="https://github.com/stuxvii/lsd_thumbnail_server">Renderer source</a>
        <a href="/cdn/playlist.zip">Personal music playlist</a>
        <a href="/mii_creator/index.html">Mii Creator accountless (WIP)</a>
        <?php
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";

    }
}
?>