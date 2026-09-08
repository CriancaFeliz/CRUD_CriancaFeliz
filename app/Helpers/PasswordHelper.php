<?php

/**
 * Centraliza a política e o algoritmo de armazenamento de senhas.
 */
class PasswordHelper {
    private const MIN_LENGTH = 12;
    private const ARGON2_MAX_LENGTH = 256;
    private const BCRYPT_MAX_LENGTH = 72;
    private const ARGON2_OPTIONS = [
        'memory_cost' => 19456,
        'time_cost' => 2,
        'threads' => 1,
    ];
    private const BCRYPT_OPTIONS = [
        'cost' => 12,
    ];
    private const COMMON_PASSWORDS = [
        'admin123',
        '123456',
        '12345678',
        '123456789',
        '1234567890',
        'password',
        'senha123',
        'criancafeliz',
    ];

    public static function hash($password) {
        $error = self::validationError($password);
        if ($error !== null) {
            throw new InvalidArgumentException($error);
        }

        return password_hash($password, self::algorithm(), self::options());
    }

    public static function verify($password, $hash) {
        return is_string($hash) && $hash !== '' && password_verify($password, $hash);
    }

    public static function needsRehash($hash) {
        return is_string($hash)
            && $hash !== ''
            && password_needs_rehash($hash, self::algorithm(), self::options());
    }

    public static function isValid($password) {
        return self::validationError($password) === null;
    }

    public static function validationError($password) {
        if (!is_string($password) || $password === '') {
            return 'Senha é obrigatória';
        }

        $length = strlen($password);
        if ($length < self::MIN_LENGTH) {
            return 'Senha deve ter pelo menos ' . self::MIN_LENGTH . ' caracteres';
        }

        $maxLength = self::maxLength();
        if ($length > $maxLength) {
            return 'Senha deve ter no máximo ' . $maxLength . ' caracteres';
        }

        if (trim($password) === '') {
            return 'Senha não pode conter apenas espaços';
        }

        if (in_array(strtolower($password), self::COMMON_PASSWORDS, true)) {
            return 'Senha não pode ser uma senha padrão ou comum';
        }

        return null;
    }

    public static function policyDescription() {
        return 'A senha deve ter entre ' . self::MIN_LENGTH . ' e ' . self::maxLength() . ' caracteres e não pode ser uma senha padrão ou comum.';
    }

    private static function algorithm() {
        if (self::supportsArgon2id()) {
            return PASSWORD_ARGON2ID;
        }

        return PASSWORD_BCRYPT;
    }

    private static function options() {
        return self::supportsArgon2id()
            ? self::ARGON2_OPTIONS
            : self::BCRYPT_OPTIONS;
    }

    private static function maxLength() {
        return self::supportsArgon2id()
            ? self::ARGON2_MAX_LENGTH
            : self::BCRYPT_MAX_LENGTH;
    }

    private static function supportsArgon2id() {
        return defined('PASSWORD_ARGON2ID')
            && function_exists('password_algos')
            && in_array(PASSWORD_ARGON2ID, password_algos(), true);
    }
}
