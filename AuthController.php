<?php
namespace App\Controllers;

use App\Services\Database;
use App\Services\UserService;

class AuthController
{
    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email AND status = 'ACTIVE'");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        // Ověření hesla (bcrypt):
        if (!password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        // Vygenerování "fake" tokenu (pro ukázku):
        $token = base64_encode($user['id'] . '|' . $user['role'] . '|' . time());

        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'role' => $user['role'],
                'email' => $user['email']
            ]
        ]);
    }
}
