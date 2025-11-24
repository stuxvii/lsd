<?php
header('Content-Type: application/json');
if (MAINTENANCE_ON && !$this->user_info["isoperator"]) {
    die("Service not available (Maintenance).");
}


$target_dir = 'uploads/';

$max_file_size = 50000000;
$audio_target_size_bits = 2 * 1024 * 1024 * 8 * 2;

function sendjsonback(string $status, string $message, int $http_code = 200, int $asset_id = 0): void
{
    http_response_code($http_code);
    echo json_encode(['status' => $status, 'message' => $message, "assetid" => $asset_id]);
    exit;
}

function handle_db_operations(
    PDO $db, 
    string $assetname, 
    string $target_file, 
    int $uid, 
    int $assetvalue, 
    string $assettype, 
    string $inv,
    string $assetdesc,
    bool $assetvisibility,
    int $fee, 
    ): void {
        if ($assetvalue > 2147483647) { // failsafe for if the user feels silly enough to put a quadvigintillion dollars for their price
            $assetvalue = 2147483647;
        }
        $assetvisibility = (int)$assetvisibility ?? 0;
        $false = 0;
        $true = 1;
        
        try {
            $db->beginTransaction();
            $uploadts = time();
            $stmt = $db->prepare('INSERT INTO `items` 
            (`name`,`asset`,`owner`,`value`,`public`,`uploadts`,`type`,`desc`) 
            VALUES (?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?)
            ');
            $stmt->execute([$assetname, $target_file, $uid, $assetvalue, $assetvisibility, $uploadts, $assettype, $assetdesc]);
            $itemid = $db->lastInsertId();
            
            $curinv = json_decode($inv, true) ?? [];
            $curinv[] = (int)$itemid;
            $newinv = json_encode($curinv);
            
            $stmtupdinv = $db->prepare('UPDATE economy SET inv = ? WHERE id = ?');
            $stmtupdinv->execute([$newinv, $uid]);

            if ($fee > 0) {
                $stmt = $db->prepare('UPDATE economy SET money = money - ? WHERE id = ? AND money >= ?');
                $stmt->execute([$fee, $uid, $fee]);
                if ($stmt->rowCount() === 0) {
                    sendjsonback('error', "Insufficient funds! You need at least ¥$fee to upload that.", 403);
                }
            }
            
            $db->commit();
            sendjsonback('success', "Your asset has been uploaded! Check its status at https://lsdblox.cc/asset/item?id=$itemid", 201, $itemid);
            
        } catch (\Exception $e) {
            $db->rollback();
            error_log("DB Error: " . $e->getMessage());
            sendjsonback('error', 'Database operation failed.', 500);
        }
    }
    
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['filetoupload'], $_POST['type'], $_POST['itemname'], $_POST['itemprice'])) {
    sendjsonback('error', 'Invalid or incomplete request.', 400);
}

if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
    sendjsonback('error', "Invalid CSRF token. Try reloading the page you came from?", 400);
}

$file = $_FILES['filetoupload'];
$assettype = (int)$_POST['type'];
$assetname = trim($_POST['itemname']);
$assetdesc = trim($_POST['itemdesc']);
$assetvalue = (int)$_POST['itemprice'];
$assetvisibility = false;
$tmp_name = $file['tmp_name'];
$fee = 0;
switch ($assettype) {
    case 2:
        $fee = 100;
        break;

    case 5:
        $fee = 10;
        break;

    case 6:
        $fee = 10;
        break;

    case 10:
        $fee = 5;
        break;
    
    default:
        $fee = 0;
        break;
}

if (isset($_POST['public']) && $_POST['public']) {
    $assetvisibility = true;
}
if ($file['error'] !== UPLOAD_ERR_OK) {
    sendjsonback('error', 'Upload error code: ' . $file['error'], 500);
}

if ($assetvalue < 0) {
    sendjsonback('error', 'Sorry, but you may not upload items with negative prices.', 400);
}

if ($file['size'] > $max_file_size) {
    $max_size_mb = round($max_file_size / 1024 / 1024, 2); 
    sendjsonback('error', "Sorry, your file is too large (max: $max_size_mb MB).", 400);
}

if (empty($tmp_name) || !is_uploaded_file($tmp_name)) {
    sendjsonback('error', 'File upload failed or no file was selected.', 400);
}

if (mb_strlen($assetname) > 64) {
    sendjsonback('error', 'Your item name is too long! If you need more detail, write it in your item\'s description.', 400);
}

if (mb_strlen($assetdesc) > 512) {
    sendjsonback('error', 'Your item description is too long!', 400);
}

foreach ($GLOBALS['asset_types'] as $group => $types) {
    if (in_array($assettype, $types)) {
        $assettypegroup = $group;
        break;
    }
}

if (!isset($GLOBALS['mime_types'][$assettypegroup])) {
    sendjsonback('error', 'Unsupported asset type provided.', 400);
}

