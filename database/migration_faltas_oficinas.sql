-- =====================================================
-- SISTEMA DE FALTAS E DESLIGAMENTO - OTIMIZADO
-- =====================================================

USE criancafeliz;

-- =====================================================
-- TABELA: Oficina
-- Gerencia as oficinas/atividades disponíveis
-- =====================================================
CREATE TABLE IF NOT EXISTS Oficina (
  id_oficina INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  descricao TEXT,
  dia_semana ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'),
  horario_inicio TIME,
  horario_fim TIME,
  ativo BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_ativo (ativo),
  INDEX idx_dia_semana (dia_semana)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Frequencia_Oficina
-- Controle de presença/falta por oficina
-- =====================================================
CREATE TABLE IF NOT EXISTS Frequencia_Oficina (
  id_frequencia INT AUTO_INCREMENT PRIMARY KEY,
  id_atendido INT NOT NULL,
  id_oficina INT NOT NULL,
  data DATE NOT NULL,
  status ENUM('P', 'F', 'J') NOT NULL COMMENT 'P=Presente, F=Falta, J=Justificada',
  justificativa TEXT NULL,
  observacao TEXT NULL,
  registrado_por INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY (id_oficina) REFERENCES Oficina(id_oficina)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY (registrado_por) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
    
  UNIQUE KEY unique_frequencia (id_atendido, id_oficina, data),
  INDEX idx_atendido (id_atendido),
  INDEX idx_oficina (id_oficina),
  INDEX idx_data (data),
  INDEX idx_status (status),
  INDEX idx_data_status (data, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Frequencia_Dia
-- Controle de presença/falta por dia (geral)
-- =====================================================
CREATE TABLE IF NOT EXISTS Frequencia_Dia (
  id_frequencia_dia INT AUTO_INCREMENT PRIMARY KEY,
  id_atendido INT NOT NULL,
  data DATE NOT NULL,
  status ENUM('P', 'F', 'J') NOT NULL COMMENT 'P=Presente, F=Falta, J=Justificada',
  justificativa TEXT NULL,
  observacao TEXT NULL,
  registrado_por INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY (registrado_por) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
    
  UNIQUE KEY unique_frequencia_dia (id_atendido, data),
  INDEX idx_atendido_dia (id_atendido),
  INDEX idx_data_dia (data),
  INDEX idx_status_dia (status),
  INDEX idx_data_status_dia (data, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TABELA: Desligamento
-- Registro de desligamentos de atendidos
-- =====================================================
CREATE TABLE IF NOT EXISTS Desligamento (
  id_desligamento INT AUTO_INCREMENT PRIMARY KEY,
  id_atendido INT NOT NULL,
  motivo VARCHAR(100) NOT NULL,
  tipo_motivo ENUM('idade', 'excesso_faltas', 'pedido_familia', 'transferencia', 'outros') NOT NULL,
  data_desligamento DATE NOT NULL,
  observacao TEXT NULL,
  automatico BOOLEAN DEFAULT FALSE,
  pode_retornar BOOLEAN DEFAULT TRUE,
  desligado_por INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (id_atendido) REFERENCES Atendido(idatendido)
    ON UPDATE CASCADE
    ON DELETE CASCADE,
  FOREIGN KEY (desligado_por) REFERENCES Usuario(idusuario)
    ON UPDATE CASCADE
    ON DELETE SET NULL,
    
  UNIQUE KEY unique_desligamento (id_atendido),
  INDEX idx_atendido_deslig (id_atendido),
  INDEX idx_tipo_motivo (tipo_motivo),
  INDEX idx_data_desligamento (data_desligamento),
  INDEX idx_automatico (automatico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- VIEW: Estatisticas_Frequencia
-- Estatísticas de frequência por atendido
-- =====================================================
CREATE OR REPLACE VIEW Estatisticas_Frequencia AS
SELECT 
    a.idatendido,
    a.nome,
    COUNT(CASE WHEN fd.status = 'P' THEN 1 END) as total_presencas,
    COUNT(CASE WHEN fd.status = 'F' THEN 1 END) as total_faltas,
    COUNT(CASE WHEN fd.status = 'J' THEN 1 END) as total_justificadas,
    COUNT(*) as total_registros,
    ROUND((COUNT(CASE WHEN fd.status = 'P' THEN 1 END) * 100.0 / NULLIF(COUNT(*), 0)), 2) as percentual_presenca
FROM 
    Atendido a
LEFT JOIN 
    Frequencia_Dia fd ON a.idatendido = fd.id_atendido
WHERE 
    a.status = 'Ativo'
GROUP BY 
    a.idatendido, a.nome;

-- =====================================================
-- VIEW: Atendidos_Com_Alerta
-- Atendidos com 2 ou mais faltas não justificadas
-- =====================================================
CREATE OR REPLACE VIEW Atendidos_Com_Alerta AS
SELECT 
    a.idatendido,
    a.nome,
    a.cpf,
    COUNT(CASE WHEN fd.status = 'F' THEN 1 END) as total_faltas,
    MAX(fd.data) as ultima_falta,
    CASE 
        WHEN COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 3 THEN 'CRÍTICO'
        WHEN COUNT(CASE WHEN fd.status = 'F' THEN 1 END) = 2 THEN 'ALERTA'
        ELSE 'NORMAL'
    END as nivel_alerta
FROM 
    Atendido a
LEFT JOIN 
    Frequencia_Dia fd ON a.idatendido = fd.id_atendido
WHERE 
    a.status = 'Ativo'
    AND NOT EXISTS (SELECT 1 FROM Desligamento d WHERE d.id_atendido = a.idatendido)
GROUP BY 
    a.idatendido, a.nome, a.cpf
HAVING 
    COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 2;

-- =====================================================
-- DADOS INICIAIS: Oficinas Padrão
-- =====================================================
INSERT INTO Oficina (nome, descricao, dia_semana, horario_inicio, horario_fim) VALUES
('Reforço Escolar', 'Aulas de reforço para crianças', 'Segunda', '14:00:00', '16:00:00'),
('Artes', 'Oficina de artes e artesanato', 'Terça', '14:00:00', '16:00:00'),
('Esportes', 'Atividades esportivas', 'Quarta', '14:00:00', '16:00:00'),
('Música', 'Aulas de música e canto', 'Quinta', '14:00:00', '16:00:00'),
('Dança', 'Oficina de dança', 'Sexta', '14:00:00', '16:00:00'),
('Teatro', 'Oficina de teatro', 'Sábado', '09:00:00', '11:00:00')
ON DUPLICATE KEY UPDATE nome = nome;

-- =====================================================
-- PROCEDURE: Registrar Falta Automatica
-- =====================================================
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS RegistrarFaltaAutomatica(
    IN p_id_atendido INT,
    IN p_data DATE
)
BEGIN
    -- Registra falta no dia se não houver registro
    INSERT INTO Frequencia_Dia (id_atendido, data, status)
    VALUES (p_id_atendido, p_data, 'F')
    ON DUPLICATE KEY UPDATE 
        status = IF(status = 'P', status, 'F');
END$$
DELIMITER ;

-- =====================================================
-- PROCEDURE: Desligar Por Excesso Faltas
-- =====================================================
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS DesligarPorExcessoFaltas()
BEGIN
    -- Desligar atendidos com 3 ou mais faltas não justificadas
    INSERT INTO Desligamento (id_atendido, motivo, tipo_motivo, data_desligamento, automatico)
    SELECT 
        a.idatendido,
        'Desligamento automático por excesso de faltas',
        'excesso_faltas',
        CURDATE(),
        TRUE
    FROM 
        Atendido a
    LEFT JOIN 
        Frequencia_Dia fd ON a.idatendido = fd.id_atendido
    WHERE 
        a.status = 'Ativo'
        AND NOT EXISTS (SELECT 1 FROM Desligamento d WHERE d.id_atendido = a.idatendido)
    GROUP BY 
        a.idatendido
    HAVING 
        COUNT(CASE WHEN fd.status = 'F' THEN 1 END) >= 3;
        
    -- Atualizar status dos atendidos desligados
    UPDATE Atendido a
    INNER JOIN Desligamento d ON a.idatendido = d.id_atendido
    SET a.status = 'Desligado'
    WHERE d.automatico = TRUE;
END$$
DELIMITER ;

-- =====================================================
-- FIM DA MIGRAÇÃO
-- =====================================================
