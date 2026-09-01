-- =====================================================
-- SCRIPT PARA LIMPAR E RECRIAR TODOS OS TRIGGERS
-- =====================================================

-- 1. DELETAR TODOS OS TRIGGERS ANTIGOS
-- =====================================================

DROP TRIGGER IF EXISTS `log_atendido_insert`;
DROP TRIGGER IF EXISTS `log_atendido_update`;
DROP TRIGGER IF EXISTS `log_atendido_delete`;

DROP TRIGGER IF EXISTS `log_ficha_acolhimento_insert`;
DROP TRIGGER IF EXISTS `log_ficha_acolhimento_update`;
DROP TRIGGER IF EXISTS `log_ficha_acolhimento_delete`;

DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_insert`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_update`;
DROP TRIGGER IF EXISTS `log_ficha_socioeconomico_delete`;

DROP TRIGGER IF EXISTS `log_anotacao_psicologica_insert`;
DROP TRIGGER IF EXISTS `log_anotacao_psicologica_update`;
DROP TRIGGER IF EXISTS `log_anotacao_psicologica_delete`;

DROP TRIGGER IF EXISTS `log_frequencia_dia_insert`;
DROP TRIGGER IF EXISTS `log_frequencia_dia_update`;
DROP TRIGGER IF EXISTS `log_frequencia_dia_delete`;

DROP TRIGGER IF EXISTS `log_desligamento_insert`;
DROP TRIGGER IF EXISTS `log_desligamento_update`;
DROP TRIGGER IF EXISTS `log_desligamento_delete`;

DROP TRIGGER IF EXISTS `log_usuario_insert`;
DROP TRIGGER IF EXISTS `log_usuario_update`;
DROP TRIGGER IF EXISTS `log_usuario_delete`;

DROP TRIGGER IF EXISTS `log_delete_all`;
DROP TRIGGER IF EXISTS `log_insert_all`;
DROP TRIGGER IF EXISTS `log_update_all`;

-- 2. VERIFICAR E ADICIONAR COLUNAS NA TABELA LOG SE NECESSÁRIO
-- =====================================================

ALTER TABLE `log` 
ADD COLUMN IF NOT EXISTS `id_registro` INT(11) DEFAULT NULL AFTER `id_usuario`,
ADD COLUMN IF NOT EXISTS `campo_alterado` VARCHAR(100) DEFAULT NULL AFTER `id_registro`,
ADD COLUMN IF NOT EXISTS `ip_usuario` VARCHAR(45) DEFAULT NULL AFTER `campo_alterado`;

-- 3. CRIAR ÍNDICES PARA MELHOR PERFORMANCE
-- =====================================================

CREATE INDEX IF NOT EXISTS `idx_data_acao` ON `log` (`data_alteracao`, `acao`);
CREATE INDEX IF NOT EXISTS `idx_tabela_acao` ON `log` (`tabela_afetada`, `acao`);
CREATE INDEX IF NOT EXISTS `idx_usuario_data` ON `log` (`id_usuario`, `data_alteracao`);
CREATE INDEX IF NOT EXISTS `idx_id_registro` ON `log` (`id_registro`);
CREATE INDEX IF NOT EXISTS `idx_campo_alterado` ON `log` (`campo_alterado`);
CREATE INDEX IF NOT EXISTS `idx_ip_usuario` ON `log` (`ip_usuario`);

-- =====================================================
-- TRIGGERS PARA TABELA ATENDIDO
-- =====================================================

DELIMITER $$

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
        JSON_OBJECT(
            'idatendido', NEW.idatendido,
            'nome', NEW.nome,
            'cpf', NEW.cpf,
            'rg', NEW.rg,
            'data_nascimento', NEW.data_nascimento,
            'data_acolhimento', NEW.data_acolhimento,
            'data_cadastro', NEW.data_cadastro,
            'endereco', NEW.endereco,
            'numero', NEW.numero,
            'complemento', NEW.complemento,
            'bairro', NEW.bairro,
            'cidade', NEW.cidade,
            'cep', NEW.cep,
            'status', NEW.status,
            'faixa_etaria', NEW.faixa_etaria
        ),
        'INSERT',
        'atendido',
        @usuario_id,
        NEW.idatendido,
        'NOVO_REGISTRO',
        @ip_usuario
    );
END$$

CREATE TRIGGER `log_atendido_update` AFTER UPDATE ON `atendido` FOR EACH ROW
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
        CONCAT('Atendido alterado: ', NEW.nome),
        JSON_OBJECT(
            'idatendido', OLD.idatendido,
            'nome', OLD.nome,
            'cpf', OLD.cpf,
            'rg', OLD.rg,
            'data_nascimento', OLD.data_nascimento,
            'data_acolhimento', OLD.data_acolhimento,
            'status', OLD.status
        ),
        JSON_OBJECT(
            'idatendido', NEW.idatendido,
            'nome', NEW.nome,
            'cpf', NEW.cpf,
            'rg', NEW.rg,
            'data_nascimento', NEW.data_nascimento,
            'data_acolhimento', NEW.data_acolhimento,
            'status', NEW.status
        ),
        'UPDATE',
        'atendido',
        @usuario_id,
        NEW.idatendido,
        'MULTIPLOS_CAMPOS',
        @ip_usuario
    );
END$$

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
        JSON_OBJECT(
            'idatendido', OLD.idatendido,
            'nome', OLD.nome,
            'cpf', OLD.cpf,
            'status', OLD.status
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
            'renda_familiar', OLD.renda_familiar,
            'qtd_pessoas', OLD.qtd_pessoas,
            'bolsa_familia', OLD.bolsa_familia,
            'auxilio_brasil', OLD.auxilio_brasil,
            'bpc', OLD.bpc
        ),
        JSON_OBJECT(
            'idficha', NEW.idficha,
            'renda_familiar', NEW.renda_familiar,
            'qtd_pessoas', NEW.qtd_pessoas,
            'bolsa_familia', NEW.bolsa_familia,
            'auxilio_brasil', NEW.auxilio_brasil,
            'bpc', NEW.bpc
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
            'renda_familiar', OLD.renda_familiar,
            'qtd_pessoas', OLD.qtd_pessoas
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

DELIMITER ;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================
-- Triggers criados com sucesso!
-- Agora execute este script no phpMyAdmin