$allowed_mimes = $GLOBALS['mime_types'][$assettypegroup];

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $tmp_name);
finfo_close($finfo);

if (!in_array($mime_type, $GLOBALS['mime_types'][$assettypegroup], true)) {
    sendjsonback('error', "File type '{$mime_type}' is not allowed for asset type '{$assettype}'.", 400);
}

$new_file_name = uniqid();
if ($assettypegroup == 'image') {
    try {
        $target_file = $target_dir . $new_file_name . '.png';

        $imagick = new Imagick();
        $imagick->readImage($tmp_name);
        $imagick->stripImage();
        $imagick->setImageFormat('png');

        if (!$imagick->writeImage(ROOT_PATH . '/' . $target_file)) {
            sendjsonback('error', 'Asset upload failed during image save.', 500);
        }
        $imagick->clear();
        $imagick->destroy();
        handle_db_operations($this->db, $assetname, $target_file, $this->user_info["id"], $assetvalue, $assettype, $this->economy["inv"], $assetdesc, $assetvisibility, $fee);
        exit;

    } catch (ImagickException $e) {
        sendjsonback('error', 'Asset processing failed (Imagick): ' . $e->getMessage(), 500);
    }
}
if ($assettypegroup == 'mesh') {
    try {
        $file_content = @file_get_contents($tmp_name);
        if ($file_content !== false && verify_mesh($file_content)) {
            $target_file = $target_dir . $new_file_name . '.mesh';
            
            if (!move_uploaded_file($tmp_name, ROOT_PATH . '/' . $target_file)) {
                sendjsonback('error', 'Asset upload failed during mesh save.', 500);
            }
            
            handle_db_operations($this->db, $assetname, $target_file, $this->user_info["id"], $assetvalue, $assettype, $this->economy["inv"], $assetdesc, $assetvisibility, $fee);
            exit;
        } else {
            sendjsonback('error', 'Unsupported mesh! Remember that it MUST be version 1.00 and be in plain text.');
        }
    } catch (ImagickException $e) {
        sendjsonback('error', 'Asset processing failed (Imagick): ' . $e->getMessage(), 500);
    }
}
if ($assettypegroup == 'audio') {
    try {
        $target_file = $target_dir . $new_file_name . '.mp3';

        $ffmpeg = FFMpeg\FFMpeg::create();
        $ffprobe = FFMpeg\FFProbe::create();

        $audio = $ffmpeg->openAdvanced(array($tmp_name));
        $duration_seconds = $ffprobe->format($tmp_name)->get('duration');

        if (!$duration_seconds || $duration_seconds <= 0) {
            sendjsonback('error', 'Could not determine audio duration or duration is zero.', 400);
        }

        $target_abr = floor($audio_target_size_bits / $duration_seconds);
        $target_abr_kbps = round($target_abr / 1000);
        
        $min_abr_kbps = 64;
        $max_abr_kbps = 320;
        
        $final_abr_kbps = max($min_abr_kbps, min($max_abr_kbps, $target_abr_kbps));

        $format = new FFMpeg\Format\Audio\Mp3();
        $format->setAudioKiloBitrate($final_abr_kbps);
        $audio->setAdditionalParameters([
            '-af', 'loudnorm=i=-16:lra=11:tp=-1.5',
            '-map_metadata', '-1',
            '-fflags', '+bitexact',
            '-flags:v', '+bitexact',
            '-flags:a', '+bitexact'
        ]);

        $audio
            ->map(array('0:a'), $format, ROOT_PATH . '/' . $target_file)
            ->save();
        handle_db_operations($this->db, $assetname, $target_file, $this->user_info["id"], $assetvalue, $assettype, $this->economy['inv'], $assetdesc, $assetvisibility, $fee);
        exit;

    } catch (\FFMpeg\Exception\ExceptionInterface $e) {
        sendjsonback('error', 'Audio processing failed (FFMpeg): ' . var_dump($e), 500);
    } catch (\Exception $e) {
        sendjsonback('error', 'Asset upload failed: ' . $e->getMessage(), 500);
    }
}
if ($this->user_info['isoperator']) {
    try {
        $target_file = $target_dir . $new_file_name . '.obj';

        if (!move_uploaded_file($tmp_name, ROOT_PATH . '/' . $target_file)) {
            sendjsonback('error', 'Asset upload failed during hat save.', 500);
        }
        
        handle_db_operations($this->db, $assetname, $target_file, $this->user_info["id"], $assetvalue, $assettype, $this->economy["inv"], $assetdesc, $assetvisibility, $fee);
        exit;

    } catch (ImagickException $e) {
        sendjsonback('error', 'Asset processing failed (Imagick): ' . $e->getMessage(), 500);
    }
}

sendjsonback('error', 'Unknown server issue.', 500);

?>