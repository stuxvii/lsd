<?php
if (isset($this->user_info["id"])) {
    header("Location: /");
}
?>

<div class="deadcenter" style="justify-content: center;">
    <form method="post" action="/account/login" class="fc border">
        <span>Username: <input type="text" name="name"></span>
        <span>Password: <input type="password" name="pass"></span>
        <a href="#" onclick="alert('Go to #bots on the lsdblox server and issue the \'lsd-resetpass\' command. After that, lsdbot will dm you a temporary password reset link.')">I forgot my password</a>
        <input type="submit" name="submit" value="Login">
    </form>
    <br>
    <div class="msgbox" style="align-items: center;flex-direction: row;">
        <br>
        <?=$msg?>
    </div>
</div>