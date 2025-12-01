<?php
class InfoController extends BaseController {
    public function Privacypolicy() {
        if (isset($_GET['botcheck']) == true) {
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
        <?php
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";

    }
}
?>