<?php
class SmfController
{
    public static function createTransaction(PDO $pdo, int $userId, string $studentName, string $studentId, string $program, string $amountStr, ?array $photo): array
    {
        $studentName = trim($studentName);
        $studentId = trim($studentId);
        $program = strtoupper(trim($program));
        $amountStr = trim($amountStr);

        if ($studentName === '' || $studentId === '' || $amountStr === '') {
            throw new InvalidArgumentException('Full Name, Student ID, and Amount are required.');
        }
        $allowedPrograms = ['BSBA','BSIS','BMMA','BSA','BSTM','BSED','BEED','BCAED'];
        if (!in_array($program, $allowedPrograms, true)) {
            throw new InvalidArgumentException('Please select a valid program.');
        }
        if (!preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $amountStr)) {
            throw new InvalidArgumentException('Amount must be a valid number with up to 2 decimals.');
        }
        $amount = (float)$amountStr;
        if ($amount <= 0) {
            throw new InvalidArgumentException('Amount must be greater than 0.');
        }

        $photoPath = null;
        if ($photo && isset($photo['tmp_name']) && $photo['error'] === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($photo['tmp_name']);
            if (!isset($allowed[$mime])) {
                throw new InvalidArgumentException('Only JPG and PNG files are allowed.');
            }
            $ext = $allowed[$mime];
            $uploadDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $basename = 'smf_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $target = $uploadDir . '/' . $basename;
            if (!move_uploaded_file($photo['tmp_name'], $target)) {
                throw new RuntimeException('Failed to upload photo.');
            }
            // Store relative path for serving
            $photoPath = 'uploads/' . $basename;
        }

        $stmt = $pdo->prepare('INSERT INTO smf_transactions (user_id, student_name, student_identifier, program, amount, photo_path) VALUES (:user_id, :student_name, :student_identifier, :program, :amount, :photo_path)');
        $stmt->execute([
            ':user_id' => $userId,
            ':student_name' => $studentName,
            ':student_identifier' => $studentId,
            ':program' => $program,
            ':amount' => $amount,
            ':photo_path' => $photoPath,
        ]);

        return [
            'id' => (int)$pdo->lastInsertId(),
            'user_id' => $userId,
            'student_name' => $studentName,
            'student_identifier' => $studentId,
            'program' => $program,
            'amount' => $amount,
            'photo_path' => $photoPath,
        ];
    }
}