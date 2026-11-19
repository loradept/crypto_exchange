<?php

class AuthService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($username, $password) {

        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            return ['success' => false, 'message' => 'Empty fields'];
        }

        $stmt = $this->db->prepare("SELECT id, password FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        if ($password !== $user['password']) {
            return ['success' => false, 'message' => 'Wrong password'];
        }

        return ['success' => true, 'user_id' => $user['id']];
    }

    public function register($username, $password) {

        $username = trim($username);
        $password = trim($password);

        if ($username === '' || $password === '') {
            return ['success' => false, 'message' => 'Fill all fields'];
        }

        if (strlen($username) < 3 || strlen($password) < 4) {
            return ['success' => false, 'message' => 'Too short'];
        }

        // check exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();

        if ($exists) {
            return ['success' => false, 'message' => 'User exists'];
        }

        $stmt = $this->db->prepare("INSERT INTO users (username, password, balance) VALUES (?, ?, 10000)");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();

        return ['success' => true];
    }
}