-- =====================================================
-- MIGRAÇÃO: SISTEMA DE LOGS PARA MYSQL
-- =====================================================

USE criancafeliz;

-- Tabela de Logs do Sistema
CREATE TABLE IF NOT EXISTS Log_Sistema (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    nivel ENUM('INFO', 'WARNING', 'ERROR', 'DEBUG') DEFAULT 'INFO',
    acao VARCHAR(100),
    descricao TEXT,
    tabela_afetada VARCHAR(50),
    registro_id INT,
    usuario_id INT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    dados_anteriores JSON,
    dados_novos JSON,
    INDEX idx_data (data_hora),
    INDEX idx_usuario (usuario_id),
    INDEX idx_nivel (nivel),
    INDEX idx_acao (acao),
    FOREIGN KEY (usuario_id) REFERENCES Usuario(idusuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Anotações Psicológicas
CREATE TABLE IF NOT EXISTS Anotacao_Psicologica (
    id_anotacao INT AUTO_INCREMENT PRIMARY KEY,
    id_atendido INT NOT NULL,
    id_psicologo INT NOT NULL,
    data_anotacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipo ENUM('Consulta', 'Avaliação', 'Evolução', 'Observação') DEFAULT 'Consulta',
    titulo VARCHAR(200),
    conteudo TEXT,
    humor INT CHECK (humor BETWEEN 1 AND 5),
    observacoes_comportamentais TEXT,
    recomendacoes TEXT,
    proxima_sessao DATE,
    anexos JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_atendido (id_atendido),
    INDEX idx_psicologo (id_psicologo),
    INDEX idx_data (data_anotacao),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (id_psicologo) REFERENCES Usuario(idusuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Controle de Faltas
CREATE TABLE IF NOT EXISTS Controle_Faltas (
    id_falta INT AUTO_INCREMENT PRIMARY KEY,
    id_atendido INT NOT NULL,
    data_falta DATE NOT NULL,
    tipo ENUM('Falta', 'Presença', 'Justificada') DEFAULT 'Falta',
    justificativa TEXT,
    registrado_por INT,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_atendido (id_atendido),
    INDEX idx_data (data_falta),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (registrado_por) REFERENCES Usuario(idusuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Desligamentos
CREATE TABLE IF NOT EXISTS Desligamento (
    id_desligamento INT AUTO_INCREMENT PRIMARY KEY,
    id_atendido INT NOT NULL,
    data_desligamento DATE NOT NULL,
    motivo VARCHAR(100),
    descricao TEXT,
    responsavel_desligamento INT,
    observacoes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_atendido (id_atendido),
    INDEX idx_data (data_desligamento),
    FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (responsavel_desligamento) REFERENCES Usuario(idusuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Tokens de Recuperação de Senha
CREATE TABLE IF NOT EXISTS Reset_Tokens (
    id_token INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    expira_em DATETIME NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    usado_em DATETIME NULL,
    ip_solicitacao VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_expiracao (expira_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Anotações do Calendário
CREATE TABLE IF NOT EXISTS Anotacao_Calendario (
    id_anotacao INT AUTO_INCREMENT PRIMARY KEY,
    data_anotacao DATE NOT NULL,
    tipo ENUM('anotacao', 'aviso', 'evento') DEFAULT 'anotacao',
    titulo VARCHAR(200),
    descricao TEXT,
    criado_por INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_data (data_anotacao),
    INDEX idx_tipo (tipo),
    FOREIGN KEY (criado_por) REFERENCES Usuario(idusuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- PROCEDURE PARA ROTAÇÃO DE LOGS
-- =====================================================

DELIMITER $$

CREATE PROCEDURE IF NOT EXISTS RotacionarLogs(IN dias_manter INT)
BEGIN
    DELETE FROM Log_Sistema 
    WHERE data_hora < DATE_SUB(NOW(), INTERVAL dias_manter DAY)
    AND nivel != 'ERROR';
END$$

DELIMITER ;

-- =====================================================
-- EVENT PARA ROTAÇÃO AUTOMÁTICA (MENSAL)
-- =====================================================

SET GLOBAL event_scheduler = ON;

CREATE EVENT IF NOT EXISTS rotacao_logs_mensal
ON SCHEDULE EVERY 1 MONTH
STARTS CURRENT_TIMESTAMP
DO
    CALL RotacionarLogs(90); -- Manter logs por 90 dias

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View de Logs Recentes
CREATE OR REPLACE VIEW vw_logs_recentes AS
SELECT 
    l.id_log,
    l.data_hora,
    l.nivel,
    l.acao,
    l.descricao,
    l.tabela_afetada,
    u.nome as usuario_nome,
    u.email as usuario_email
FROM Log_Sistema l
LEFT JOIN Usuario u ON l.usuario_id = u.idusuario
WHERE l.data_hora >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY l.data_hora DESC;

-- View de Estatísticas de Faltas
CREATE OR REPLACE VIEW vw_estatisticas_faltas AS
SELECT 
    a.idatendido,
    a.nome,
    COUNT(CASE WHEN cf.tipo = 'Falta' THEN 1 END) as total_faltas,
    COUNT(CASE WHEN cf.tipo = 'Presença' THEN 1 END) as total_presencas,
    COUNT(CASE WHEN cf.tipo = 'Justificada' THEN 1 END) as total_justificadas,
    MAX(cf.data_falta) as ultima_falta
FROM Atendido a
LEFT JOIN Controle_Faltas cf ON a.idatendido = cf.id_atendido
GROUP BY a.idatendido, a.nome;

-- =====================================================
-- DADOS INICIAIS
-- =====================================================

-- Log de instalação
INSERT INTO Log_Sistema (nivel, acao, descricao, tabela_afetada)
VALUES ('INFO', 'INSTALACAO', 'Sistema de logs migrado para MySQL', 'Log_Sistema');

-- =====================================================
-- SUCESSO!
-- =====================================================

SELECT '✅ Migração de logs concluída com sucesso!' as status;
SELECT 'Tabelas criadas: Log_Sistema, Anotacao_Psicologica, Controle_Faltas, Desligamento, Reset_Tokens, Anotacao_Calendario' as info;
