<?php

require_once(__DIR__ . '/../config/Database.php');

class UserModel {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }
    
    public function getAll() {
        $stmt = $this->conn->prepare("SELECT id, username, email FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($username, $email, $password) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $passwordHash])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }
    
    public function update($id, $username, $email) {
        $stmt = $this->conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        return $stmt->execute([$username, $email, $id]);
    }
    
    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

