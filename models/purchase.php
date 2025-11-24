<?php
if (!$this->user_info) {
    http_response_code(500);
    die("Authentication Error...");
}

if (isset($_POST['itemid'])) {
    $csrftoken = $_POST["csrftoken"];
    if ($_POST["csrftoken"] != $_SESSION["csrftoken"]) {
        die(json_encode(["status" => "error", "message" => "Invalid CSRF token ($csrftoken). Try reloading the page you came from?"]));
    }
    $itemid = (int)$_POST['itemid'];
    $curinv = json_decode($this->economy["inv"], true);
    if (!is_array($curinv)) {
        $curinv = []; 
    }
    $stmtcheckitem = $this->db->prepare("
    SELECT *
    FROM items 
    WHERE id = ?
    ");
    $stmtcheckitem->execute([$itemid]);
    $row = $stmtcheckitem->fetch(PDO::FETCH_ASSOC);

    if (empty($row['asset'])) {
        $msg = 'Item not found';
        $status = 'error';
    } else {
        if ($row['public'] && $row['approved']) {
            if (in_array($itemid,$curinv)) {
                $status = 'error';
                $msg = 'You already own that item!';
            } else {
                try {
                    // lock item row
                    $stmt = $this->db->prepare('SELECT owner, value, public, approved FROM items WHERE id = ? FOR UPDATE');
                    $stmt->execute([$itemid]);
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$row) throw new Exception('Item not found');
                    if (!$row['public'] || !$row['approved']) throw new Exception('Not purchasable');
                    
                    $price = (int)$row['value'];
                    if ($price > 0) {
                        $stmt = $this->db->prepare('UPDATE economy SET money = money - ? WHERE id = ? AND money >= ?');
                        $stmt->execute([$price, $this->user_info["id"], $price]);
                        if ($stmt->rowCount() === 0) throw new Exception('Insufficient funds!');
                    }

                    $owner = (int)$row['owner'];
                    $stmt = $this->db->prepare('UPDATE economy SET money = money + ? WHERE id = ?');
                    $stmt->execute([$price, $owner]);

                    $stmt = $this->db->prepare('SELECT inv FROM economy WHERE id = ? FOR UPDATE');
                    $stmt->execute([$this->user_info["id"]]);

                    $invrow = $stmt->fetch(PDO::FETCH_ASSOC);
                    $curinv = json_decode($invrow['inv'] ?? '[]', true);
                    $curinv[] = $itemid;
                    $newinv = json_encode($curinv);

                    $stmt = $this->db->prepare('UPDATE economy SET inv = ? WHERE id = ?');
                    $stmt->execute([$newinv, $this->user_info["id"]]);

                    $transid = bin2hex(random_bytes(4)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(2)) . '-' . bin2hex(random_bytes(8));

                    $stmt = $this->db->prepare('INSERT INTO transactions (id, issuer, receiver, amount, asset, `time`) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$transid, $this->user_info["id"], $owner, $price, $itemid, time()]);

                    $status = 'success';
                    $msg = 'Purchased!';
                } catch (Exception $e) {
                    $status = 'error';
                    $msg = $e->getMessage();
                }
            }
        } else {
            $status = 'error';
            $msg = 'This item is not available for purchase.';
        }
    }

echo json_encode([
    'status' => $status,
    'message' => $msg,
    'newmoney' => $this->economy["money"] - $price ?? 0
    ]
);
} else { die("Please provide an item ID.");}
?>