<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$usrid = (int) $_POST['userid'] ?? $_GET['id'];
	if (isset($_POST['desc']) && isset($_POST['csrftoken'])) {
		if ($_POST['csrftoken'] != $_SESSION['csrftoken']) {
			die('Invalid CSRF token.');
		}

		if ($usrid !== $this->user_info['id']) {
			http_response_code(403);
			exit(json_encode(['status' => 'error', 'message' => 'Unauthorized to edit this profile.']));
		}
		$uid = $this->user_info['id'];
		$country = $_POST['country'] ?? 'NONE';
		$desc = $_POST['desc'] ?? '';

		$showposts = (int) (isset($_POST['showposts']) ? true : false);
		$showinventory = (int) (isset($_POST['showinventory']) ? true : false);
		$showlastseen = (int) (isset($_POST['showlastseen']) ? true : false);
		$showcountry = (int) (isset($_POST['showcountry']) ? true : false);
		$showfollowing = (int) (isset($_POST['showfollowing']) ? true : false);
		$showfollowers = (int) (isset($_POST['showfollowers']) ? true : false);
		$showmutuals = (int) (isset($_POST['showmutuals']) ? true : false);

		$stmtupdateprofile = $this->db->prepare('
			UPDATE profiles
			SET country = ?, `desc` = ?, showposts = ?, showinventory = ?, showlastseen = ?, showcountry = ?, showfollowing = ?, showfollowers = ?, showmutuals = ?
			WHERE id = ?
		');
		$stmtupdateprofile->execute([
			$country,
			$desc,
			$showposts,
			$showinventory,
			$showlastseen,
			$showcountry,
			$showfollowing,
			$showfollowers,
			$showmutuals,
			$uid
		]);
		$_SESSION['csrftoken'] = bin2hex(random_bytes(32));
		header("Location: profile?id={$uid}");
		exit;
	}
	ob_start();
	?>
    <form method="post" action="/social/profile?id=<?= $this->user_info['id'] ?>" style="color:white;" class="fc aifs">
        <label for="desc">Description</label>
        <textarea id="desc" name="desc" style="margin-top:6px;" cols="32" rows="12"><?= htmlspecialchars($this->profile['desc'] ?? '') ?></textarea>

        <label for="showinventory">
            <input type="checkbox" name="showinventory" id="showinventory" <?= ($this->profile['showinventory'] ?? 0) ? 'checked' : '' ?>> Show wearing
        </label>

        <label for="showposts">
            <input type="checkbox" name="showposts" id="showposts" <?= ($this->profile['showposts'] ?? 0) ? 'checked' : '' ?>> Show feed posts
        </label>

        <label for="showlastseen">
            <input type="checkbox" name="showlastseen" id="showlastseen" <?= ($this->profile['showlastseen'] ?? 0) ? 'checked' : '' ?>> Show last-seen
        </label>

        <label for="showcountry">
            <input type="checkbox" name="showcountry" id="showcountry" <?= ($this->profile['showcountry'] ?? 0) ? 'checked' : '' ?>> Show country
        </label>

        <label for="showfollowing">
            <input type="checkbox" name="showfollowing" id="showfollowing" <?= ($this->profile['showfollowing'] ?? 0) ? 'checked' : '' ?>> Show following
        </label>

        <label for="showfollowers">
            <input type="checkbox" name="showfollowers" id="showfollowers" <?= ($this->profile['showfollowers'] ?? 0) ? 'checked' : '' ?>> Show followers
        </label>

        <label for="showmutuals">
            <input type="checkbox" name="showmutuals" id="showmutuals" <?= ($this->profile['showmutuals'] ?? 0) ? 'checked' : '' ?>> Show mutuals
        </label>

        <label for="country">Country</label>
        <select id="country" name="country" style="margin-top:6px;" autocomplete="country">
            <?php
			foreach ($GLOBALS['countries_list_iso_codes'] as $code => $name) {
				?>
                <option value="<?= $code ?>" <?= (($this->profile['country'] ?? 'NONE') === $code) ? 'selected' : '' ?>><?= $name ?></option>
            <?php } ?>
        </select>
        <input type="number" name="userid" value="<?= $usrid ?>" class="focus hidden">
        <input type="hidden" name="csrftoken" id="csrftoken" value="<?= $_SESSION['csrftoken'] ?>" required>
        <input type="submit" value="Save">
    </form>
	<?php
	$msg = ob_get_clean();
	echo json_encode(['status' => 'success', 'message' => $msg]);
	exit;
}

