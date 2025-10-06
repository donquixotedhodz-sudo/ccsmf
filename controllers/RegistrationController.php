<?php
class RegistrationController
{
    public static function createStudent(PDO $pdo, string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = trim(strtolower($email));
        if ($name === '' || $email === '' || $password === '') {
            throw new InvalidArgumentException('All fields are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        // Check duplicate email
        $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $check->execute([':email' => $email]);
        if ($check->fetch()) {
            throw new RuntimeException('An account with this email already exists.');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (name, email, role, password_hash) VALUES (:name, :email, :role, :hash)');
        $insert->execute([
            ':name' => $name,
            ':email' => $email,
            ':role' => 'student',
            ':hash' => $hash,
        ]);

        $id = (int)$pdo->lastInsertId();
        return [
            'id' => $id,
            'name' => $name,
            'email' => $email,
            'role' => 'student',
        ];
    }
}