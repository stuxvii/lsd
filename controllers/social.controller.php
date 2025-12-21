<?php
class SocialController extends BaseController {
    protected $other_user_info;
    protected $other_preferences;
    protected $other_economy;
    protected $other_profile;
    protected $other_uid;

    public function __CONSTRUCT() {
        parent::__CONSTRUCT();
        if (isset($_GET["id"])) {
            $this->other_uid = $_GET["id"];
            $this->other_user_info = $this->model->getUserInfo($this->other_uid);
        }
        if (!$this->user_info["id"]) {
            die("You must be logged in.");
        }
    }

    public function Profile($action = null) {
        $this->showPage("social/profile.php");
    }
    
    public function Post() {
        $id = $_GET["id"];
        $content = null;

        $stmt = $this->db->prepare('
            SELECT *
            FROM feed
            WHERE id = ?
        ');
        $stmt->execute([$id]);
        $content = $stmt->fetch();
        ob_start();
        ?><meta property="og:title" content="LSDBLOX - <?=$this->getuser($content['author'])["username"]?>">
        <meta property="og:description" content="<?=$content["content"]?>">
        <meta property="og:image" content="https://lsdblox.cc/social/avatar?id=<?=$content['author']?>&amp;=<?=time()?>">
        <meta property="og:type" content="website">
        <?php
        $meta_tags = ob_get_clean();
        ob_start();
        if ($content) {
            require_once ROOT_PATH . "/views/social/post.php";
        } else {
            echo "Not found";
        }
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Avatar() {
        if (!file_exists(ROOT_PATH . "/renders/" . $this->other_uid . ".png")) {
            $rendercontent = $this->getRender($this->other_uid, 1);
            if (!empty($rendercontent)) {
                file_put_contents(ROOT_PATH . "/renders/" . $this->other_uid . ".png", $rendercontent);
            }
        } else {
            $rendercontent = file_get_contents(ROOT_PATH . "/renders/" . $this->other_uid . ".png");
        }

        if (empty($rendercontent)) {
            die('Not found');
        }

        header('Content-type: image/png');
        echo $rendercontent;
    }

    public function Refresh() {
        header("Location: /");
    }

    public function Group($args = null) {
        ob_start();
        require_once ROOT_PATH . "/views/social/group.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Leaderboard($args = null) {
        ob_start();
        require_once ROOT_PATH . "/views/social/leaderboard.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Feed() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->user_info === null) {
                http_response_code(400);
                header('Location: /');
                exit;
            }
            if ($_POST['message']) {
                if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
                    die("Invalid CSRF token.");
                }
                $cooldown_seconds = 1;
                $cooldown_file_path = '/var/tmp/msgboardcooldown/';
                $cooldown_file = $cooldown_file_path . hash('sha256', $_SERVER['REMOTE_ADDR']) . '.time';

                if (!is_dir($cooldown_file_path)) {
                    if (!mkdir($cooldown_file_path, 0777, true)) {
                        error_log('Failed to create cooldown directory: ' . $cooldown_file_path);
                    }
                }

                $current_time = microtime(true);
                $last_render_time = 0.0;

                if (file_exists($cooldown_file)) {
                    $last_render_time = (float) file_get_contents($cooldown_file);
                }

                $time_since_last_call = $current_time - $last_render_time;
                if ($time_since_last_call < $cooldown_seconds) {
                    header('Location: /social/refresh');
                    exit;
                }

                $message = trim($_POST['message']);

                if (empty($message) || mb_strlen($message, 'UTF-8') > 512) {
                    header('Location: /social/refresh');
                    exit;
                }

                try {
                    $uploadts = time();
                    $stmt = $this->db->prepare('INSERT INTO `feed` (`content`,`author`,`uploadtimestamp`) VALUES (?,?,?)');
                    $stmt->execute([$message, $this->user_info["id"], $uploadts]);
                    header('Location: /social/refresh');
                } catch (Exception $e) {
                    error_log('DB Error: ' . $e->getMessage());
                    sendjsonback('error', 'Database operation failed.', 500);
                }

                if (file_put_contents($cooldown_file, $current_time) === false) {
                    error_log('Failed to write cooldown time to: ' . $cooldown_file);
                }

                exit;
            }
        }
        $page = isset($_GET['page']) ? $_GET['page'] : '0';
        $raw  = isset($_GET['raw'])  ? $_GET['raw']  : false;
        if ($raw) {
            header('Content-type: application/json');
            $offset = 6 * (int) $page;
            if (is_int($offset)) {
                $stmt = $this->db->prepare("
                    SELECT *
                    FROM feed
                    ORDER BY id DESC
                    LIMIT 6 OFFSET {$offset};
                ");
                $stmt->execute();
                $offset = $offset + 6;
                $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $results = [];
                if ($fetch) {
                    foreach ($fetch as $row) {
                        $row["username"] = $this->getuser($row["author"])["username"];
                        $row["content"]  = $this->formatmessage($row["content"]);
                        $results[] = $row;
                    }
                } else {
                    http_response_code(404);
                    exit;
                }
                echo json_encode($results);
            } else {
                echo json_encode("What");
            }
            exit;
        }
    }
}
?>
