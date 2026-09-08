<?php

/**
 * Tokens de redefinicao de senha armazenados no banco.
 */
class PasswordResetToken extends BaseModel {
    public function __construct() {
        parent::__construct('password_reset_tokens', 'id');
    }

    public function createToken($email, $tokenHash, $expiresAt) {
        $this->deleteExpired();

        $this->query(
            "UPDATE password_reset_tokens SET used_at = NOW() WHERE email = ? AND used_at IS NULL",
            [$email]
        );

        return $this->create([
            'email' => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
            'used_at' => null
        ]);
    }

    public function findValidByHash($tokenHash) {
        $stmt = $this->query(
            "SELECT * FROM password_reset_tokens WHERE token_hash = ? AND used_at IS NULL AND expires_at >= NOW() LIMIT 1",
            [$tokenHash]
        );

        return $stmt->fetch();
    }

    public function markUsed($tokenHash) {
        $stmt = $this->query(
            "UPDATE password_reset_tokens SET used_at = NOW() WHERE token_hash = ? AND used_at IS NULL",
            [$tokenHash]
        );

        return $stmt->rowCount() > 0;
    }

    public function deleteExpired() {
        return $this->query(
            "DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used_at IS NOT NULL"
        );
    }
}
