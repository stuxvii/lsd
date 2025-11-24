
<?php
if (isset($_COOKIE['_ROBLOSECURITY'])) {
    header("Location: /");
    exit;
}
$secure = null;
if (isset($_SERVER['HTTPS'])) {
    $secure =  $_SERVER['HTTPS'];
} else {
    header("Location: /");
}
?>
<div class="midh">
    <div id="deleteifsuccess">
        <form method="post" action="/account/register" class="fc border">
            <span>Username: </span>
            <input type="text" name="name">
            <span>(3-20 chars, a-z/0-9/_)</span>
            <br>
            <span>Password:</span>
            <input type="password" name="pass">
            <span>(must be 15 characters or more)</span>
            <br>
            <span>Password confirmation:</span>
            <input type="password" name="confirmpass">
            <br>
            <span>Inv Key:</span>
            <input type="password" name="key">
            <br>
            <span>By pressing "Register" you agree to LSDBLOX's <br><a href="/info/privacypolicy">Privacy Policy</a> and <a href="/info/termsofservice">Terms of Service</a></span>
            <br>
            <input type="submit" name="submit" value="Register">
        </form>
        <br>
    </div>
    
    <div class="msgbox">
        <br>
        <?=$msg?>
    </div>
</div>