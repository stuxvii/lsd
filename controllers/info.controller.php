<?php
class InfoController extends BaseController {
    private $pages = [
        "termsofservice",
        "status",
        "main",
    ];

    public function __call($name, $arguments = null) {
        $view = strtolower($name);
        if (in_array($view, $this->pages)) {
            $this->showPage("info/{$view}.php");
            $this->showPage("info/status.php");
        }
    }

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
}
?>