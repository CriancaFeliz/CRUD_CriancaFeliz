-- =====================================================
-- SISTEMA DE LOGS INTELIGENTE - CRIANÇA FELIZ
-- Triggers para registrar todas as alterações
-- =====================================================

-- Tabela de Logs (já existe, apenas verificar)
-- CREATE TABLE `log` (
--   `id_log` int(11) NOT NULL AUTO_INCREMENT,
--   `data_alteracao` datetime DEFAULT CURRENT_TIMESTAMP,
--   `registro_alt` varchar(255) DEFAULT NULL,
--   `valor_anterior` longtext DEFAULT NULL,
--   `valor_atual` longtext DEFAULT NULL,
--   `acao` varchar(50) DEFAULT NULL,
--   `tabela_afetada` varchar(100) DEFAULT NULL,
--   `id_usuario` int(11) DEFAULT NULL,
--   `id_registro` int(11) DEFAULT NULL,
--   `campo_alterado` varchar(100) DEFAULT NULL,
--   `ip_usuario` varchar(45) DEFAULT NULL,
--   `navegador` varchar(255) DEFAULT NULL,
--   PRIMARY KEY (`id_log`),
--   KEY `id_usuario` (`id_usuario`),
--   KEY `tabela_afetada` (`tabela_afetada`),
--   KEY `data_alteracao` (`data_alteracao`),
--   KEY `acao` (`acao`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =====================================================
-- TRIGGERS PARA TABELA ATENDIDO
-- =====================================================

DELIMITER $$

-- INSERT em Atendido
CREATE TRIGGER `log_atendido_insert` AFTER INSERT ON `atendido` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Novo atendido criado: ', NEW.nome),
        NULL,
        CONCAT(
            'Nome: ', COALESCE(NEW.nome, 'N/A'), ' | ',
            'CPF: ', COALESCE(NEW.cpf, 'N/A'), ' | ',
            'Data Nascimento: ', COALESCE(NEW.data_nascimento, 'N/A'), ' | ',
            'Status: ', COALESCE(NEW.status, 'N/A')
        ),
        'INSERT',
        'atendido',
        @usuario_id,
        NEW.idatendido,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

-- UPDATE em Atendido
CREATE TRIGGER `log_atendido_update` AFTER UPDATE ON `atendido` FOR EACH ROW
BEGIN
    DECLARE v_mudancas VARCHAR(1000);
    SET v_mudancas = '';
    
    IF OLD.nome != NEW.nome THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Nome: "', OLD.nome, '" → "', NEW.nome, '" | ');
    END IF;
    
    IF OLD.cpf != NEW.cpf THEN
        SET v_mudancas = CONCAT(v_mudancas, 'CPF: "', OLD.cpf, '" → "', NEW.cpf, '" | ');
    END IF;
    
    IF OLD.data_nascimento != NEW.data_nascimento THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Data Nascimento: "', OLD.data_nascimento, '" → "', NEW.data_nascimento, '" | ');
    END IF;
    
    IF OLD.status != NEW.status THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Status: "', OLD.status, '" → "', NEW.status, '" | ');
    END IF;
    
    IF OLD.endereco != NEW.endereco THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Endereço: "', OLD.endereco, '" → "', NEW.endereco, '" | ');
    END IF;
    
    IF v_mudancas != '' THEN
        INSERT INTO `log` (
            `data_alteracao`,
            `registro_alt`,
            `valor_anterior`,
            `valor_atual`,
            `acao`,
            `tabela_afetada`,
            `id_usuario`,
            `id_registro`,
            `campo_alterado`,
            `ip_usuario`
        ) VALUES (
            NOW(),
            CONCAT('Atendido alterado: ', NEW.nome, ' (ID: ', NEW.idatendido, ')'),
            CONCAT(
                'Nome: ', OLD.nome, ' | ',
                'CPF: ', OLD.cpf, ' | ',
                'Status: ', OLD.status
            ),
            CONCAT(
                'Nome: ', NEW.nome, ' | ',
                'CPF: ', NEW.cpf, ' | ',
                'Status: ', NEW.status
            ),
            'UPDATE',
            'atendido',
            @usuario_id,
            NEW.idatendido,
            'MULTIPLOS_CAMPOS',
            @ip_usuario
        );
    END IF;
END$$

-- DELETE em Atendido
CREATE TRIGGER `log_atendido_delete` AFTER DELETE ON `atendido` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Atendido deletado: ', OLD.nome),
        CONCAT(
            'Nome: ', OLD.nome, ' | ',
            'CPF: ', OLD.cpf, ' | ',
            'Status: ', OLD.status
        ),
        NULL,
        'DELETE',
        'atendido',
        @usuario_id,
        OLD.idatendido,
        'REGISTRO_DELETADO',
        @ip_usuario
    );
END$$

-- =====================================================
-- TRIGGERS PARA TABELA FICHA_ACOLHIMENTO
-- =====================================================

CREATE TRIGGER `log_ficha_acolhimento_insert` AFTER INSERT ON `ficha_acolhimento` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Nova Ficha de Acolhimento criada (ID: ', NEW.id, ')'),
        NULL,
        CONCAT(
            'ID Atendido: ', NEW.id_atendido, ' | ',
            'Queixa Principal: ', COALESCE(NEW.queixa_principal, 'N/A'), ' | ',
            'Data Acolhimento: ', COALESCE(NEW.data_acolhimento, 'N/A')
        ),
        'INSERT',
        'ficha_acolhimento',
        @usuario_id,
        NEW.id,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_ficha_acolhimento_update` AFTER UPDATE ON `ficha_acolhimento` FOR EACH ROW
BEGIN
    DECLARE v_mudancas VARCHAR(1000);
    SET v_mudancas = '';
    
    IF OLD.queixa_principal != NEW.queixa_principal THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Queixa Principal alterada | ');
    END IF;
    
    IF OLD.escola != NEW.escola THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Escola: "', OLD.escola, '" → "', NEW.escola, '" | ');
    END IF;
    
    IF OLD.periodo != NEW.periodo THEN
        SET v_mudancas = CONCAT(v_mudancas, 'Período: "', OLD.periodo, '" → "', NEW.periodo, '" | ');
    END IF;
    
    IF v_mudancas != '' THEN
        INSERT INTO `log` (
            `data_alteracao`,
            `registro_alt`,
            `valor_anterior`,
            `valor_atual`,
            `acao`,
            `tabela_afetada`,
            `id_usuario`,
            `id_registro`,
            `campo_alterado`,
            `ip_usuario`
        ) VALUES (
            NOW(),
            CONCAT('Ficha de Acolhimento alterada (ID: ', NEW.id, ')'),
            CONCAT('Queixa: ', OLD.queixa_principal, ' | Escola: ', OLD.escola),
            CONCAT('Queixa: ', NEW.queixa_principal, ' | Escola: ', NEW.escola),
            'UPDATE',
            'ficha_acolhimento',
            @usuario_id,
            NEW.id,
            'MULTIPLOS_CAMPOS',
            @ip_usuario
        );
    END IF;
END$$

CREATE TRIGGER `log_ficha_acolhimento_delete` AFTER DELETE ON `ficha_acolhimento` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Ficha de Acolhimento deletada (ID: ', OLD.id, ')'),
        CONCAT('Queixa Principal: ', OLD.queixa_principal),
        NULL,
        'DELETE',
        'ficha_acolhimento',
        @usuario_id,
        OLD.id,
        'REGISTRO_DELETADO',
        @ip_usuario
    );
END$$

-- =====================================================
-- TRIGGERS PARA TABELA FICHA_SOCIOECONOMICO
-- =====================================================

CREATE TRIGGER `log_ficha_socioeconomico_insert` AFTER INSERT ON `ficha_socioeconomico` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Nova Ficha Socioeconômica criada (ID: ', NEW.idficha, ')'),
        NULL,
        JSON_OBJECT(
            'idficha', NEW.idficha,
            'id_atendido', NEW.id_atendido,
            'agua', NEW.agua,
            'esgoto', NEW.esgoto,
            'energia', NEW.energia,
            'renda_familiar', NEW.renda_familiar,
            'qtd_pessoas', NEW.qtd_pessoas,
            'cond_residencia', NEW.cond_residencia,
            'moradia', NEW.moradia,
            'nr_veiculos', NEW.nr_veiculos,
            'observacoes', NEW.observacoes,
            'entrevistado', NEW.entrevistado,
            'residencia', NEW.residencia,
            'nr_comodos', NEW.nr_comodos,
            'construcao', NEW.construcao,
            'nome_menor', NEW.nome_menor,
            'assistente_social', NEW.assistente_social,
            'cadunico', NEW.cadunico,
            'renda_per_capita', NEW.renda_per_capita,
            'bolsa_familia', NEW.bolsa_familia,
            'auxilio_brasil', NEW.auxilio_brasil,
            'bpc', NEW.bpc,
            'auxilio_emergencial', NEW.auxilio_emergencial,
            'seguro_desemprego', NEW.seguro_desemprego,
            'aposentadoria', NEW.aposentadoria
        ),
        'INSERT',
        'ficha_socioeconomico',
        @usuario_id,
        NEW.idficha,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_ficha_socioeconomico_update` AFTER UPDATE ON `ficha_socioeconomico` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Ficha Socioeconômica alterada (ID: ', NEW.idficha, ')'),
        JSON_OBJECT(
            'idficha', OLD.idficha,
            'id_atendido', OLD.id_atendido,
            'agua', OLD.agua,
            'esgoto', OLD.esgoto,
            'energia', OLD.energia,
            'renda_familiar', OLD.renda_familiar,
            'qtd_pessoas', OLD.qtd_pessoas,
            'cond_residencia', OLD.cond_residencia,
            'moradia', OLD.moradia,
            'nr_veiculos', OLD.nr_veiculos,
            'observacoes', OLD.observacoes,
            'entrevistado', OLD.entrevistado,
            'residencia', OLD.residencia,
            'nr_comodos', OLD.nr_comodos,
            'construcao', OLD.construcao,
            'nome_menor', OLD.nome_menor,
            'assistente_social', OLD.assistente_social,
            'cadunico', OLD.cadunico,
            'renda_per_capita', OLD.renda_per_capita,
            'bolsa_familia', OLD.bolsa_familia,
            'auxilio_brasil', OLD.auxilio_brasil,
            'bpc', OLD.bpc,
            'auxilio_emergencial', OLD.auxilio_emergencial,
            'seguro_desemprego', OLD.seguro_desemprego,
            'aposentadoria', OLD.aposentadoria
        ),
        JSON_OBJECT(
            'idficha', NEW.idficha,
            'id_atendido', NEW.id_atendido,
            'agua', NEW.agua,
            'esgoto', NEW.esgoto,
            'energia', NEW.energia,
            'renda_familiar', NEW.renda_familiar,
            'qtd_pessoas', NEW.qtd_pessoas,
            'cond_residencia', NEW.cond_residencia,
            'moradia', NEW.moradia,
            'nr_veiculos', NEW.nr_veiculos,
            'observacoes', NEW.observacoes,
            'entrevistado', NEW.entrevistado,
            'residencia', NEW.residencia,
            'nr_comodos', NEW.nr_comodos,
            'construcao', NEW.construcao,
            'nome_menor', NEW.nome_menor,
            'assistente_social', NEW.assistente_social,
            'cadunico', NEW.cadunico,
            'renda_per_capita', NEW.renda_per_capita,
            'bolsa_familia', NEW.bolsa_familia,
            'auxilio_brasil', NEW.auxilio_brasil,
            'bpc', NEW.bpc,
            'auxilio_emergencial', NEW.auxilio_emergencial,
            'seguro_desemprego', NEW.seguro_desemprego,
            'aposentadoria', NEW.aposentadoria
        ),
        'UPDATE',
        'ficha_socioeconomico',
        @usuario_id,
        NEW.idficha,
        'MULTIPLOS_CAMPOS',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_ficha_socioeconomico_delete` AFTER DELETE ON `ficha_socioeconomico` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Ficha Socioeconômica deletada (ID: ', OLD.idficha, ')'),
        JSON_OBJECT(
            'idficha', OLD.idficha,
            'id_atendido', OLD.id_atendido,
            'agua', OLD.agua,
            'esgoto', OLD.esgoto,
            'energia', OLD.energia,
            'renda_familiar', OLD.renda_familiar,
            'qtd_pessoas', OLD.qtd_pessoas,
            'cond_residencia', OLD.cond_residencia,
            'moradia', OLD.moradia,
            'nr_veiculos', OLD.nr_veiculos,
            'observacoes', OLD.observacoes,
            'entrevistado', OLD.entrevistado,
            'residencia', OLD.residencia,
            'nr_comodos', OLD.nr_comodos,
            'construcao', OLD.construcao,
            'nome_menor', OLD.nome_menor,
            'assistente_social', OLD.assistente_social,
            'cadunico', OLD.cadunico,
            'renda_per_capita', OLD.renda_per_capita,
            'bolsa_familia', OLD.bolsa_familia,
            'auxilio_brasil', OLD.auxilio_brasil,
            'bpc', OLD.bpc,
            'auxilio_emergencial', OLD.auxilio_emergencial,
            'seguro_desemprego', OLD.seguro_desemprego,
            'aposentadoria', OLD.aposentadoria
        ),
        NULL,
        'DELETE',
        'ficha_socioeconomico',
        @usuario_id,
        OLD.idficha,
        'REGISTRO_DELETADO',
        @ip_usuario
    );
END$$

-- =====================================================
-- TRIGGERS PARA TABELA ANOTACAO_PSICOLOGICA
-- =====================================================

CREATE TRIGGER `log_anotacao_psicologica_insert` AFTER INSERT ON `anotacao_psicologica` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Nova Anotação Psicológica criada (ID: ', NEW.id_anotacao, ')'),
        NULL,
        CONCAT(
            'Tipo: ', NEW.tipo, ' | ',
            'Título: ', COALESCE(NEW.titulo, 'N/A'), ' | ',
            'Psicólogo ID: ', NEW.id_psicologo
        ),
        'INSERT',
        'anotacao_psicologica',
        NEW.id_psicologo,
        NEW.id_anotacao,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_anotacao_psicologica_update` AFTER UPDATE ON `anotacao_psicologica` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Anotação Psicológica alterada (ID: ', NEW.id_anotacao, ')'),
        CONCAT('Tipo: ', OLD.tipo, ' | Título: ', OLD.titulo),
        CONCAT('Tipo: ', NEW.tipo, ' | Título: ', NEW.titulo),
        'UPDATE',
        'anotacao_psicologica',
        NEW.id_psicologo,
        NEW.id_anotacao,
        'MULTIPLOS_CAMPOS',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_anotacao_psicologica_delete` AFTER DELETE ON `anotacao_psicologica` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Anotação Psicológica deletada (ID: ', OLD.id_anotacao, ')'),
        CONCAT('Tipo: ', OLD.tipo, ' | Título: ', OLD.titulo),
        NULL,
        'DELETE',
        'anotacao_psicologica',
        OLD.id_psicologo,
        OLD.id_anotacao,
        'REGISTRO_DELETADO',
        @ip_usuario
    );
END$$

-- =====================================================
-- TRIGGERS PARA TABELA FREQUENCIA_DIA
-- =====================================================

CREATE TRIGGER `log_frequencia_dia_insert` AFTER INSERT ON `frequencia_dia` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Frequência registrada (ID Atendido: ', NEW.id_atendido, ', Data: ', NEW.data, ')'),
        NULL,
        CONCAT('Status: ', NEW.status, ' | Data: ', NEW.data),
        'INSERT',
        'frequencia_dia',
        NEW.registrado_por,
        NEW.id_frequencia_dia,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_frequencia_dia_update` AFTER UPDATE ON `frequencia_dia` FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO `log` (
            `data_alteracao`,
            `registro_alt`,
            `valor_anterior`,
            `valor_atual`,
            `acao`,
            `tabela_afetada`,
            `id_usuario`,
            `id_registro`,
            `campo_alterado`,
            `ip_usuario`
        ) VALUES (
            NOW(),
            CONCAT('Frequência alterada (Data: ', NEW.data, ')'),
            CONCAT('Status: ', OLD.status),
            CONCAT('Status: ', NEW.status),
            'UPDATE',
            'frequencia_dia',
            NEW.registrado_por,
            NEW.id_frequencia_dia,
            'status',
            @ip_usuario
        );
    END IF;
END$$

-- =====================================================
-- TRIGGERS PARA TABELA DESLIGAMENTO
-- =====================================================

CREATE TRIGGER `log_desligamento_insert` AFTER INSERT ON `desligamento` FOR EACH ROW
BEGIN
    INSERT INTO `log` (
        `data_alteracao`,
        `registro_alt`,
        `valor_anterior`,
        `valor_atual`,
        `acao`,
        `tabela_afetada`,
        `id_usuario`,
        `id_registro`,
        `campo_alterado`,
        `ip_usuario`
    ) VALUES (
        NOW(),
        CONCAT('Desligamento registrado (ID Atendido: ', NEW.id_atendido, ')'),
        NULL,
        CONCAT(
            'Motivo: ', NEW.motivo, ' | ',
            'Tipo: ', NEW.tipo_motivo, ' | ',
            'Data: ', NEW.data_desligamento, ' | ',
            'Automático: ', NEW.automatico
        ),
        'INSERT',
        'desligamento',
        NEW.desligado_por,
        NEW.id_desligamento,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

DELIMITER ;

-- =====================================================
-- Atualizar estrutura da tabela LOG se necessário
-- =====================================================

ALTER TABLE `log` 
ADD COLUMN IF NOT EXISTS `id_registro` INT(11) DEFAULT NULL AFTER `id_usuario`,
ADD COLUMN IF NOT EXISTS `campo_alterado` VARCHAR(100) DEFAULT NULL AFTER `id_registro`,
ADD COLUMN IF NOT EXISTS `ip_usuario` VARCHAR(45) DEFAULT NULL AFTER `campo_alterado`,
ADD KEY IF NOT EXISTS `idx_id_registro` (`id_registro`),
ADD KEY IF NOT EXISTS `idx_campo_alterado` (`campo_alterado`),
ADD KEY IF NOT EXISTS `idx_ip_usuario` (`ip_usuario`);

-- Criar índices para melhor performance
CREATE INDEX IF NOT EXISTS `idx_data_acao` ON `log` (`data_alteracao`, `acao`);
CREATE INDEX IF NOT EXISTS `idx_tabela_acao` ON `log` (`tabela_afetada`, `acao`);
CREATE INDEX IF NOT EXISTS `idx_usuario_data` ON `log` (`id_usuario`, `data_alteracao`);
