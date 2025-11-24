<?php
class HomeController extends BaseController {
    public function Main() {
        if (isset($this->economy["lastbuxclaim"])) {
            $can_claim_stipend = ($this->economy["lastbuxclaim"] + 43200) < time() ? true : false;
            if ($can_claim_stipend) {
                $stmtstipend = $this->db->prepare("UPDATE economy SET `money` = `money` + 25 WHERE id = ?");
                $stmtstipend->execute([$this->user_info["id"]]);
                $stmtrefresh = $this->db->prepare("UPDATE economy SET lastbuxclaim = ? WHERE id = ?");
                $stmtrefresh->execute([time(), $this->user_info["id"]]);
            }
        }
        ob_start();
        if ($this->user_info) {
            require_once ROOT_PATH . "/views/home/main.php";
        } else {
            $quirky_messages = [
                "eat more lsd",
                "hi polynomers people",
                "<3",
                "flintstones",
                'so retro...',
                'rawr xd',
                'it\'s 1:20 am and i\'m writing these stupid messages',
                'yeah',
                'mhm',
                'drop a like if you eat poop',
                'big chungus'
            ];
            echo $quirky_messages[random_int(0, count($quirky_messages) - 1)];
        }
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
}
?>