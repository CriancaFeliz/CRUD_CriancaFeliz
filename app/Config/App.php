<?php

/**
 * Configurações da Aplicação
 */
class App {
    
    // Modo de armazenamento: apenas mysql é suportado
    const STORAGE_MODE = 'mysql';
    
    // Verificar se banco de dados está disponível
    public static function isDatabaseAvailable() {
        try {
            Database::getConnection();
            return true;
        } catch (Exception $e) {
            reportException($e, 'App::database');
            return false;
        }
    }
}