ob_start();
?>
<div id="manage" class="hidden"></div>
<div class="fc">
<?php if ($this->other_user_info): ?>

	<div class="border" style="flex-direction:row;align-items:normal;justify-content:space-between;height:200%;">
		<?php if ($charisavailable): ?>
			<img class='profileimg' src="/social/avatar?id=<?= $this->other_user_info['id'] ?>">
		<?php endif; ?>

		<div class="fc" style="justify-content: space-between">
			<div class="fc aifs">
            <h1>
				<?=$this->other_user_info['id'] == 2 ? "<span title='This user donated when donations were open.'>🌟</span>" : ""?>
				<?= $this->other_user_info['isoperator'] ? '「' . $this->other_user_info['username'] . '」' : $this->other_user_info['username']; ?>
			</h1>
            
			<?php if ($this->other_user_info['id'] === $this->user_info['id']): ?>
				<a href="#" onclick="showeditpanel()">Edit</a>
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
			<?php 
			if ($this->other_profile["showfollowers"]) {
				$stmt_get_amount_of_followers = $this->db->prepare('SELECT COUNT(*) as c FROM interaction WHERE to_what = ? AND `type` = 1');
				$stmt_get_amount_of_followers->execute([$this->other_user_info['id']]);
				$stmt_followers = $stmt_get_amount_of_followers->fetch()["c"];
			}
			
			if ($this->other_profile["showfollowing"]) {
				$stmt_get_following_amount = $this->db->prepare('SELECT COUNT(*) as c FROM interaction WHERE from_who = ? AND `type` = 1');
				$stmt_get_following_amount->execute([$this->other_user_info['id']]);
				$stmt_following = $stmt_get_following_amount->fetch()["c"];
			}

			if ($this->other_profile["showmutuals"]) {
				$stmt_get_mutuals_amount = $this->db->prepare('
					SELECT COUNT(T1.to_what) as c
					FROM interaction AS T1
					INNER JOIN interaction AS T2
					ON T1.from_who = T2.to_what
					WHERE T1.to_what = ?
					AND T2.from_who = ?
					AND T1.type = 1
					AND T2.type = 1
				');

				$stmt_get_mutuals_amount->execute([$this->other_user_info['id'], $this->other_user_info['id']]);

				$stmt_mutuals = $stmt_get_mutuals_amount->fetch()["c"];
			}
			?>
			<div class="fc">
			<div>
			<?php if ($this->other_profile["showfollowers"]):?>
			<span id="follower_count">Followers: <?=$stmt_followers?></span>
			<?php endif; ?>
			
			<?php if ($this->other_profile["showfollowing"]):?>
			<span id="follower_count">Following: <?=$stmt_following?></span>
			<?php endif; ?>
			
			<?php if ($this->other_profile["showmutuals"]):?>
			<span id="follower_count">Mutuals: <?=$stmt_mutuals?></span>
			<?php endif; ?>
			</div>
			<?php
			if ($this->other_user_info['id'] != $this->user_info['id']): 
				$stmt_check_if_following = $this->db->prepare('SELECT COUNT(*) as c FROM interaction WHERE from_who = ? AND to_what = ? AND type = 1');
				$stmt_check_if_following->execute([$this->user_info['id'], $this->other_user_info['id']]);
				$stmt_cif_fetch = $stmt_check_if_following->fetch()["c"];
				$following = false;
				if ($stmt_cif_fetch >= 1) {
					$following = true;
				} else {
					$following = false;
				}
				?>
				<button onclick="follow(<?= $this->other_user_info['id'] ?>)" id="follow_button"><?=$following ? "Unfollow" : "Follow"?></button>
			<?php endif; ?>
			</div>
		</div>
	</div>

	<?php
	if ($this->other_profile['showinventory'] || $this->other_profile['showposts']):
		?>
	<div class="fr wfa">
		<?php if ($this->other_profile['showinventory']): ?>
			<div class="catalogitemborder" style="width:auto">
				<?php
				$equipped = json_decode($this->other_profile['equipped']);
				if (!empty($equipped)) {
					foreach ($equipped as $itemid) {
						?>
							<a href="/asset/item?id=<?= $itemid ?>">
								<img src="/asset/thumbnail?id=<?= $itemid ?>" height="100" width="100">
							</a>
							<?php
					}
				} else {
					echo 'No items in inventory.';
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ($this->other_profile['showposts']): ?>
			<div class="border fc aifs" style="overflow:scroll; max-height:-webkit-fill-available;">
				<span class="msgdate" style="font-size:0.6em;">Last 6 Posts</span>
				<?php
				$stmtgetposts = $this->db->prepare('
					SELECT id, content, uploadtimestamp
					FROM feed
					WHERE author = ?
					ORDER BY id DESC
					LIMIT 6
				');

				$stmtgetposts->execute([$this->other_user_info['id']]);
				if ($stmtgetposts) {
					while ($row = $stmtgetposts->fetch()) {
						$postid = $row['id'];
						$content = $row['content'];
						$uploadtimestamp = $row['uploadtimestamp'];
						?>
						<div class="fc aifs" style="padding:0;">
							<a href="/social/post?id=<?= $postid; ?>"><?= mb_strimwidth($content, 0, 14, '..') ?></a>
							<span class="msgdate" style="font-size:0.6em;"><?= date('Y-d-m H:i:s', $uploadtimestamp) ?></span>
						</div>
						<?php
					}
				} else {
					echo 'No posts.';
				}
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<script>
		<?php if ($this->other_user_info['id'] === $this->user_info['id']): ?>
		function showeditpanel() {
			const container = document.getElementById('manage');
            const postData = new URLSearchParams();
            postData.append('userid', <?= $this->user_info['id'] ?>);

			fetch('/social/profile', {
				method: 'POST',
                body: postData
			})
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then(data => {
				if (data.status === 'success') {
					container.className = 'focus';
					container.innerHTML = data.message;
				} else {
					container.textContent = data.message || 'Auth failure: Unknown error.';
				}
			})
			.catch(error => {
				console.error('Fetch error:', error);
				container.textContent = 'Connection or Server Failure: ' + error.message;
				container.style.color = 'red';
			});
		}
		<?php endif; ?>
		function follow(id) {
			const follow_button = document.getElementById('follow_button');
			const follower_count = document.getElementById('follower_count');
            const postData = new URLSearchParams();
            postData.append('user', id);
            postData.append('csrftoken', "<?=$_SESSION["csrftoken"]?>");

			fetch('/social/profile/follow', {
				method: 'POST',
                body: postData
			})
			.then(response => {
				if (!response.ok) {
					throw new Error(`HTTP error! status: ${response.status}`);
				}
				return response.json();
			})
			.then(data => {
				if (data.status == true) {
					txt = "Unfollow";
				} else {
					txt = "Follow";
				}
				follower_count.innerHTML = "Followers: " + data.followers
				follow_button.innerHTML = txt;
			})
			.catch(error => {
				console.error('Fetch error:', error);
				follow_button.textContent = 'error' + error.message;
			});
		}
	</script>

<?php else: ?>
	User not found.
<?php endif; ?>

</div>