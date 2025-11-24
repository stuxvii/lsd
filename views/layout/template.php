<?php
$page_content = $page_content ?? '';
$secure = $_SERVER['HTTPS'] ?? false;

$preferences = $this->preferences;
$econ = $this->economy;
$meta_tags = $meta_tags ?? '';

//https://stackoverflow.com/a/10989524
function isMobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
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

		<?php if (isset($freakmode) && $freakmode): ?>html,
		body,
		input,
		select,
		option,
		button {
			cursor: url('/assets/cursors/kangel.cur'), auto;
		}

		<?php
endif;
?>
	</style>
	<script>
		const csrftoken = "<?= $_SESSION['csrftoken'] ?>";
		let logged_in = false;
		<?php if ($this->user_info):?>
		logged_in = true;
		const last_stipend_claim = <?= ($this->economy['lastbuxclaim'] + 43200) - time() ?>;
		<?php endif;?>
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
						<?= htmlspecialchars($this->user_info['username']) ?></a>
					<a href="/you/log"><span id="amountofmoney">¥<?= htmlspecialchars($this->economy['money']) ?></span></a>
					<div style="position:relative">
						<a href="#" id="menu_button">☰</a>
						<div id="you_menu" class="drop_down hidden">
							<a title="Time left until stipend claim eligibility" id="stipend_countdown">--:--:--</a>
							<?php if ($this->user_info['isoperator']): ?>
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
			<?php if (isMobile()): ?>
			<div class="navbar warn_navbar">
				<div>Website is not optimized for mobile. Pages may not fit.</div>
			</div>
			<?php endif;
			 if (!$secure): ?>
			<div class="navbar bad_navbar">
				<div>You're connecting via HTTP. You will be forbidden from logging in to eliminate the risk of your
					information being intercepted. <a href="https://lsdblox.cc">Press here to go to the secure version
						of the site.</a></div>
				<div>The reason we keep this site available through HTTP is to still allow Roblox to fetch assets.
					Nothing more.</div>
			</div>
			<?php endif;
if (isset($this->preferences['freakmode']) && $this->preferences['freakmode']): ?>
			<marquee scrollamount="44"> pusi destroyer - NOW AVAILABLE IN ARGENTINA - とどけて せつなさにはなまえをつけようか snow halation
				- goodbye~ OH NOOO - NOW'S YOUR CHANCE TO BE A [[[BIG SHOT]]] - BORN TO DIE - WORLD IS A FUCK - 鬼神 Kill
				Em All 1989 - I am trash man - 410,757,864,530 DEAD COPS
			</marquee>
			<?php
endif;
echo "<div class='content'>";
if (MAINTENANCE_ON == true && !$this->user_info['isoperator']):
	?>
			<div class="maintenance_image_div focus">
				<img src="/assets/images/maintenanceowo.png">
			</div>
			<div class='border fc aifs'>
				<span>lsdblox is currently</span>
				<span>under maintenance.</span>
				<span>check #announcements,</span>
				<span>#acids-yapzone and</span>
				<span>#lsdblox for information.</span>
			</div>
			<?php
else:
	echo $page_content;
endif;
echo '</div>';
?>
			<div class="navbar bottomnavbar">
				<div class="v_mid">
					<a href="/info/privacypolicy">Privacy</a>
					<a href="/info/termsofservice">TOS</a>
				</div>
<div class="v_mid">
	Page served in <?php
	$num = microtime() - REQUEST_TIME;
	$num = intval($num * ($p = pow(10, 4))) / $p;
	echo $num?>s
</div>
				<div class="v_mid">
					<a href="/info/status">Status</a>
					<a href="/info/attribution">Thanks</a>
				</div>
			</div>
			<?php if ($this->user_info): ?>
			<?php
endif;
if (isset($this->preferences['mirrorsidebars']) && $this->preferences['mirrorsidebars']) {
	$rightside = true;
}
echo '</div>';
require ROOT_PATH . '/views/layout/sidebars.php';
if (isset($this->preferences['freakmode']) && $this->preferences['freakmode']):
	?>
			<img class="ravelogo bounce" src="/assets/images/rave.png">
			<img class="lsdleaklogo bounce" src="/assets/images/liveleak.png">
			<img class="dancingbaby bounce" src="/assets/images/baby.gif">
			<img class="illuminati bounce" src="/assets/images/illuminati.gif">
			<div class="omgkawaiiangel">
				<img src="/assets/images/kangeldrug.png" class="bounce spinning" height>
			</div>
			<script>
				let response;
				let fishContext;
				let fishBuffer;
				let arrayBuffer;
				async function getfish() {
					fishContext = new (window.AudioContext || window.webkitAudioContext)();
					response = await fetch("/assets/audio/fish.opus");
					arrayBuffer = await response.arrayBuffer();
					fishBuffer = await fishContext.decodeAudioData(arrayBuffer);
				}
				getfish();
				async function fish() {
					if (fishContext) {
						fishSource = new AudioBufferSourceNode(fishContext);
						fishSource.addEventListener('ended', function () {
							fishSource = null;
						});
						fishSource.start(0);
						const gainnode = fishContext.createGain();
						gainnode.gain.value = 0.3;
						gainnode.connect(fishContext.destination);
						fishSource.connect(gainnode);
						fishSource.buffer = fishBuffer;
					}
				}
				document.addEventListener('click', function (event) {
					fish();
				})
			</script>
			<?php endif; ?>
		</div>
		<script src="/assets/js/main.js"></script>
</body>
</html>