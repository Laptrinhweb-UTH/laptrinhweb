<?php
require_once __DIR__ . '/../helpers/Database.php';

class User {
    /** @var Database */
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Kiểm tra email có tồn tại không
    public function findUserByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    // Lưu token vào database
    public function saveResetToken($email, $token, $expire) {
        $this->db->query("UPDATE users SET reset_token = :token, reset_token_expire = :expire WHERE email = :email");
        $this->db->bind(':token', $token);
        $this->db->bind(':expire', $expire);
        $this->db->bind(':email', $email);
        return $this->db->execute();
    }

    // Tìm user bằng token còn hạn
    public function findUserByToken($token) {
        $this->db->query("SELECT * FROM users WHERE reset_token = :token AND reset_token_expire > NOW()");
        $this->db->bind(':token', $token);
        return $this->db->single();
    }

    // Cập nhật mật khẩu mới và xóa token
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query("UPDATE users SET password = :password, reset_token = NULL, reset_token_expire = NULL WHERE id = :id");
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>