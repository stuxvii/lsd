<?php

class AssetModel extends BaseController {
    public function getItemData(int $id): ?array {
        $sql = "SELECT * FROM items WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]); 
        
        $item = $stmt->fetch();
        return $item ?: null;
    }
}