<?php

class BaseController {
    protected $model;
    protected $db;
    protected $user_info = null;
    protected $preferences = null;
    protected $profile = null;
    protected $economy = null;

    public function __CONSTRUCT() {
        $this->db = Database::connect();
        $this->model = new UserModel();

        if (isset($_COOKIE["_ROBLOSECURITY"])) {
            $this->user_info = $this->model->findUserByAuthToken($_COOKIE["_ROBLOSECURITY"]);
        }

        if (isset($this->user_info['id'])) {
            $user_id = $this->user_info['id'];
            $this->preferences = $this->model->getUserSettings($user_id);
            $this->profile = $this->model->getUserProfile($user_id);
            $this->economy = $this->model->getUserEconomy($user_id);
            if (!$this->preferences) {
                $stmt = $this->db->prepare('INSERT IGNORE INTO config (id) VALUES (?)');
                $stmt->execute([$user_id]);
            }
            if (!$this->profile) {
                $stmt = $this->db->prepare('INSERT IGNORE INTO profiles (id) VALUES (?)');
                $stmt->execute([$user_id]);
            }
            if (!$this->economy) {
                $stmt = $this->db->prepare('INSERT IGNORE INTO economy (id) VALUES (?)');
                $stmt->execute([$user_id]);
            }
        }
    }
    
    public function get_place(int $place_id): Array {
        $get_place_stmt = $this->db->prepare("SELECT `owner`, `name`, `desc`, `public` FROM items WHERE id = ?");
        $get_place_stmt->execute([$place_id]);
        $fetch = $get_place_stmt->fetch();

        $data = [
            "id" => $place_id,
            "rootPlaceId" => $place_id,
            "name" => $fetch["name"],
            "description" => $fetch["desc"],
            "privacyType" => "Public",
            "creatorType" => "User",
            "creatorTargetId" => $fetch["owner"],
            "creatorName" => $this->getuser($fetch["owner"])["username"],
            "created" => date("Y-m-d", $fetch["uploadts"]),
            "updated" => date("Y-m-d", $fetch["uploadts"]),
            "isArchived" => false,
            "isActive" => (bool)$fetch["public"],
        ];

        return $data;
    }

    public function getuser(int $user_id): Array {
        $getuserstmt = $this->db->prepare("
        SELECT username, discordid, isoperator, registerts
        FROM users 
        WHERE id = ?
        ");
        $getuserstmt->execute([$user_id]);
        $userinfo = $getuserstmt->fetch(PDO::FETCH_ASSOC);
        return $userinfo;
    }


    public function formatmessage($msg) {
        $emojis = [
            ":laughing:" => "<img src='/assets/images/emoji/laughing.png'>",
            ":dumb:" => "<img src='/assets/images/emoji/dumb.png'>",
            ":blush:" => "<img src='/assets/images/emoji/blush.png'>",
            ":confused:" => "<img src='/assets/images/emoji/confused.png'>",
            ":bleh:" => "<img src='/assets/images/emoji/bleh.png'>",
            ":tongue:" => "<img src='/assets/images/emoji/thirst.png'>",
            ":love:" => "<img src='/assets/images/emoji/love.png'>",
            ":fuckyou:" => "<img src='/assets/images/emoji/fuckyou.png'>",
            ":crying:" => "<img src='/assets/images/emoji/crying.png'>",
            ":sad:" => "<img src='/assets/images/emoji/sad.png'>",
            ":sorrywhat:" => "<img src='/assets/images/emoji/sorrywhat.png'>",
            ":angry:" => "<img src='/assets/images/emoji/angry.png'>",
            ":wink:" => "<img src='/assets/images/emoji/wink.png'>",
            ":smile:" => "<img src='/assets/images/emoji/smile.png'>",
            ":chilling:" => "<img src='/assets/images/emoji/chilling.png'>",
            ":thirst:" => "<img src='/assets/images/emoji/thirst.png'>",
            ":aha:" => "<img src='/assets/images/emoji/aha.png'>",
            ":thumbsup:" => "<img src='/assets/images/emoji/thumbsup.png'>",
            ":partynoob:" => "<img src='/assets/images/emoji/partynoob.png'>",
            ":nerd:" => "<img src='/assets/images/emoji/nerd.png'>",
            ":420:" => "<img src='/assets/images/emoji/420.png'>",
            ":jester:" => "<img src='/assets/images/emoji/jester.png'>",
            ":bigsmile:" => "<img src='/assets/images/emoji/bigsmile.png'>",
        ];
        $cleanmessage = htmlspecialchars($msg);
        $search = array_keys($emojis);
        $replace = array_values($emojis);
        $emojis_rendered = str_replace($search, $replace, $cleanmessage);
        $new_message = trim($emojis_rendered);
        return nl2br($new_message);
    }
}

class ItemData {
    private $itemid;
    public function __CONSTRUCT() {
        $this->db = Database::connect();
        $stmtgetitem = $this->db->prepare('
        SELECT *
        FROM items
        WHERE id = ?
        ');
        $stmtgetitem->execute([$itemid]);

        $row = [];
        $itemname = NULL;
        $owned = false;

        $invarray = json_decode($inv);
        if (!empty($invarray) && in_array($itemid,$invarray)) {
            $owned = true;
        }

        if ($stmtgetitem->rowCount() > 0) {
            $row = $result->fetch_assoc();

            // Fetch basic info
            $value = $row['value'];
            $itemname = htmlspecialchars($row['name']);
            $itemdesc = htmlspecialchars($row['desc']);
            $itemupts = $row['uploadts']; // upload date as a unix timestamp
            $owner = $row['owner'];
            $type = $row['type'];
            $public = $row['public'];
            $approved = $row['approved'];
            if ($approved == 2) {
                $approved = null;
            }
            
            // We need to get the owner of the item,
            $stmtgetowner = $db->prepare('
            SELECT username
            FROM users
            WHERE id = ?
            ');
            $stmtgetowner->execute([$owner]);
            $ownrow = $result->fetch();
            $ownername = htmlspecialchars($ownrow['username']);
        }
    }
}
function time_elapsed_string($lastseendate) {
    $current_time = time();
    $diff = $current_time - $lastseendate;

    if ($diff < 0) {
        return "in the future";
    }

    if ($diff < 60) {
        if ($diff < 1) {
            return "just now";
        }
        $seconds = max(1, $diff);
        return $seconds . ' second' . ($seconds !== 1 ? 's' : '') . ' ago';
    }

    $time_units = array(
        31536000 => 'year',
        2592000  => 'month',
        604800   => 'week',
        86400    => 'day',
        3600     => 'hour',
        60       => 'minute',
    );

    foreach ($time_units as $seconds => $unit) {
        if ($diff >= $seconds) {
            $value = floor($diff / $seconds);
            return $value . ' ' . $unit . ($value !== 1 ? 's' : '') . ' ago';
        }
    }
    
    return "just now";
}

function verify_mesh(string $mesh) {
    $regex = "/\[.*?\]/";
    $lines = explode("\n", trim($mesh));

    if (count($lines) != 3) {
        return false;
    }

    if (trim($lines[0]) != "version 1.00") {
        return false;
    }

    $triplets = preg_match_all($regex, trim($lines[2]));
    $faces = $triplets / 9; // every 9 triplets makes a face

    if ($faces == (int)trim($lines[1])) {
        return true;
    } else {
        return false;
    }
}

