<?php
class UserModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }
    
    public function findUserByAuthToken(string $token): ?array {
        if (!preg_match('/^[0-9a-f]{128}$/', $token)) {
            return $user ?: null;
        }
        $sql = "SELECT * FROM users WHERE authuuid = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$token]); 
        
        $user = $stmt->fetch();
        return $user ?: null;
    }
    
    public function getUserInfo(int $userId): ?array {
        $sql = "SELECT username, discordid, isoperator, registerts, id FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]); 
        
        $info = $stmt->fetch();
        return $info ?: null;
    }
    
    public function getUserEconomy(int $userId): ?array {
        $sql = "SELECT * FROM economy WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        $econ = $stmt->fetch();
        return $econ ?: null;
    }
    
    public function initUserEconomy(int $userId, int $defaultMoney = 100, string $defaultInv = ''): bool {
        $sql = "INSERT INTO economy (id, money, inv) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $defaultMoney, $defaultInv]);
    }
    
    public function getUserSettings(int $userId): ?array {
        $sql = "SELECT * FROM config WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public function getUserProfile(int $userId): ?array {
        $sql = "SELECT * FROM profiles WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}