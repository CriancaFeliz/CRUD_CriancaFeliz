<?php

/**
 * Configurações da Aplicação
 */
class App {
    
    // Modo de armazenamento: 'json' ou 'mysql'
    const STORAGE_MODE = 'mysql'; // ALTERE PARA 'json' SE QUISER VOLTAR AO SISTEMA ANTIGO
    
    // Verificar se banco de dados está disponível
    public static function isDatabaseAvailable() {
        if (self::STORAGE_MODE !== 'mysql') {
            return false;
        }
        
        try {
            Database::getConnection();
            return true;
        } catch (Exception $e) {
            error_log('⚠️ Banco de dados não disponível, usando JSON: ' . $e->getMessage());
            return false;
        }
    }
    
    // Obter Model de Acolhimento (MySQL ou JSON)
    public static function getAcolhimentoModel() {
        if (self::isDatabaseAvailable()) {
            return new AcolhimentoDB();
        } else {
            return new Acolhimento();
        }
    }
    
    // Obter Model de Socioeconômico (MySQL ou JSON)
    public static function getSocioeconomicoModel() {
        if (self::isDatabaseAvailable()) {
            return new SocioeconomicoDB();
        } else {
            return new Socioeconomico();
        }
    }
    
    // Obter Model de Usuário (MySQL ou JSON)
    public static function getUserModel() {
        if (self::isDatabaseAvailable()) {
            return new User(); // Já atualizado para MySQL
        } else {
            // Fallback para JSON se necessário
            return new User();
        }
    }
}
