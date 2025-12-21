<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST["csrftoken"]) || $_POST["csrftoken"] != $_SESSION["csrftoken"]) {
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'json') !== false) {
            die(json_encode(["status" => "Invalid CSRF Token"]));
        }
        die("Invalid CSRF Token");
    }

    if (isset($action) && $action === "follow") {
        header('Content-Type: application/json');

        $target_user_id = $_POST["user"] ?? null;

        if (!$target_user_id || !is_numeric($target_user_id)) {
            echo json_encode(["status" => "User is not set"]);
            exit;
        }

        if ($target_user_id == $this->user_info["id"]) {
            echo json_encode(["status" => "Can't follow yourself"]);
            exit;
        }

        $stmt_check = $this->db->prepare('SELECT 1 FROM interaction WHERE `from_who` = ? AND `to_what` = ? AND type = 1');
        $stmt_check->execute([$this->user_info['id'], $target_user_id]);
        $exists = $stmt_check->fetchColumn();

        if ($exists) {
            $stmt = $this->db->prepare('DELETE FROM interaction WHERE `from_who` = ? AND `to_what` = ? AND type = 1');
            $stmt->execute([$this->user_info['id'], $target_user_id]);
            $is_following = false;
        } else {
            $stmt = $this->db->prepare('INSERT IGNORE INTO interaction (`from_who`, `to_what`, `timestamp`, `type`) VALUES (?, ?, ?, 1)');
            $stmt->execute([$this->user_info['id'], $target_user_id, time()]);
            $is_following = true;
        }

        $stmt_count = $this->db->prepare('SELECT COUNT(*) FROM interaction WHERE to_what = ? AND type = 1');
        $stmt_count->execute([$target_user_id]);
        $count = $stmt_count->fetchColumn();

        echo json_encode(["status" => $is_following, "followers" => $count]);
        exit;
    }

    if (isset($_POST['desc'])) {
        $target_uid = (int)($_POST['userid'] ?? 0);

        if ($target_uid !== (int)$this->user_info['id']) {
            http_response_code(403);
            exit(json_encode(['status' => 'error', 'message' => 'Unauthorized.']));
        }

        $fields = ['showposts', 'showinventory', 'showlastseen', 'showcountry', 'showfollowing', 'showfollowers', 'showmutuals'];
        $params = [
            $_POST['country'] ?? 'NONE',
            $_POST['desc'] ?? '',
        ];

        foreach ($fields as $field) {
            $params[] = isset($_POST[$field]) ? 1 : 0;
        }
        $params[] = $this->user_info['id'];

        $sql = "UPDATE profiles SET country = ?, `desc` = ?, " . implode(' = ?, ', $fields) . " = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $_SESSION['csrftoken'] = bin2hex(random_bytes(32));
        header("Location: profile?id={$this->user_info['id']}");
        exit;
    }

    if (isset($_POST['userid'])) {
        $uid = (int)$this->user_info['id'];
        if ((int)$_POST['userid'] !== $uid) {
             exit(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
        }

        ob_start();
        ?>
        <form method="post" action="/social/profile?id=<?= $uid ?>" style="color:white;" class="fc aifs">
            <label for="desc">Description</label>
            <textarea id="desc" name="desc" style="margin-top:6px;" cols="32" rows="12"><?= htmlspecialchars($this->profile['desc'] ?? '') ?></textarea>

            <label><input type="checkbox" name="showinventory" <?= ($this->profile['showinventory'] ?? 0) ? 'checked' : '' ?>> Show wearing</label>
            <label><input type="checkbox" name="showposts" <?= ($this->profile['showposts'] ?? 0) ? 'checked' : '' ?>> Show feed posts</label>
            <label><input type="checkbox" name="showlastseen" <?= ($this->profile['showlastseen'] ?? 0) ? 'checked' : '' ?>> Show last-seen</label>
            <label><input type="checkbox" name="showcountry" <?= ($this->profile['showcountry'] ?? 0) ? 'checked' : '' ?>> Show country</label>
            <label><input type="checkbox" name="showfollowing" <?= ($this->profile['showfollowing'] ?? 0) ? 'checked' : '' ?>> Show following</label>
            <label><input type="checkbox" name="showfollowers" <?= ($this->profile['showfollowers'] ?? 0) ? 'checked' : '' ?>> Show followers</label>
            <label><input type="checkbox" name="showmutuals" <?= ($this->profile['showmutuals'] ?? 0) ? 'checked' : '' ?>> Show mutuals</label>

            <label for="country">Country</label>
            <select id="country" name="country" style="margin-top:6px;" autocomplete="country">
                <?php foreach ($GLOBALS['countries_list_iso_codes'] as $code => $name): ?>
                    <option value="<?= $code ?>" <?= (($this->profile['country'] ?? 'NONE') === $code) ? 'selected' : '' ?>><?= $name ?></option>
                <?php endforeach; ?>
            </select>
            
            <input type="number" name="userid" value="<?= $uid ?>" class="focus hidden">
            <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>" required>
            <input type="submit" value="Save">
        </form>
        <?php
        $html = ob_get_clean();
        echo json_encode(['status' => 'success', 'message' => $html]);
        exit;
    }
}

