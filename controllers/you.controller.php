<?php
class YouController extends BaseController
{

    public function Main()
    {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        header('Location: https://lsdblox.cc/social/profile?id=' . $this->user_info['id']);
        exit;
    }

    public function Inventory()
    {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        ob_start();
        require_once ROOT_PATH . '/views/you/inventory.php';
        $page_content = ob_get_clean();
        require_once ROOT_PATH . '/views/layout/template.php';
        exit;
    }

    public function Log()
    {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        ob_start();
        require_once ROOT_PATH . '/views/you/log.php';
        $page_content = ob_get_clean();
        require_once ROOT_PATH . '/views/layout/template.php';
        exit;
    }

    public function Character($mode = 'default', $category = 2)
    {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }

        $color_map = [
            'head_color' => 'head',
            'trso_color' => 'trso',
            'lleg_color' => 'lleg',
            'rleg_color' => 'rleg',
            'larm_color' => 'larm',
            'rarm_color' => 'rarm'
        ];

        $invarray = json_decode($this->economy['inv'], true);
        $items = [];

        $links = [
            ['href' => 4, 'text' => 'T-Shirts'],
            ['href' => 5, 'text' => 'Shirts'],
            ['href' => 6, 'text' => 'Pants'],
            ['href' => 7, 'text' => 'Faces'],
            ['href' => 8, 'text' => 'Heads'],
            ['href' => 9, 'text' => 'Hats'],
        ];

        if ($invarray) {
            $placeholders = implode(',', array_fill(0, count($invarray), '?'));
            $sql = "SELECT * FROM items WHERE id IN ($placeholders) AND (type = ?) AND approved = 1";
            try {
                $stmt = $this->db->prepare($sql);
                $params = array_merge($invarray, [$category]);
                $stmt->execute($params);
                $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $items = array_reverse($fetch);
            } catch (PDOException $e) {
                echo 'Database Error: ' . $e->getMessage();
            }
        }

