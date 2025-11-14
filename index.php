<?php

header('Content-Type: application/json');

require_once 'config/Auth.php';
require_once 'models/UserModel.php';

$auth = new Auth();
if (!$auth->checkApiKey()) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid or inactive API key']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

$userModel = new UserModel();
$id = null;

if (isset($pathParts[2]) && is_numeric($pathParts[2])) {
    $id = intval($pathParts[2]);
}

if ($method == 'GET') {
    if ($id) {
        $user = $userModel->getById($id);
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'User not found']);
        }
    } else {
        $users = $userModel->getAll();
        echo json_encode($users);
    }
}

if ($method == 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON', 'json_error' => json_last_error_msg()]);
        exit;
    }
    
    $missingFields = [];
    if (!isset($data['username']) || empty(trim($data['username']))) {
        $missingFields[] = 'username';
    }
    if (!isset($data['email']) || empty(trim($data['email']))) {
        $missingFields[] = 'email';
    }
    if (!isset($data['password']) || empty(trim($data['password']))) {
        $missingFields[] = 'password';
    }
    
    if (!empty($missingFields)) {
        http_response_code(400);
        $fieldName = $missingFields[0];
        echo json_encode(['error' => "Поле {$fieldName} обязательно"]);
        exit;
    }
    
    $userId = $userModel->create($data['username'], $data['email'], $data['password']);
    if ($userId) {
        $user = $userModel->getById($userId);
        http_response_code(201);
        echo json_encode($user);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Failed to create']);
    }
}

if ($method == 'PUT') {
    if ($id) {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON', 'json_error' => json_last_error_msg()]);
            exit;
        }
        
        $missingFields = [];
        if (!isset($data['username']) || empty(trim($data['username']))) {
            $missingFields[] = 'username';
        }
        if (!isset($data['email']) || empty(trim($data['email']))) {
            $missingFields[] = 'email';
        }
        
        if (!empty($missingFields)) {
            http_response_code(400);
            $fieldName = $missingFields[0];
            echo json_encode(['error' => "Поле {$fieldName} обязательно"]);
            exit;
        }
        
        if ($userModel->update($id, $data['username'], $data['email'])) {
            $user = $userModel->getById($id);
            echo json_encode($user);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to update']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
    }
}

if ($method == 'DELETE') {
    if ($id) {
        if ($userModel->delete($id)) {
            echo json_encode(['message' => 'User deleted']);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Failed to delete']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
    }
}

