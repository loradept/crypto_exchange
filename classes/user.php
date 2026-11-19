<?php
class User {
    public $id;
    public $balance = 0;
    private $db;

    public function __construct($db, $user_id = null) {
        $this->db = $db;
        if ($user_id) {
            $this->id = $user_id;
            $this->loadData(); // Загружаем данные из БД сразу при создании объекта
        }
    }

    private function loadData() {
        // Делаем запрос к БД, чтобы получить баланс конкретного юзера
        $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = ?");
        $stmt->bind_param("i", $this->id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        if ($res) {
            $this->balance = $res['balance'];
        }
    }
}