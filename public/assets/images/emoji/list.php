<?php
$emojis = [
    ":laughing:" => "<img src='/forum/emoji/laughing.png'>",
    ":dumb:" => "<img src='/forum/emoji/dumb.png'>",
    ":blush:" => "<img src='/forum/emoji/blush.png'>",
    ":confused:" => "<img src='/forum/emoji/confused.png'>",
    ":bleh:" => "<img src='/forum/emoji/bleh.png'>",
    ":tongue:" => "<img src='/forum/emoji/thirst.png'>",
    ":love:" => "<img src='/forum/emoji/love.png'>",
    ":fuckyou:" => "<img src='/forum/emoji/fuckyou.png'>",
    ":crying:" => "<img src='/forum/emoji/crying.png'>",
    ":sad:" => "<img src='/forum/emoji/sad.png'>",
    ":sorrywhat:" => "<img src='/forum/emoji/sorrywhat.png'>",
    ":angry:" => "<img src='/forum/emoji/angry.png'>",
    ":wink:" => "<img src='/forum/emoji/wink.png'>",
    ":smile:" => "<img src='/forum/emoji/smile.png'>",
    ":chilling:" => "<img src='/forum/emoji/chilling.png'>",
    ":thirst:" => "<img src='/forum/emoji/thirst.png'>",
    ":aha:" => "<img src='/forum/emoji/aha.png'>",
    ":thumbsup:" => "<img src='/forum/emoji/thumbsup.png'>",
    ":partynoob:" => "<img src='/forum/emoji/partynoob.png'>",
    ":nerd:" => "<img src='/forum/emoji/nerd.png'>",
    ":420:" => "<img src='/forum/emoji/420.png'>",
    ":jester:" => "<img src='/forum/emoji/jester.png'>",
    ":bigsmile:" => "<img src='/forum/emoji/bigsmile.png'>",
];
function formatmessage($msg) {
    global $emojis;
    $rawmessage = htmlspecialchars($msg);
    $search = array_keys($emojis);
    $replace = array_values($emojis);
    return str_replace($search, $replace, $rawmessage);
}
?>