<?php
class AdminController extends BaseController {
    public function Main() {
        ob_start();
        if (!$this->user_info["isoperator"]) {
            header("Location: /");
            exit;
        }
        require_once ROOT_PATH . "/views/admin/main.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
}