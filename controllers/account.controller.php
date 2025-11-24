<?php
class AccountController extends BaseController {
    public function Logout() {
        session_unset();
        setcookie('.ROBLOSECURITY', "", time() - 3600, "/", "lsdblox.cc", true, true);
        setcookie('_ROBLOSECURITY', "", time() - 3600, "/", "lsdblox.cc", true, true);
        setcookie('auth', "", time() - 3600, "/", "lsdblox.cc", true, true);
        header("Location: /");
        exit;
    }
    
    public function Login() {
        ob_start();
        $msg = "";
        function error($reason) {
            return "<img src=\"/assets/images/error.png\" height='32'><span class=\"info\">$reason</span>";
        }
        $do_db_request = true;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $invalid = "The credentials you provided are invalid.";
            
            $un = $_POST['name'] ?? null;
            $pass = $_POST['pass'] ?? null;
            
            if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $un)) {
                $msg = error($invalid);
                $do_db_request = false;
            }
            
            $stmt = $this->db->prepare("SELECT pass, authuuid FROM users WHERE username = ?");
            $stmt->execute([$un]);
            
            $user_data = $stmt ? $stmt->fetch() : false;
            
            if ($do_db_request) {
                if ($user_data && password_verify($pass, $user_data['pass'])) {
                    setcookie('.ROBLOSECURITY', $user_data['authuuid'], ['expires' => time() + (86400 * 30),'path' => '/','domain' => '.lsdblox.cc','secure' => true,'httponly' => true,'samesite' => 'Strict']);
                    header("Location: /");
                    exit;
                } else {
                    $msg = error($invalid);
                }
            }
        }
        require_once ROOT_PATH . "/views/account/login.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
    
    public function Config() {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        require_once ROOT_PATH . "/views/account/config.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
    
    public function Main() {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        ob_start();
        require_once ROOT_PATH . "/views/account/main.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
    
    public function Delete() {
        if ($this->user_info === null) {
            http_response_code(400);
            header('Location: /');
            exit;
        }
        ob_start();
        require_once ROOT_PATH . "/views/account/delete.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
    
    public function Recovery() {
        ob_start();
        require_once ROOT_PATH . "/views/account/recovery.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }

    public function Register() {
        function key_gen() {
            return bin2hex(random_bytes(64));
        }
        function error($reason) {
            return "<img src=\"error.png\" height='32'><span class=\"info\">$reason</span>";
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $un = $_POST['name'];
            $key = trim($_POST['key']);
            $pass = $_POST['pass'];
            $confirmpass = $_POST['confirmpass'];
            $can_proceed = true;

            $usernamevalidateregex = '/^[a-zA-Z0-9_]{3,20}$/';

            if ($key === '' || $un === '' || $pass === '') {
                $msg = error("An invitation key, username, and password are required.");
                $can_proceed = false;
            }

            if (strlen($pass) < 15) {
                $msg = error("Password is not long enough. Suggestion: 6 random uncommon english words.");
                $can_proceed = false;
            }

            if (strlen($pass) > 512) {
                $msg = error("Password is too long! Keep it under 512 characters please.");
                $can_proceed = false;
            }

            if ($pass != $confirmpass) {
                $msg = error("Passwords do not match.");
                $can_proceed = false;
            }

            if (!preg_match($usernamevalidateregex, $un)) {
                $msg = error("The username '$un' is invalid.");
                $can_proceed = false;
            }

            $stmtcheckusername = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
            $stmtcheckusername->execute([$un]);
            
            if ($stmtcheckusername && $can_proceed) {
                $row = $stmtcheckusername->fetch();
                $user_count = $row['count'];
            } else {
                error_log("DB Error checking username: " . $this->db->error);
                $msg = error("Internal error while checking username availability.");
                $can_proceed = false;
            }

            if ($user_count > 0) {
                $msg = error("The username '$un' is already taken.");
            } else {
                if ($can_proceed) {
                    $stmt = $this->db->prepare("
                        UPDATE users 
                        SET 
                            username = ?,
                            pass = ?,
                            registerts = ?,
                            authuuid = ?
                        WHERE invkey = ? AND (username IS NULL OR username = '')
                    ");

                    $hashpw = password_hash($pass, PASSWORD_ARGON2ID);
                    $stmt->execute([ 
                        $un, 
                        $hashpw,
                        time(), 
                        key_gen(), 
                        $key
                    ]
                    );

                    if ($stmt->rowCount() > 0) {
                        header("Location: /account/login");
                        exit;
                    } else {
                        $msg = error("Failed to register. The key may be invalid or already used.");
                    }
                }
            }
        }
        ob_start();
        require_once ROOT_PATH . "/views/account/register.php";
        $page_content = ob_get_clean();
        require_once ROOT_PATH . "/views/layout/template.php";
    }
}
?>