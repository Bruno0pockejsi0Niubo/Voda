<?php
namespace App\Controllers;

use App\Services\Database;

class UserController
{
    public function createUser()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        // Jednoduchá validace
        $email = $input['email'] ?? '';
        $firstName = $input['first_name'] ?? '';
        $lastName = $input['last_name'] ?? '';
        $role = $input['role'] ?? 'CUSTOMER'; 
        $companyId = $input['company_id'] ?? null;

        // Vygenerujeme dočasné heslo
        $tempPassword = substr(md5(uniqid()), 0, 8);
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            INSERT INTO users (company_id, email, password_hash, role, status, first_name, last_name)
            VALUES (:company_id, :email, :password_hash, :role, 'ACTIVE', :first_name, :last_name)
        ");

        $stmt->execute([
            'company_id' => $companyId,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'first_name' => $firstName,
            'last_name' => $lastName
        ]);

        $userId = $db->lastInsertId();

        // Tady můžete odeslat e-mail s dočasným heslem atd.

        echo json_encode([
            'message' => 'User created',
            'user_id' => $userId,
            'temp_password' => $tempPassword
        ]);
    }
}
