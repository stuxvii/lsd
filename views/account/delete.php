<?php
$msg = "";
$curstep = "text";
$instructions = "(1/2) Enter the sentence \"I wish to delete my account\" <br>in the box below to proceed.";
if (!$this->user_info) {
    $instructions = "Please <a href='/account/login'>log in.</a>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($this->user_info) {
        if (isset($_POST["text"])) {
            if ($_POST["text"] === "I wish to delete my account") {
                $curstep = "password";
                $instructions = "(2/2) Enter your password to confirm<br>the erasure of your account.<br>";
            } else {
                $msg = "Confirmation phrase was incorrect.<br>Check your spelling?";
            }
        }

        elseif (isset($_POST["password"])) {
            $curstep = "password";
            $instructions = "(2/2) Enter your password to confirm<br>the erasure of your account.<br>";
            
            if (password_verify($_POST["password"], $this->user_info["pass"])) {
                $delete_config_stmt = $this->db->prepare("DELETE FROM config WHERE id = ?");
                $delete_config_stmt->execute([$this->user_info["id"]]);
                
                $delete_avatar_stmt = $this->db->prepare("DELETE FROM profiles WHERE id = ?");
                $delete_avatar_stmt->execute([$this->user_info["id"]]);
                
                $delete_avatar_stmt = $this->db->prepare("DELETE FROM economy WHERE id = ?");
                $delete_avatar_stmt->execute([$this->user_info["id"]]);

                $delete_user_stmt = $this->db->prepare("UPDATE users SET id = NULL, username = '[REDACTED]', discordid = NULL, pass = NULL, registerts = NULL, WHERE id = ?");
                $delete_user_stmt->execute([$this->user_info["id"]]);
                
                setcookie('_ROBLOSECURITY', '', time() - 3600, '/');
                
                header("Location: https://www.google.com", true, 303);
                exit;
            } else {
                $msg = "Incorrect password.";
            }
        }
    } else {
        $msg = "Brother you NEED to log in to delete your account. I'm sorry.";
    }
}
?>
<div class="border">
    <em>Account deletion</em>
    <form id="plrform" method="post" action="">
        <hr>
        <?php if ($curstep === "text") { ?>
            <span>If you aren't satisfied with the service, or are encountering <br>any issues, please tell us about it on <a href="https://discord.gg/7JwYGHAvJV">our Discord server.</a></span>
                <br>
            <span>Please note that the deletion of your account does not mean that
                <br>
                your UGC will be deleted. To request the deletion of your UGC, send an email to us <b>before</b>
                <br>
                deleting your account, as it will be desassociated with it if you terminate your account.
            </span>
            <hr>
            <br>
        <?php } ?>
        
        <span><?= $instructions; ?></span>
        <br>
        <input type="<?= $curstep === 'password' ? 'password' : 'text'; ?>" 
            name="<?= $curstep; ?>" 
            style="width:400px;" 
            autocomplete="<?= $curstep === 'password' ? 'current-password' : 'off'; ?>"
            <?php if (!$this->user_info) { echo "disabled"; }?>
            >
        <input type="submit" value="Continue" <?php if (!$this->user_info) { echo "disabled"; }?>>
        <br>
        <?php if (!empty($msg)) { echo $msg; } ?>
    </form>
</div>