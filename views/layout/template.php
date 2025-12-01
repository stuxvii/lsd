<?php
$page_content = $page_content ?? '';
$secure = $_SERVER['HTTPS'] ?? false;

$preferences = $this->preferences;
$econ = $this->economy;
$meta_tags = $meta_tags ?? '';

// https://stackoverflow.com/a/10989524
function isMobile()
{
	return preg_match('/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i', $_SERVER['HTTP_USER_AGENT']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">

	<?= $meta_tags ?>

	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/assets/css/normalize.css">
	<link rel="stylesheet" href="/assets/css/styles.css">
	<title>LSDBlox</title>
	<meta name="robots" content="noindex">
	<style>
		<?php
		$primary_hex = '#ffffff';
		$secondary_hex = '#000000';

		if (isset($preferences['appearance'])) {
			$scheme = $preferences['appearance'];

			if (isset($GLOBALS['color_schemes'][$scheme])) {
				if ($this->preferences['light_mode']) {
					$primary_hex = $GLOBALS['color_schemes'][$scheme]['secondary'];
					$secondary_hex = $GLOBALS['color_schemes'][$scheme]['primary'];
				} else {
					$primary_hex = $GLOBALS['color_schemes'][$scheme]['primary'];
					$secondary_hex = $GLOBALS['color_schemes'][$scheme]['secondary'];
				}
			}
		}

		?>:root {
			--primary-color: <?= $primary_hex ?>;
			--secondary-color: <?= $secondary_hex ?>;
		}

		* {
			accent-color: var(--secondary-color);
		}

		body {
			background-image: linear-gradient(<?= $primary_hex ?>cc,
					<?= $primary_hex ?>ff),
				var(--bgimg);
		}

		<?php if (isset($this->preferences['movingbg']) && !$this->preferences['movingbg']): ?>@keyframes movebg {
			0% {
				background-position: right 0px bottom 0px;
			}

			100% {
				background-position: right -0px bottom -0px;
			}
		}

		<?php
endif;

$font_family = $GLOBALS['fonts_list'][0]['font_family'];
$font_url = $GLOBALS['fonts_list'][0]['url'];

if (isset($preferences['font'])) {
	$scheme = $preferences['font'];

	if (isset($GLOBALS['fonts_list'][$scheme])) {
		$font_family = $GLOBALS['fonts_list'][$scheme]['font_family'];
		$font_url = $GLOBALS['fonts_list'][$scheme]['url'];
	}
}

?>@font-face {
			font-family: <?= $font_family ?>;
			src: url('<?= $font_url ?>');
		}

		body {
			font-family: '<?= $font_family ?>' <?= $this->preferences['emojidex'] ?? false ? ", 'Emojidex'" : '' ?>;
		}

		<?php if (isset($freakmode) && $freakmode): ?>
		html,
		body,
		input,
		select,
		option,
		button {
			cursor: url('/assets/cursors/kangel.cur'), auto;
		}

		<?php endif; ?>
	</style>
	<script>
		const csrftoken = "<?= $_SESSION['csrftoken'] ?>";
		let logged_in = false;
		<?php if ($this->user_info): ?>
		logged_in = true;
		const last_stipend_claim = <?= ($this->economy['lastbuxclaim'] + 43200) - time() ?>;
		<?php endif; ?>
	</script>
</head>

<body>
	<div class="sidebars" <?php
if (isset($this->user_info['id'])) {
	echo !$this->user_info['id'] ? "style='flex-direction: column;'" : '';
}
?>
		>
		<?php
		require ROOT_PATH . '/views/layout/sidebars.php';
		?>
		<div class="main">
			<div class="navbar">
				<div class="v_mid">
					<a href="/" title="LSDBlox"><img height='20' class="lsdblox_logo" alt="LSDBLOX"
							src='/assets/images/anim/logo.gif'></a>
					<?php if (MAINTENANCE_ON == false || isset($this->user_info['isoperator'])): ?>
					<a href="/asset/catalog">Catalog</a>
					<?php if ($this->user_info): ?>
					<a href="/asset/upload">Upload</a>
					<a href="/you/character">Avatar</a>
					<a href="/you/inventory">Inventory</a>
				</div>
				<div class="v_mid">
					<a href="/you/">
						<?= htmlspecialchars($this->user_info['username']) ?>
					</a>
					<a href="/you/log"><span id="amountofmoney">¥<?= htmlspecialchars($this->economy['money']) ?>
						</span></a>
					<div style="position:relative">
						<a href="#" id="menu_button">☰</a>
						<div id="you_menu" class="drop_down hidden">
							<a title="Time left until stipend claim eligibility" id="stipend_countdown">--:--:--</a>
							<?php if ($this->user_info['isoperator']): ?>
							<a href="/admin/">Dashboard</a>
							<a href="/moderation/">Moderate</a>
							<a href="/moderation/reports">Reports</a>
							<?php endif; ?>
							<a href="/account/config">Settings</a>
							<a href="/account/logout">Log out</a>
						</div>
					</div>
					<?php endif; ?>
					<?php endif; ?>
				</div>
				<?php if ($secure && !$this->user_info): ?>
				<div class="v_mid">
					<a href="/account/login">Login</a>
					<a href="/account/register">Register</a>
				</div>
				<?php endif; ?>

			</div>
			<div class='content'>
				<?= $page_content; ?>
			</div>
				<div class="navbar bottomnavbar">
					<div class="v_mid">
						<a href="/info/privacypolicy">Privacy</a>
						<a href="/info/termsofservice">TOS</a>
					</div>
					<?php if ($this->user_info): ?>
					<div class="v_mid music_player">
						<span class="music_btn" id="music_play">►</span>
						<span class="music_btn" id="music_pause">⏸</span>
						<span class="music_btn" id="music_stop">⏹</span>
						<span id="music_song_time">X:XX</span>
						<div class="music_progressbar">
							<span id="music_song_name">No music playing..</span>
							<div  class="music_progressbar_color">
								<span id="music_progressbar" style="width:0%;"></span>
							</div>
						</div>
						<span id="music_song_duration">X:XX</span>
						<span class="music_btn" id="music_playlist_open">☰</span>
						<div  id="music_playlist" class="hidden focus">
							<span class="music_btn" id="music_playlist_close">X</span>
							<div class="border aifs">
								<span>Buy more songs in the <a href="/asset/catalog">Catalog</a>!</span>
								<hr>
							<?php
							$invarray = json_decode($this->economy['inv']);
							if (!empty($invarray)) {
								$inv = array_reverse($invarray);
								$emptyinv = false;
							}
							if (!$emptyinv) {
								$placeholders = implode(',', array_fill(0, count($invarray), '?'));
								$stmtcheckitem = $this->db->prepare("SELECT id, `name` FROM items
								WHERE id IN ($placeholders) AND type = 2 AND approved = 1");
								$params = array_merge($invarray);
								$stmtcheckitem->execute($params);

								if ($stmtcheckitem->rowCount() > 0) {
									$results = $stmtcheckitem->fetchAll();
								} else {
									$results = false;
								}
							}
							foreach ($results as $audio) {
								?>
							<span class="music_playlist_item" song-id="<?= $audio['id'] ?>">▶ <?= htmlspecialchars($audio['name']) ?></span>
							<?php } ?>
							<script>
							let song_names = {<?php
							foreach ($results as $audio) {
								$new_name = htmlspecialchars($audio["name"]);
								echo "\"{$audio["id"]}\": {\"name\":\"{$new_name}\"},"; // yeah i really can't think of a better way to do this sorry using json_encode is bad
							}
							?>};
							
							</script>
							</div>
						</div>
					</div>

					<?php endif; ?>
					<div class="v_mid">
						<a href="/info/status">Status</a>
						<a href="/info/attribution">Thanks</a>
					</div>
				</div>
			</div>
					<?php
					if (isset($this->preferences['mirrorsidebars']) && $this->preferences['mirrorsidebars']) {
						$rightside = true;
					}
					require ROOT_PATH . '/views/layout/sidebars.php';
					?>
					<script src="/assets/js/main.js"></script>
</body>

</html>