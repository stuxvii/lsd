<?php
$file_id = (int) $_GET['id'] ?? 2;
$cache_dir = ROOT_PATH . '/cache/';

$file_path = 'assets/images/modpending.png';

if (!$file_id || !is_numeric($file_id)) {
    $file_id = $_GET['assetversionid'];
    if (!$file_id || !is_numeric($file_id)) {
        http_response_code(400);
        exit('Invalid file request.');
    }
}

$stmtcheckitem = $this->db->prepare('
SELECT *
FROM items
WHERE id = ?
');
$stmtcheckitem->execute([$file_id]);
$row = $stmtcheckitem->fetch();

if ($row['approved'] != 1) {
    if (!$this->user_info['isoperator']) {
        header('Content-Length: ' . filesize($file_path));
        header('Content-Type: image/png');
        readfile($file_path);
    }
}

$file_path = ROOT_PATH . '/' . $row['asset'];
$filename = basename($file_path);

if (!file_exists($file_path) || !is_readable($file_path)) {
    $file_path = 'assets/images/404.png';
    $filename = basename($file_path);
    header('Content-Length: ' . filesize($file_path));
    header('Content-Type: image/png');
    http_response_code(404);
    readfile($file_path);
    exit;
}

switch ($row['type']) {
    case 1:
        $type = 'Decals';
        break;
    case 2:
        $type = 'Audio';
        break;
    case 4:
        $type = 'T-Shirt';
        break;
    case 5:
        $type = 'Shirt';
        break;
    case 6:
        $type = 'Pant';
        break;
    case 7:
        $type = 'Face';
        break;
    case 8:
        $type = 'Head';
        break;
    case 9:
        $type = 'Hat';
        break;
    case 10:
        $type = 'Mesh';
        break;
}

if ($type == 'Audio') {
    if (file_exists(ROOT_PATH . "/cache/$file_id.png")) {
        readfile(ROOT_PATH . "/cache/$file_id.png");
        exit;
    }
    try {
        $ffmpeg = FFMpeg\FFMpeg::create();

        $audio = $ffmpeg->open($file_path);
        $waveform = $audio->waveform(128, 128, ['#00ff00']);
        $waveform->save(ROOT_PATH . "/cache/$file_id.png");
        readfile(ROOT_PATH . "/cache/$file_id.png");
    } catch (e) {
        readfile('assets/images/semiquaver.png');
        error_log(e);
    }
    exit;
}

if ($type == 'T-Shirt' || $type == 'Decals' || $type == 'Face') {
    header('Content-type: image/png');
    readfile($file_path);
    exit;
}

if (!file_exists(ROOT_PATH . "/cache/$file_id.png")) {
    $ch = curl_init("http://localhost:8000");

    $params = ['id' => $file_id];
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        echo 'curl error: ' . $error . "\n";
    } else {
        $httprespcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httprespcode != 200) {
            echo "error encountered!!\n";
            echo 'HTTP err: ' . $httprespcode . "\n";
            echo "response: \n" . $response . "\n";
        } else {
            if (!empty($response)) {
                $base64String = (string) $response;
                $rendercontent = base64_decode($base64String);
                if (!empty($rendercontent)) {
                    file_put_contents($cache_dir . $file_id . '.png', $rendercontent);
                }
            }
        }
    }
} else {
    $rendercontent = file_get_contents($cache_dir . "$file_id.png");
}

if (empty($rendercontent)) {
    die('Not found');
}

header('Content-type: image/png');
echo $rendercontent;

exit;
