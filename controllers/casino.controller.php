<?php
class CasinoController extends BaseController {
    public function Main() {
        if (!$this->user_info) {
            header("Location: /account/logout");
        }
        $msg = "";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!isset($_POST["amount"])) {
                $msg = "You need to specify an amount of money.";
            }

            if (!isset($_POST["csrftoken"]) || $_POST["csrftoken"] != $_SESSION["csrftoken"]) {
                $msg = "CSRF token mismatch.";
            }

            $money_amount = (int)$_POST["amount"];

            try {
                $this->db->beginTransaction();
                if ($money_amount == 0) {
                    throw new Exception("You must deposit a value other than 0.");
                }
                
                if ($money_amount < 0) {
                    if (($money_amount * -1) > $this->economy["money"]) {
                        throw new Exception("Insufficient funds in account.");
                    }
                }

                if ($money_amount > $this->economy["pocket_money"]) {
                    throw new Exception("Insufficient funds in pocket.");
                }
                $stmt_deduct = $this->db->prepare('UPDATE economy SET `pocket_money` = `pocket_money` - ? WHERE id = ?');
                $stmt_deduct->execute([$money_amount, $this->user_info['id']]);

                $stmt_add = $this->db->prepare('UPDATE economy SET `money` = `money` + ? WHERE id = ?');
                $stmt_add->execute([$money_amount, $this->user_info['id']]);
                
                $this->economy["pocket_money"] -= $money_amount;
                $this->economy["money"] += $money_amount;
                $this->db->commit();
            } catch (Exception $e) {
                $this->db->rollBack();
                $msg = "Transaction failed: " . $e->getMessage();
            }
        }
        ob_start();
        require_once ROOT_PATH . "/views/casino/main.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Game($mode = null, $args = null) {
        if (!$this->user_info) {
            header("Location: /");
        }
        ob_start();
        switch ($mode) {
            case "roulette":
                require_once ROOT_PATH . "/views/casino/roulette.php";
                break;
            case "blackjack":
                require_once ROOT_PATH . "/views/casino/blackjack.php";
                break;
            default:
                header("Location: /casino");
                break;
        }
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
}
?>