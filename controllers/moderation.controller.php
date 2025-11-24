<?php
class ModerationController extends BaseController {
    public function Reports() {
        if (!$this->user_info['isoperator']) {
            header("Location: /moderation/report");
            exit;
        }
        $stmtcheckitem = $this->db->prepare('
        SELECT *
        FROM reports
        WHERE resolved = 0
        ');
        $stmtcheckitem->execute();
        $fetch = $stmtcheckitem->fetchAll();
        ob_start();
        require_once ROOT_PATH . "/views/moderation/reports.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Report() {
        if (!$this->user_info) {
            header("Location: /");
        }
        ob_start();
        require_once ROOT_PATH . "/views/moderation/report.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Main() {
        if (!$this->user_info['isoperator']) {
            header("Location: /");
            exit;
        }
        $stmtcheckitem = $this->db->prepare('
        SELECT *
        FROM items
        WHERE approved = 0
        ');
        $stmtcheckitem->execute();
        $fetch = $stmtcheckitem->fetchAll();
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $json_data = file_get_contents('php://input');
            
            $data = json_decode($json_data, true);
            if ($data === null || !is_array($data) || !isset($data[0]) || !is_array($data[0]) || $data[0]["csrftoken"] != $_SESSION["csrftoken"]) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
                exit;
            }

            $post_item = $data[0];
            
            $id = filter_var($post_item['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid asset ID provided.']);
                exit;
            }
            
            $action = $post_item['action'] ?? '';
            $allowed_actions = ['reject', 'approve'];
            
            if (!in_array($action, $allowed_actions)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid action specified. Must be "reject" or "approve".']);
                exit;
            }

            $success = false;
            $message = '';
            
            if ($action == 'reject') {
                    $stmtgetitem = $this->db->prepare("
                    SELECT asset
                    FROM items
                    WHERE id = ? and approved = 0
                    ");
                    $stmtgetitem->execute([$id]);
                    $asset = $stmtgetitem->fetch()["asset"];
                    
                    $stmtdelistitem = $this->db->prepare("
                    UPDATE items
                    SET asset = NULL, approved = NULL
                    WHERE id = ? and approved = 0
                    ");
                if ($stmtdelistitem->execute([$id])) {
                    unlink(ROOT_PATH . '/' . $asset);
                    $success = true;
                    $message = "Asset deleted.";
                } else {
                    $message = "Failed to delete asset.";
                }
            } else {
                $stmtapproveitem = $this->db->prepare("
                UPDATE items
                SET approved = 1, approver = ?
                WHERE id = ?
                ");
                if ($stmtapproveitem->execute([$this->user_info['id'], $id])) {
                    $success = true;
                    $message = "Asset approved.";
                } else {
                    $message = "Failed to approve asset.";
                }
            }

            if ($success) {
                http_response_code(200);
                echo json_encode(['status' => 'success', 'message' => $message]);
            } else {
                http_response_code(500); 
                echo json_encode(['status' => 'error', 'message' => $message]);
            }
            
            exit;
        }
        ob_start();
        require_once ROOT_PATH . "/views/moderation/moderate.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Success() {
        ob_start();
        require_once ROOT_PATH . "/views/moderation/success.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Triage() {
        if (!$this->user_info['isoperator']) {
            header("Location: /");
            exit;
        }
        ob_start();
        require_once ROOT_PATH . "/views/moderation/triage.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
}
?>