        $insert_avatar_stmt = $this->db->prepare('
        INSERT IGNORE INTO profiles (id) VALUES (?)
        ');

        $insert_avatar_stmt->execute([$this->user_info['id']]);

        if ($mode == 'default') {
            ob_start();
            require_once ROOT_PATH . '/views/you/character.php';
            $page_content = ob_get_clean();
            require_once ROOT_PATH . '/views/layout/template.php';
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if ($_POST['csrftoken'] != $_SESSION['csrftoken']) {
                die('Invalid CSRF token. Try reloading the page you came from?');
            }
        }
        switch ($mode) {
            case 'inventory':
                header("Content-type: application/json");
                if ($invarray) {
                    $placeholders = implode(',', array_fill(0, count($invarray), '?'));
                    $sql = "SELECT id, `name` FROM items WHERE id IN ($placeholders) AND (type = ?) AND approved = 1";
                    try {
                        $stmt = $this->db->prepare($sql);
                        $params = array_merge($invarray, [$category]);
                        $stmt->execute($params);
                        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($fetch as $val => $item) {
                            $fetch[$val]["equipped"] = in_array($fetch[$val]["id"], json_decode($this->profile["equipped"]), true);
                        }
                        $items = array_reverse($fetch);
                        die(json_encode($items));
                    } catch (PDOException $e) {
                        echo 'Database Error: ' . $e->getMessage();
                    }
                }
                break;
            case 'save':
                $update_data = [];
                $valid_request = true;

                foreach ($color_map as $post_key => $db_column) {
                    if (!isset($_POST[$post_key])) {
                        $valid_request = false;
                        break;
                    }
                    $color_value = (int) $_POST[$post_key];

                    if (!in_array($color_value, $GLOBALS['brickcolor'])) {
                        $valid_request = false;
                        break;
                    }

                    $update_data[$db_column] = (int) $color_value;
                }

                $stmt = $this->db->prepare('UPDATE profiles SET colors = ? WHERE id = ?');
                $stmt->execute([json_encode($update_data), $this->user_info['id']]);

                exit;
                break;

            case 'equip':
                if (in_array((int) $_POST['item'], json_decode($this->economy['inv']))) {
                    $item = (int) $_POST['item'];
                    $stmtgetcuritems = $this->db->prepare('SELECT equipped FROM profiles WHERE id = ?');
                    $stmtgetcuritems->execute([$this->user_info['id']]);

                    if ($stmtgetcuritems) {
                        $row = $stmtgetcuritems->fetch();
                        $newequipped = json_decode($row['equipped']);
                    } else {
                        $newequipped = array();
                    }

                    $newequipped[] = $item;
                    $jsonfordb = json_encode($newequipped);
                    $stmt = $this->db->prepare('UPDATE profiles SET equipped = ? WHERE id = ?');
                    $stmt->execute([$jsonfordb, $this->user_info['id']]);
                    die('Equipped.');
                } else {
                    http_response_code(400);
                    echo 'Uh, you dont own this item. Stop.';
                }
                exit;
                break;
            case 'unequip':
                if (in_array((int) $_POST['item'], $invarray)) {
                    $item = (int) $_POST['item'];
                    $stmtgetcuritems = $this->db->prepare('SELECT equipped FROM profiles WHERE id = ?');
                    $stmtgetcuritems->execute([$this->user_info['id']]);

                    if ($stmtgetcuritems) {
                        $row = $stmtgetcuritems->fetch();
                        $new_equipped = json_decode($row['equipped']);
                    } else {
                        $new_equipped = array();
                    }

                    if (($key = array_search($item, $new_equipped, true)) !== false) {
                        unset($new_equipped[$key]);
                    } else {
                        error_log('What?! Diddy blud tried to unequip an item they do not have equipped? Value: ' . $item);
                    }

                    $new_equipped = array_values($new_equipped);
                    $jsonfordb = json_encode($new_equipped);
                    $stmt = $this->db->prepare('UPDATE profiles SET equipped = ? WHERE id = ?');
                    $stmt->execute([$jsonfordb, $this->user_info['id']]);
                    if ($stmt) {
                        http_response_code(200);
                        echo 'Equipped';
                    } else {
                        http_response_code(500);
                        echo 'yikes server error!';
                    }
                } else {
                    http_response_code(400);
                    echo 'You dont own this item, and are probably fucking around in devtools';
                }
                break;
            case 'render':
                if ($this->user_info) {
                    $cooldown_seconds = 2;
                    $cooldown_file_path = '/tmp/render_cooldowns/';
                    $cooldown_file = $cooldown_file_path . $this->user_info["id"] . '.time';

                    if (!is_dir($cooldown_file_path)) {
                        if (!mkdir($cooldown_file_path, 0755, true)) {
                            error_log('Failed to create cooldown directory: ' . $cooldown_file_path);
                        }
                    }

                    $fp = fopen($cooldown_file, 'c+');
                    if ($fp === false) {
                        error_log('Failed to open cooldown file for locking: ' . $cooldown_file);
                        exit;
                    }

                    if (!flock($fp, LOCK_EX)) {
                        fclose($fp);
                        error_log('Failed to acquire lock on cooldown file: ' . $cooldown_file);
                        exit;
                    }

                    $current_time = time();
                    $last_render_time = 0;

                    $contents = stream_get_contents($fp, -1, 0);
                    if ($contents !== false && !empty($contents)) {
                        $last_render_time = (int) $contents;
                    }

                    $time_since_last_render = $current_time - $last_render_time;

                    if ($time_since_last_render < $cooldown_seconds) {
                        flock($fp, LOCK_UN);
                        fclose($fp);

                        $wait_time = $cooldown_seconds - $time_since_last_render;
                        http_response_code(429);
                        exit;
                    }

                    ftruncate($fp, 0);
                    rewind($fp);

                    if (fwrite($fp, $current_time) === false) {
                        error_log('Failed to write cooldown time to: ' . $cooldown_file);
                    }

                    flock($fp, LOCK_UN);
                    fclose($fp);

                    die("/social/avatar?id=" . $this->user_info["id"]);

                    exec("");
                } else {
                    header('Location: /account/logout');
                }
                break;
        }
    }
}
