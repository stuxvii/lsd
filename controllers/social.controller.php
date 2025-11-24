<?php
class SocialController extends BaseController{
    private $other_user_info;
    private $other_preferences;
    private $other_economy;
    private $other_profile;
    private $other_uid;

    public function __CONSTRUCT() {
        parent::__CONSTRUCT();
        if (isset($_GET["id"])) {
            $this->other_uid = $_GET["id"];
            $this->other_user_info = $this->model->getUserInfo($this->other_uid);
        }
    }

    public function Profile($action = null) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!$this->user_info["id"]) {
                die("You must be logged in.");
            }
            switch ($action) {
                case "follow":
                    if (!isset($_POST["csrftoken"]) || $_POST["csrftoken"] != $_SESSION["csrftoken"]) {
                        die();
                    }

                    if (isset($_POST["user"]) && is_numeric($_POST["user"])) {
                        $user_to_follow = $_POST["user"];
                    } else {
                        die(json_encode(["status" => "User is not set"]));
                    }

                    if ($user_to_follow == $this->user_info["id"]) {
                        die(json_encode(["status" => "Can't follow yourself"]));
                    }

                    $is_already_following_stmt = $this->db->prepare('SELECT COUNT(*) as c FROM interaction
                        WHERE `from_who` = ? AND `to_what` = ? AND type = 1
                    ');
                    $is_already_following_stmt->execute([$this->user_info['id'], $user_to_follow]);
                    $following = true;
                    
                    if ($is_already_following_stmt->fetch()["c"] > 0) {
                        $stmt_unfollow = $this->db->prepare('DELETE FROM interaction
                            WHERE `from_who` = ? AND `to_what` = ? AND type = 1
                        ');
                        $stmt_unfollow->execute([$this->user_info['id'], $user_to_follow]);
                        $following = false;
                    } else {
                        $stmt_follow = $this->db->prepare('INSERT IGNORE INTO interaction (`from_who`, `to_what`, `timestamp`, `type`)
                            VALUES (?, ?, ?, 1)
                        ');
                        $stmt_follow->execute([$this->user_info['id'], $user_to_follow, time()]);
                        $following = true;
                    }
                    
                    $stmt_get_amount_of_followers = $this->db->prepare('SELECT COUNT(*) as c FROM interaction WHERE to_what = ? AND type = 1');
                    $stmt_get_amount_of_followers->execute([$user_to_follow]);
                    $stmt_followers = $stmt_get_amount_of_followers->fetch()["c"];
                    die(json_encode(["status" => $following, "followers" => $stmt_followers]));
                    break;
                default:
                    break;
            }
        }
        if (isset($this->other_user_info)) {
            $this->other_preferences = $this->model->getUserSettings($this->other_user_info['id']);
            $this->other_economy = $this->model->getUserEconomy($this->other_user_info['id']);
            $this->other_profile = $this->model->getUserProfile($this->other_user_info['id']);
            $this->other_economy["inv"] = json_decode($this->other_economy["inv"]);
            $charisavailable = file_exists(ROOT_PATH . "/renders/" . $this->other_user_info['id'] . ".png");
            ob_start();
            ?><meta property="og:title" content="<?=$this->other_user_info["username"]?>">
            <meta property="og:description" content="<?=htmlspecialchars($this->other_profile["desc"])?>">
            <meta property="og:image" content="https://lsdblox.cc/social/avatar?id=<?=$this->other_uid?>&amp;=<?=time()?>">
            <meta property="og:type" content="website">
            <?php
            $meta_tags = ob_get_clean();
        }
        ob_start();
        require_once ROOT_PATH . "/views/social/profile.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
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
        header("Content-type: image/png");
        readfile(ROOT_PATH . "/renders/" . $this->other_uid . ".png");
    }

    public function Refresh() {
        header("Location: /");
    }

    public function Group($args = null) {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        ob_start();
        require_once ROOT_PATH . "/views/social/group.php";
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

    public function Avatar_fetch() {
        header("Content-type: application/json");
        $usrid = $_GET['id'];
        $usrid = (int)$usrid;
        $stmt_check = $this->db->prepare('
        SELECT username
        FROM users
        WHERE id = ?
        ');
        $stmt_check->execute([$usrid]);

        if ($stmt_check->rowCount() > 0) {
            $charisavailable = false;
            $defaultavatar = [
                "head" => 1009,
                "trso" => 23,
                "lleg" => 301,
                "rleg" => 301,
                "larm" => 1009,
                "rarm" => 1009
            ];

            $stmt_get_avatar = $this->db->prepare('
            SELECT colors, equipped
            FROM profiles
            WHERE id = ?
            ');
            $stmt_get_avatar->execute([$usrid]);
            $row = $stmt_get_avatar->fetch(PDO::FETCH_ASSOC);
            $undecoded = [];

            if ($row && !empty($row['colors'])) {
                $undecoded = $row['colors'];
                $avatar = json_decode($undecoded, true);
            } else {
                $avatar = $defaultavatar;
            }

            $equipped = json_decode($row['equipped']) ?? array();
            $response = [
                "accessories" => $equipped,
                "colors" => [
                    "head" => $avatar["head"],
                    "trso" => $avatar["trso"],
                    "rarm" => $avatar["rarm"],
                    "larm" => $avatar["larm"],
                    "rleg" => $avatar["rleg"],
                    "lleg" => $avatar["lleg"]
                ]
            ];
            echo json_encode($response);
        } else {
            readfile("public/assets/images/404.png");
        }
    }
}
?>
