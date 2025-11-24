<?php
$status = '';
$group_number = $_GET['id'] ?? $_POST['group'] ?? 0;
$group_number = (int) $group_number;
$is_in_group = false;

if ($group_number > 0) {
	$stmt_get_groups = $this->db->prepare('
	SELECT *
	FROM groups
	WHERE id = ?
	');
	$stmt_get_groups->execute([$group_number]);
	$groupsettings = $stmt_get_groups->fetch();

	$stmt_is_in_group = $this->db->prepare('
	SELECT COUNT(*) AS c
	FROM interaction
	WHERE from_who = ? AND to_what = ? AND type = 2
	');
	$stmt_is_in_group->execute([$this->user_info["id"], $group_number]);
	$is_already_in_group = $stmt_is_in_group->fetch()["c"];

	$stmt_get_members = $this->db->prepare('
	SELECT COUNT(*) AS c
	FROM interaction
	WHERE to_what = ? AND type = 2
	');
	$stmt_get_members->execute([$group_number]);
	$group_member_count = $stmt_get_members->fetch()["c"];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	switch ($args) {
		case 'join':
			try {
				$this->db->beginTransaction();

				if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
					throw new Exception('Invalid CSRF token.');
				}

				$group_id = (int) ($_POST['group'] ?? null);

				if (empty($group_id)) {
					throw new Exception('Invalid request.');
				}

				$stmt_check_group = $this->db->prepare('SELECT * FROM groups WHERE id = ?');
				$stmt_check_group->execute([$group_id]);

				if ($stmt_check_group->rowCount() < 0) {
					throw new Exception("Group doesn't exist.");
				}

				$is_already_in_group = $this->db->prepare('SELECT COUNT(*) as c FROM interaction
					WHERE `from_who` = ? AND `to_what` = ? AND type = 2
				');
				$is_already_in_group->execute([$this->user_info['id'], $group_id]);
				$following = true;
				
				if ($is_already_in_group->fetch()["c"] > 0) {
					$stmt_leave = $this->db->prepare('DELETE FROM interaction
						WHERE `from_who` = ? AND `to_what` = ? AND type = 2
					');
					$stmt_leave->execute([$this->user_info['id'], $group_id]);
					$following = false;
				} else {
					$stmt_join = $this->db->prepare('INSERT IGNORE INTO interaction (`from_who`, `to_what`, `timestamp`, `type`)
						VALUES (?, ?, ?, 2)
					');
					$stmt_join->execute([$this->user_info['id'], $group_id, time()]);
					$following = true;
				}

				$this->db->commit();

				header("Location: /social/group?id=$group_id");
			} catch (Exception $e) {
				$this->db->rollback();
				$status = $e->getMessage();
			}
			break;

		case 'create':
			try {
				$this->db->beginTransaction();

				if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
					throw new Exception('Invalid CSRF token.');
				}

				if ($this->economy['money'] < 100) {
					throw new Exception('Insufficient funds.');
				}

				$group_name = $_POST['group_name'] ?? 'New Group';
				$group_desc = $_POST['group_description'] ?? 'This is my cool group.';

				if (mb_strlen($group_name) > 100) {
					throw new Exception('Name too long.');
				}

				if (mb_strlen($group_desc) > 1000) {
					throw new Exception('Description too long.');
				}

				$stmt_deduct = $this->db->prepare('UPDATE economy SET `money` = `money` - 100 WHERE id = ?');
				$stmt_deduct->execute([$this->user_info['id']]);

				$stmt_create = $this->db->prepare('INSERT INTO groups (`name`, `owner`, `desc`) VALUES (?, ?, ?)');
				$stmt_create->execute([$group_name, $this->user_info['id'], $group_desc]);

				$group_id = $this->db->lastInsertId();

				$stmt_joingp = $this->db->prepare('INSERT INTO interaction (from_who, to_what, `timestamp`, `type`) VALUES (?,?,?,?)');
				$stmt_joingp->execute([$this->user_info['id'], $group_id, time(), 2]);

				$this->db->commit();
				header("Location: /social/group?id=$group_id");
			} catch (Exception $e) {
				$this->db->rollback();
				$status = $e->getMessage();
			}
			break;

		case 'send':
			try {
				$this->db->beginTransaction();

				if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
					throw new Exception('Invalid CSRF token.');
				}

				if (!$is_already_in_group) {
					throw new Exception('You must join this group to post a message.');
				}
				
				$input_msg = $_POST['message'] ?? null;
				$group_id = (int) ($_POST['group'] ?? null);

				if (empty($input_msg) || empty($group_id)) {
					throw new Exception('Invalid request.');
				}

				$stmt_check_group = $this->db->prepare('SELECT * FROM groups WHERE id = ?');
				$stmt_check_group->execute([$group_id]);

				if ($stmt_check_group->rowCount() < 0) {
					throw new Exception("Group doesn't exist.");
				}

				$last_msg_id = 0;

				$stmt_get_last_post = $this->db->prepare('SELECT MAX(msgid) AS max_msgid FROM group_messages WHERE `group` = ? ORDER BY msgid DESC');
				$stmt_get_last_post->execute([$group_id]);

				if ($stmt_get_last_post->rowCount() > 0) {
					$last_msg_id = ($stmt_get_last_post->fetch()['max_msgid'] ?? 0) + 1;
				}

				$stmt_post_message = $this->db->prepare('INSERT INTO group_messages (`text`, `group`, msgid, author, creationtime) VALUES (?,?,?,?,?)');
				$stmt_post_message->execute([$input_msg, $group_id, $last_msg_id, $this->user_info['id'], time()]);

				$this->db->commit();

				header("Location: /social/group?id=$group_id");
			} catch (Exception $e) {
				$this->db->rollback();
				$status = $e->getMessage();
			}
			break;

		default:
			break;
	}
}

?>
<form id="buy_group" method="post" action="/social/group/create" class="hidden">
	<button type="button" onclick="document.getElementById('buy_group').classList.toggle('hidden')">x</button>
	<div class="aifs fc">
	<span>Name</span>
	<input type="text" placeholder="LSDBLOX Fan Group!" name="group_name" id="group_name" required>
	<br>
	Description
	<br>
	<textarea type="textarea" placeholder="All the LSDBLOXERS put ya hands up!" rows="8" cols="20" name="group_description" id="group_description"></textarea>
	<input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
	<br>
	<input type="submit" value="¥100">
	</div>
</form>
<div class="fr">
<div class="fc">
<?= $status ?>
<div class="border fc">
	<button onclick="document.getElementById('buy_group').className = 'focus'">Buy Group</button>
	<span>├────┤</span>
	<?php
	$stmt_get_what_groups_youre_in = $this->db->prepare('
	SELECT g.*
	FROM interaction AS i
	JOIN groups AS g ON i.to_what = g.id
	WHERE i.from_who = ? AND i.type = 2
	');
	$stmt_get_what_groups_youre_in->execute([$this->user_info['id']]);
	$groups = $stmt_get_what_groups_youre_in->fetchAll();

	if (count($groups) > 0) {
		foreach ($groups as $group) {
			?>
			<a href="/social/group?id=<?= $group['id'] ?>"><?= $group['name'] ?></a>
			<?php
		}
	} else {
		echo "You're in no groups";
	}
	?>
</div>
</div>
<?php
if ($group_number > 0 && $stmt_get_groups->rowCount() > 0) {
	?>
<div class="fc border aifs" style="overflow:scroll;max-height:90vh">
<h3><?= htmlspecialchars($groupsettings['name']) ?></h3>
<span><?= htmlspecialchars($groupsettings['desc']) ?></span>
<div class="fr v_mid">
	<form id="plrform" method="post" action="/social/group/join">
		<input type="submit" value="Join">
		<input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
		<input type="hidden" name="group" value="<?= $groupsettings['id'] ?>">
	</form>
	<span><?=$group_member_count?> MEMBERS</span>
</div>
<?php if ($is_already_in_group): ?>
<form id="plrform" method="post" action="/social/group/send" class="feed_compose">
	<textarea rows="2" cols="24" type="textarea" id="message" name="message" maxlength="100" required placeholder="hi i like hello kitten"></textarea>
    <input type="submit" value="Send" style="margin-top:15px;">
    <input type="hidden" name="csrftoken" value="<?= $_SESSION['csrftoken'] ?>">
    <input type="hidden" name="group" value="<?= $groupsettings['id'] ?>">
</form>
<?php endif;?>
<div class="border aifs" style="justify-content:revert;">
	<table>
<?php
$stmt_get_messages = $this->db->prepare('
SELECT author, creationtime, text
FROM group_messages
WHERE `group` = ?
ORDER BY id DESC
');
$stmt_get_messages->execute([$group_number]);
if ($stmt_get_messages->rowCount() > 0) {
	$messages_fetch = $stmt_get_messages->fetchAll();

	foreach ($messages_fetch as $messages) {
		$author = $messages['author'];
		$time = $messages['creationtime'];
		$message = $this->formatmessage($messages['text']);
		?>
	<tr>
		<td class="fc">
			<a href="/social/profile?id=<?= $author ?>" class="fc"><?= $this->getuser($author)['username'] // i should look into stopping use of this function ?>
			<img src="/social/avatar?id=<?= $author ?>" height="100"></a>
			<span title="<?= date('D, dS M Y H:i:s', $time) ?>"><?= date('d-m-y H:i:s', $time) ?></span>
		</td>
		<td style="text-align:left;">
			<?= $message ?>
		</td>
	</tr>
	<?php
	}
} else {
	echo 'No messages, post anything!';
}
?>
</table>
</div>
</div>
<?php } ?>
</div>