if (!isset($this->other_user_info)) {
    echo '<div class="fc">User not found.</div>';
    return;
}

$this->other_preferences = $this->model->getUserSettings($this->other_user_info['id']);
$this->other_economy = $this->model->getUserEconomy($this->other_user_info['id']);
$this->other_profile = $this->model->getUserProfile($this->other_user_info['id']);
$this->other_economy["inv"] = json_decode($this->other_economy["inv"]);

ob_start();
?>
<meta property="og:title" content="<?= htmlspecialchars($this->other_user_info["username"]) ?>">
<meta property="og:description" content="<?= htmlspecialchars($this->other_profile["desc"]) ?>">
<meta property="og:image" content="https://lsdblox.cc/social/avatar?id=<?= $this->other_uid ?>&amp;=<?= time() ?>">
<meta property="og:type" content="website">
<?php
$meta_tags = ob_get_clean();

$stat_followers = 0;
$stat_following = 0;
$stat_mutuals = 0;
$is_following_user = false;

if ($this->other_profile["showfollowers"]) {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM interaction WHERE to_what = ? AND `type` = 1');
    $stmt->execute([$this->other_user_info['id']]);
    $stat_followers = $stmt->fetchColumn();
}

if ($this->other_profile["showfollowing"]) {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM interaction WHERE from_who = ? AND `type` = 1');
    $stmt->execute([$this->other_user_info['id']]);
    $stat_following = $stmt->fetchColumn();
}

