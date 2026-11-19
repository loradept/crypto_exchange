<?php

class TradeManager {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function executeOrderSimple($userId, $type, $coin, $amount, $price) {

        $amount = (float)$amount;
        $price  = (float)$price;

        
        if ($amount <= 0 || $price <= 0) {
            return [
                'success' => false,
                'message' => 'Invalid amount or price'
            ];
        }

        if (!in_array($type, ['buy', 'sell'])) {
            return [
                'success' => false,
                'message' => 'Invalid type'
            ];
        }

        try {

            $this->db->begin_transaction();

           
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id=? FOR UPDATE");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();

            if (!$user) {
                throw new Exception("User not found");
            }

            $balance = (float)$user['balance'];

            
            $total = $amount * $price;

            
            if ($type === "buy") {

                if ($balance < $total) {
                    throw new Exception("Not enough USDT balance");
                }

                $balance -= $total;
            }

            
            else {

                $balance += $total;
            }

            
            $upd = $this->db->prepare("UPDATE users SET balance=? WHERE id=?");
            $upd->bind_param("di", $balance, $userId);
            $upd->execute();

            // 📊 SAVE TRANSACTION
            $log = $this->db->prepare("
                INSERT INTO transactions (user_id, type, coin, amount, price)
                VALUES (?, ?, ?, ?, ?)
            ");

            $log->bind_param(
                "issdd",
                $userId,
                $type,
                $coin,
                $amount,
                $price
            );

            $log->execute();

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Transaction completed successfully'
            ];

        } catch (Exception $e) {

            $this->db->rollback();

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}