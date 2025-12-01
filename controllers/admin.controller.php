<?php
class AdminController extends BaseController
{
    public function __construct()
    {
        parent::__CONSTRUCT();
        if (!$this->user_info['isoperator']) {
            header('Location: /');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['csrftoken'] != $_SESSION['csrftoken']) {
            die('Invalid request.');
        }
    }

    public function Main()
    {
        ob_start();
        require_once ROOT_PATH . '/views/admin/main.php';
        $page_content = ob_get_clean();
        require_once ROOT_PATH . '/views/layout/template.php';
    }

    public function Session($args = null)
    {
        switch ($args) {
            case 'refresh':
                $user_id_to_refresh = $_POST["id"] ?? null;
                if (!is_numeric($user_id_to_refresh)) {
                    die("Please enter a valid ID brudda.");
                }
                try {
                    $this->db->beginTransaction();
                    $stmt = $this->db->prepare("UPDATE users SET authuuid = ? WHERE id = ?");
                    $stmt->execute([bin2hex(random_bytes(64)), $user_id_to_refresh]);
                    $this->db->commit();
                } catch(Exception $e) {
                    $this->db->rollback();
                    die("Erm.. What the sigma?! An unexpected error ocurred. {$e}");
                }
                break;
            case 'refresh_all':
                try {
                    $this->db->beginTransaction();
                    $get_ids = $this->db->prepare("UPDATE users SET authuuid = LOWER(HEX(RANDOM_BYTES(64)))");
                    $get_ids->execute();
                    $this->db->commit();
                } catch(Exception $e) {
                    $this->db->rollback();
                    die("Erm.. What the sigma?! An unexpected error ocurred. {$e}");
                }
                break;
        }
        header('Location: /admin');
    }

    public function Cache($args = null)
    {
        switch ($args) {
            case 'asset':
                foreach (glob(ROOT_PATH . '/cache/*') as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                break;
            case 'avatar':
                foreach (glob(ROOT_PATH . '/renders/*') as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                break;
        }
        header('Location: /admin');
    }
}