if ($this->other_profile["showmutuals"]) {
    $stmt = $this->db->prepare('
        SELECT COUNT(T1.to_what)
        FROM interaction AS T1
        INNER JOIN interaction AS T2 ON T1.from_who = T2.to_what
        WHERE T1.to_what = ? AND T2.from_who = ? AND T1.type = 1 AND T2.type = 1
    ');
    $stmt->execute([$this->other_user_info['id'], $this->other_user_info['id']]);
    $stat_mutuals = $stmt->fetchColumn();
}

if ($this->other_user_info['id'] != $this->user_info['id']) {
    $stmt = $this->db->prepare('SELECT COUNT(*) FROM interaction WHERE from_who = ? AND to_what = ? AND type = 1');
    $stmt->execute([$this->user_info['id'], $this->other_user_info['id']]);
    $is_following_user = ($stmt->fetchColumn() >= 1);
}
?>

<div id="manage" class="hidden"></div>

<div class="fc">
    <div class="border" style="flex-direction:row;align-items:normal;justify-content:space-between;height:200%;">
        <img class='profileimg' src="/social/avatar?id=<?= $this->other_user_info['id'] ?>">

        <div class="fc" style="justify-content: space-between">
            <div class="fc aifs">
                <h1>
                    <?= $this->other_user_info['id'] == 2 ? "<span title='This user donated.'>🌟</span>" : "" ?>
                    <?= $this->other_user_info['isoperator'] ? '「' . htmlspecialchars($this->other_user_info['username']) . '」' : htmlspecialchars($this->other_user_info['username']); ?>
                </h1>

                <?php if ($this->other_user_info['id'] === $this->user_info['id']): ?>
                    <a href="#" onclick="showeditpanel(); return false;">Edit</a>
                <?php endif; ?>

                <?php if ($this->other_profile['showcountry']): ?>
                    <span><?= country2flag(htmlspecialchars($this->other_profile['country'])) ?></span>
                <?php endif; ?>

                <?php if ($this->other_profile['showlastseen']): ?>
                    <div>
                        <abbr title="In intervals of 12 hours">Last login:</abbr>
                        <span title="<?= date('jS l, F Y', $this->other_economy['lastbuxclaim']); ?>">
                            <?= time_elapsed_string($this->other_economy['lastbuxclaim']); ?>
                        </span>
                    </div>
                <?php endif; ?>

                <span title="<?= date('jS l, F Y', $this->other_user_info['registerts']); ?>">
                    Join date: <?= date('d-m-y', $this->other_user_info['registerts']); ?>
                </span>

                <span><?= htmlspecialchars($this->other_profile['desc']) ?></span>
            </div>

            <div class="fc">
                <div>
                    <?php if ($this->other_profile["showfollowers"]): ?>
                        <span id="follower_count">Followers: <?= $stat_followers ?></span>
                    <?php endif; ?>

                    <?php if ($this->other_profile["showfollowing"]): ?>
                        <span>Following: <?= $stat_following ?></span>
                    <?php endif; ?>

                    <?php if ($this->other_profile["showmutuals"]): ?>
                        <span>Mutuals: <?= $stat_mutuals ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($this->other_user_info['id'] != $this->user_info['id']): ?>
                    <button onclick="follow(<?= $this->other_user_info['id'] ?>)" id="follow_button">
                        <?= $is_following_user ? "Unfollow" : "Follow" ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($this->other_profile['showinventory'] || $this->other_profile['showposts']): ?>
        <div class="fr wfa">
            
            <?php if ($this->other_profile['showinventory']): ?>
                <div>
                    <strong>STEAL THEIR LOOK!</strong>
                    <div class="catalogitemborder" style="width:auto">
                        <?php
                        $equipped = json_decode($this->other_profile['equipped']);
                        if (!empty($equipped)) {
                            foreach ($equipped as $itemid) {
                                echo '<a href="/asset/item?id=' . $itemid . '"><img src="/asset/thumbnail?id=' . $itemid . '" height="100" width="100"></a>';
                            }
                        } else {
                            echo 'No items in inventory.';
                        }
                        ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($this->other_profile['showposts']): ?>
                <div class="border fc aifs" style="overflow:scroll; max-height:-webkit-fill-available;">
                    <span class="msgdate">Last 6 Posts</span>
                    <?php
                    $stmtgetposts = $this->db->prepare('SELECT id, content, uploadtimestamp FROM feed WHERE author = ? ORDER BY id DESC LIMIT 6');
                    $stmtgetposts->execute([$this->other_user_info['id']]);
                    
                    if ($stmtgetposts->rowCount() > 0):
                        while ($row = $stmtgetposts->fetch()): ?>
                            <div class="fc aifs" style="padding:0;">
                                <a href="/social/post?id=<?= $row['id']; ?>"><?= mb_strimwidth(htmlspecialchars($row['content']), 0, 14, '..') ?></a>
                                <span class="msgdate" style="font-size:0.6em;"><?= date('Y-d-m H:i:s', $row['uploadtimestamp']) ?></span>
                            </div>
                        <?php endwhile;
                    else:
                        echo 'No posts.';
                    endif;
                    ?>
                </div>
            <?php endif; ?>

        </div>
    <?php endif; ?>
</div>

<script>
    <?php if ($this->other_user_info['id'] === $this->user_info['id']): ?>
    function showeditpanel() {
        const container = document.getElementById('manage');
        const postData = new URLSearchParams();
        postData.append('userid', <?= $this->user_info['id'] ?>);
        postData.append('csrftoken', "<?= $_SESSION["csrftoken"] ?>");

        fetch('/social/profile', {
            method: 'POST',
            body: postData
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(data => {
            if (data.status === 'success') {
                container.className = 'focus';
                container.innerHTML = data.message;
            } else {
                alert(data.message || 'Unknown error.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Connection error.');
        });
    }
    <?php endif; ?>

    function follow(id) {
        const btn = document.getElementById('follow_button');
        const countSpan = document.getElementById('follower_count');
        
        const postData = new URLSearchParams();
        postData.append('user', id);
        postData.append('csrftoken', "<?= $_SESSION["csrftoken"] ?>");

        fetch('/social/profile/follow', {
            method: 'POST',
            body: postData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === "User is not set" || data.status === "Can't follow yourself") {
                alert(data.status);
            } else {
                btn.innerHTML = data.status === true ? "Unfollow" : "Follow";
                if(countSpan) countSpan.innerHTML = "Followers: " + data.followers;
            }
        })
        .catch(console.error);
    }
</script>