<?php
if (isset($_COOKIE['_ROBLOSECURITY'])) {
    header("Location: /");
    exit;
}

$key = $_GET['key'] ?? $_POST['key'] ?? null;

$stmt = $this->db->prepare("SELECT * FROM onetimelinks WHERE `link` = ? and `type` = 1");
$stmt->execute([$key]);
$user_data = $stmt ? $stmt->fetch() : false;

$uid = $user_data["user"];
$ts = $user_data["creationdate"] + 3600;

$curtime = time();
$msg = "";
if ($curtime > $ts) {
    header("Location: /");
    exit;
}

function error($msg) {
    echo "<span>$msg</span>";
    return;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $password = $_POST['password'];
    $confirmation = $_POST['confirmation'];
    $key = $_POST['key'];
    if (empty($password)) {
        error("Please enter a new password");
        return;
    }
    if (empty($confirmation)) {
        error("Please enter the password confirmation");
        return;
    }
    if (strlen($password) < 15) {
        error("New password is too short, the minimum length is 15 characters.");
        return;
    }
    if (strlen($password) > 512) {
        error("New password is too long!, the maximum length is 512 characters.");
        return;
    }
    if ($password != $confirmation) {
        error("Password and confirmation do not match.");
        return;
    }

    $data = bin2hex(random_bytes(64));

    $newpassword = password_hash($password, PASSWORD_ARGON2ID);

    $stmt = $this->db->prepare("UPDATE users SET pass = ?, authuuid = ? WHERE id = ?");
    $stmt->execute([$newpassword, $data, $uid]);

    $stmt = $this->db->prepare("UPDATE onetimelinks SET creationdate = 0 WHERE `link` = ?");
    $stmt->execute([$key]);

    $msg = "Password changed. <a href='/account/login'>Welcome back!</a>";
}
if (!$key) {
    header("Location: /");
    exit;
}

ob_start();
?>
<style>
.content {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction:column;
}
</style>
<div class="deadcenter" style="justify-content: center;">
    <form method="post" action="/account/recovery">
        <input class="hidden focus" type="password" name="key" value="<?php echo $_GET['key'];?>">
        New password: <input type="password" name="password">
        <br>
        Confirmation: <input type="password" name="confirmation">
        <br>
        <input type="submit" name="submit" value="Modify (modify and sever)">
    </form>
    <br>
    <div class="fc aifs">
        <?=$msg?>
    </div>
</div>